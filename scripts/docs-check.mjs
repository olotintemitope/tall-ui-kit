/*
 * Browser checks for the built docs site (composer docs:build), in headless Chromium:
 *
 *   python3 -m http.server 8106 -d site     # or any static server
 *   BASE=http://127.0.0.1:8106 node scripts/docs-check.mjs
 *   node scripts/docs-check.mjs --widths=1440,390 --sets=neutral:light,newbanger:dark
 *
 * Every page at each width and theme/mode set: axe-core (WCAG 2.0 to 2.2 A and AA plus
 * best-practice) with 0 violations, no console errors, no sideways page scroll. Then
 * the behaviours: search finds a component and Enter opens it, a copy button copies
 * the snippet, the Theme switch swaps the stylesheet and the Mode toggle sets
 * html[data-color-scheme]. Exits 1 on any failure.
 */
import { chromium } from 'playwright';
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { createRequire } from 'node:module';
import { join, relative } from 'node:path';

const require = createRequire(import.meta.url);
const axeSource = readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
const arg = (name, fallback) => process.argv.find((a) => a.startsWith(`--${name}=`))?.split('=')[1] ?? fallback;

const base = (process.env.BASE ?? 'http://127.0.0.1:8106').replace(/\/$/, '');
const root = new URL('../site', import.meta.url).pathname;
const walk = (dir) => readdirSync(dir).flatMap((f) => (statSync(join(dir, f)).isDirectory() ? (f === 'assets' ? [] : walk(join(dir, f))) : f.endsWith('.html') ? [relative(root, join(dir, f))] : []));
const only = arg('pages', null);
const pages = only ? only.split(',') : walk(root).sort();
const widths = arg('widths', '1440,390').split(',').map(Number);
const sets = arg('sets', 'neutral:light').split(',').map((s) => s.split(':'));
const behaviour = arg('behaviour', 'yes') === 'yes';

const browser = await chromium.launch();
let failures = 0;
let runs = 0;

for (const [theme, scheme] of sets) {
    for (const width of widths) {
        const context = await browser.newContext({ viewport: { width, height: 900 } });
        await context.addInitScript(([t, s]) => {
            try {
                localStorage.setItem('tallui_docs_theme', t);
                localStorage.setItem('tallui_color_scheme', s);
            } catch {}
        }, [theme, scheme]);
        const page = await context.newPage();
        let errors = [];
        page.on('pageerror', (e) => errors.push(e.message));
        page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });

        for (const path of pages) {
            errors = [];
            await page.goto(`${base}/${path}`, { waitUntil: 'networkidle' });
            await page.waitForTimeout(150);
            await page.addScriptTag({ content: axeSource });
            const result = await page.evaluate(async () => window.axe.run(document, {
                runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'] },
            }));
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
            const applied = await page.evaluate(() => [document.documentElement.dataset.docsTheme ?? 'neutral', document.documentElement.dataset.colorScheme]);
            runs++;
            const bad = result.violations.length || overflow || errors.length || applied[0] !== theme || applied[1] !== scheme;
            if (bad) {
                failures++;
                console.log(`FAIL ${theme}:${scheme} ${width} ${path}  violations ${result.violations.length}${overflow ? '  SIDEWAYS SCROLL' : ''}${errors.length ? '  console: ' + errors.join(' | ') : ''}${applied[0] !== theme || applied[1] !== scheme ? '  set not applied: ' + applied.join(':') : ''}`);
                for (const v of result.violations) {
                    console.log(`    ${v.id} (${v.impact}): ${v.help}`);
                    for (const node of v.nodes.slice(0, 3)) console.log(`      ${node.target.join(' ')}  ${node.failureSummary?.split('\n')[1]?.trim() ?? ''}`);
                }
            }
        }
        console.log(`${theme}:${scheme} ${width}: ${pages.length} pages checked`);
        await context.close();
    }
}

if (behaviour) {
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, permissions: ['clipboard-read', 'clipboard-write'] });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    const check = (name, ok, detail = '') => {
        console.log(`${ok ? 'ok  ' : 'FAIL'} ${name}${detail ? ': ' + detail : ''}`);
        if (!ok) failures++;
    };

    // Search: visible with JS, finds "Date and time pickers", Enter opens it.
    await page.goto(`${base}/index.html`, { waitUntil: 'networkidle' });
    await page.locator('#docs-search').fill('date');
    const firstResult = await page.locator('#docs-search-results [role=option]').first().textContent();
    check('search lists results', Boolean(firstResult), firstResult?.trim());
    await Promise.all([page.waitForURL('**/components/**', { timeout: 5000 }).catch(() => {}), page.keyboard.press('Enter')]);
    await page.waitForLoadState('networkidle');
    check('search Enter opens the page', page.url().includes('/components/'), page.url());
    await page.locator('main h1').click();
    await page.keyboard.press('/');
    check('slash focuses search', await page.evaluate(() => document.activeElement?.id === 'docs-search'));

    // Copy: the first snippet's copy button puts the snippet on the clipboard.
    await page.goto(`${base}/components/button.html`, { waitUntil: 'networkidle' });
    const snippet = (await page.locator('[data-code-block] pre').first().innerText()).trim();
    await page.locator('[data-code-block] [data-copy-button]').first().click();
    await page.waitForTimeout(200);
    const copied = (await page.evaluate(() => navigator.clipboard.readText())).trim();
    check('copy button copies the snippet', copied === snippet && copied.includes('<x-ui.button'), copied.split('\n')[0]);
    check('copy button shows Copied', (await page.locator('[data-code-block] [data-copy-button]').first().getAttribute('data-copy-state')) === 'copied');

    // Theme: the switch swaps the stylesheet and survives a reload.
    await page.getByRole('radio', { name: 'NewBanger' }).click();
    await page.waitForTimeout(200);
    check('theme switch swaps the stylesheet', (await page.locator('#docs-theme').getAttribute('href')).endsWith('newbanger.css'));
    await page.reload({ waitUntil: 'networkidle' });
    check('theme survives a reload', (await page.evaluate(() => document.documentElement.dataset.docsTheme)) === 'newbanger');
    await page.getByRole('radio', { name: 'Neutral' }).click();

    // Mode: the toggle sets html[data-color-scheme] and the page background changes.
    const before = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
    await page.getByRole('radio', { name: 'Dark' }).click();
    await page.waitForTimeout(200);
    const after = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
    check('mode toggle switches to dark', (await page.evaluate(() => document.documentElement.dataset.colorScheme)) === 'dark' && before !== after, `${before} -> ${after}`);
    await page.getByRole('radio', { name: 'Light' }).click();

    // Without JavaScript: content reads, search stays hidden.
    const nojs = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 390, height: 900 } });
    const plain = await nojs.newPage();
    await plain.goto(`${base}/components/select.html`);
    check('reads without JavaScript', (await plain.locator('main h1').textContent())?.trim() === 'Select' && await plain.locator('main table').count() > 0);
    check('search hidden without JavaScript', !(await plain.locator('[data-docs-search]').isVisible()));
    await nojs.close();

    check('no console errors during behaviour checks', errors.length === 0, errors.join(' | '));
    await context.close();
}

await browser.close();
console.log(`\n${runs} page runs, ${failures} failure(s)`);
process.exit(failures ? 1 : 0);
