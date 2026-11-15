/*
 * axe-core audit of the workbench gallery in headless Chromium, in both themes.
 *
 *   composer serve            # or: vendor/bin/testbench serve --port=8077
 *   BASE=http://127.0.0.1:8077 npm run a11y
 *   node scripts/a11y.mjs --pages=/,/forms --widths=1440,390
 *
 * Rules: WCAG 2.0/2.1/2.2 A and AA plus best-practice (the kit's target). Opens the
 * menus and a dialog on /overlays so their content is checked too. Exits 1 when any
 * page has a violation.
 */
import { chromium } from 'playwright';
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const axeSource = readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
const arg = (name, fallback) => process.argv.find((a) => a.startsWith(`--${name}=`))?.split('=')[1] ?? fallback;

const base = process.env.BASE ?? 'http://127.0.0.1:8077';
const pages = arg('pages', '/,/forms,/overlays,/data,/charts,/tables,/sidebar').split(',');
const widths = arg('widths', '1440,390').split(',').map(Number);
const themes = arg('themes', 'neutral,newbanger').split(',');

const browser = await chromium.launch();
let total = 0;

for (const theme of themes) {
    for (const width of widths) {
        const context = await browser.newContext({ viewport: { width, height: 900 } });
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(e.message));

        for (const path of pages) {
            await page.goto(`${base}${path}?theme=${theme}`, { waitUntil: 'networkidle' });
            await page.evaluate(() => window.uiDefer?.flush?.());
            await page.waitForTimeout(300);

            const states = [['page', null]];
            if (path === '/overlays') {
                states.push(['open menu', async () => { await page.getByRole('button', { name: 'Actions' }).click(); }]);
                states.push(['open dialog', async () => { await page.keyboard.press('Escape'); await page.getByRole('button', { name: 'Dialog', exact: true }).click(); }]);
            }
            if (path === '/forms') {
                states.push(['open select', async () => { await page.getByRole('combobox', { name: 'Payout day' }).click(); }]);
            }

            for (const [state, open] of states) {
                if (open) {
                    await open();
                    await page.waitForTimeout(300);
                }
                await page.addScriptTag({ content: axeSource });
                const result = await page.evaluate(async () => window.axe.run(document, {
                    runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'] },
                }));
                const violations = result.violations;
                total += violations.length;
                const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
                console.log(`${theme.padEnd(9)} ${String(width).padStart(4)} ${path.padEnd(10)} ${state.padEnd(12)} violations: ${violations.length}  passes: ${result.passes.length}${overflow ? '  PAGE SCROLLS SIDEWAYS' : ''}`);
                for (const v of violations) {
                    console.log(`    ${v.id} (${v.impact}): ${v.help}`);
                    for (const node of v.nodes.slice(0, 4)) console.log(`      ${node.target.join(' ')}  ${node.failureSummary?.split('\n')[1]?.trim() ?? ''}`);
                }
            }
        }

        if (errors.length) console.log(`  page errors: ${[...new Set(errors)].join(' | ')}`);
        await context.close();
    }
}

await browser.close();
console.log(`\nTotal violations: ${total}`);
process.exit(total > 0 ? 1 : 0);
