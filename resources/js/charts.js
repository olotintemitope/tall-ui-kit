/*
 * `uiChart`: hover and keyboard read-out for x-ui.chart (and, through it,
 * x-admin.chart and x-admin.trend, which use the older `adminChart` name).
 *
 * The plot itself is server-rendered SVG and HTML (TallUiKit\Support\Charts\ChartPlot,
 * resources/views/components/ui/chart/index.blade.php), so there is no chart
 * instance, nothing to redraw on resize and nothing to update on a Livewire morph:
 * the server sends the new SVG. This component only draws the crosshair, the hover
 * dots and the tooltip, from the plot's `data-plot` payload (each index's label
 * and formatted values, each line's y in px; x follows from the type and count).
 *
 * One component per chart, no per-point Alpine. Pointer-move finds the nearest
 * index; the plot is focusable, Left and Right (Home, End) move the active index
 * and announce it through a live region, Escape hides it. Money charts show
 * "•••••" under privacy mode.
 *
 * The tooltip is HTML (white, 1px panel-border, rounded-md, shadow-popover), the
 * date in meta and each value in numeric with a square swatch, and the series
 * name in meta when there are several. Built with textContent only: labels are
 * data, never markup.
 */
const MASK = '•••••';

function uiChart(Alpine) {
    return () => {
        // Kept out of Alpine's reactive state: hovering never re-renders anything.
        let raw = null;
        let data = null;
        let active = -1;

        return {
            view: 'chart',

            payload() {
                const next = this.$refs.plot?.dataset.plot ?? '';
                if (next !== raw) {
                    raw = next;
                    data = next ? JSON.parse(next) : null;
                }

                return data;
            },

            hidden() {
                return Boolean(this.payload()?.money && Alpine.store('privacy')?.hidden);
            },

            /*
             * Each index's x as a fraction of the plot width, as the server placed it
             * (TallUiKit\Support\Charts\ChartPlot::x): lines run edge to edge, bars sit in
             * the middle of equal slots.
             */
            xs() {
                const d = this.payload();
                const n = d?.labels.length ?? 0;
                if (d && d.xs?.length !== n) {
                    d.xs = Array.from({ length: n }, (_, i) => (d.type === 'line' ? (n > 1 ? i / (n - 1) : 0.5) : (i + 0.5) / n));
                }

                return d?.xs ?? [];
            },

            /* The nearest index to the pointer, from the stored x positions. */
            move(event) {
                const plot = this.$refs.plot;
                const xs = this.xs();
                if (!plot || xs.length === 0) {
                    return;
                }

                const rect = plot.getBoundingClientRect();
                const f = (event.clientX - rect.left) / (rect.width || 1);
                let lo = 0;
                let hi = xs.length - 1;
                while (lo < hi) {
                    const mid = (lo + hi) >> 1;
                    if (xs[mid] < f) {
                        lo = mid + 1;
                    } else {
                        hi = mid;
                    }
                }
                const index = lo > 0 && f - xs[lo - 1] < xs[lo] - f ? lo - 1 : lo;

                if (index !== active) {
                    this.show(index, rect.width);
                }
            },

            key(event) {
                const last = this.xs().length - 1;
                if (last < 0) {
                    return;
                }

                const next = {
                    ArrowRight: active < 0 ? 0 : Math.min(last, active + 1),
                    ArrowLeft: active < 0 ? last : Math.max(0, active - 1),
                    Home: 0,
                    End: last,
                }[event.key];

                if (event.key === 'Escape' && active >= 0) {
                    event.preventDefault();
                    this.hide();

                    return;
                }

                if (next === undefined) {
                    return;
                }

                event.preventDefault();
                this.show(next, this.$refs.plot.clientWidth);
                this.$refs.live.textContent = this.readout(next);
            },

            readout(index) {
                const d = this.payload();
                const masked = this.hidden();
                const values = d.series.map((s) => (d.series.length > 1 ? `${s.name} ` : '') + (masked ? 'hidden' : s.values[index]));

                return `${d.labels[index]}: ${values.join(', ')}`;
            },

            show(index, width) {
                const d = this.payload();
                const { crosshair, dots, tooltip, bars } = this.$refs;
                const line = d.type === 'line';
                active = index;
                const x = this.xs()[index] * width;

                // Lines get a 1px crosshair and a dot per series; bars mark their slot.
                if (line) {
                    crosshair.style.transform = `translateX(${Math.round(x)}px)`;
                    crosshair.classList.remove('hidden');
                }

                if (line) {
                    dots.replaceChildren(
                        ...d.series.map((s) => {
                            const dot = document.createElement('span');
                            dot.className = 'absolute left-0 top-0 -ml-[5px] -mt-[5px] size-2.5 rounded-full border-2 border-surface';
                            dot.style.backgroundColor = `var(--color-${s.color})`;
                            dot.style.transform = `translate(${x}px, ${s.y[index]}px)`;

                            return dot;
                        }),
                    );
                }

                if (!line) {
                    for (const [i, slot] of [...bars.children].entries()) {
                        slot.toggleAttribute('data-active', i === index);
                    }
                }

                this.fill(tooltip, index);
                // Beside the active x, flipped to the left in the right half, inside the plot.
                const right = x > width / 2;
                const left = right ? x - tooltip.offsetWidth - 12 : x + 12;
                tooltip.style.left = `${Math.max(0, Math.round(left))}px`;
                tooltip.style.top = '0px';
                tooltip.style.opacity = '1';
            },

            fill(tooltip, index) {
                const d = this.payload();
                const masked = this.hidden();
                tooltip.replaceChildren();

                const title = document.createElement('p');
                title.className = 'type-meta';
                title.textContent = d.labels[index];

                const list = document.createElement('ul');
                list.className = 'mt-0.5';
                for (const s of d.series) {
                    const row = document.createElement('li');
                    row.className = 'flex items-center gap-1.5';

                    const swatch = document.createElement('span');
                    swatch.className = 'inline-block size-2 shrink-0 forced-color-adjust-none';
                    swatch.style.backgroundColor = `var(--color-${s.color})`;

                    const value = document.createElement('span');
                    value.className = 'type-numeric text-ink';
                    value.textContent = masked ? MASK : s.values[index];

                    row.append(swatch, value);
                    if (d.series.length > 1) {
                        const name = document.createElement('span');
                        name.className = 'type-meta';
                        name.textContent = s.name;
                        row.append(name);
                    }
                    list.append(row);
                }

                tooltip.append(title, list);
            },

            hide() {
                const { crosshair, dots, tooltip, bars } = this.$refs;
                active = -1;
                crosshair?.classList.add('hidden');
                dots?.replaceChildren();
                if (tooltip) {
                    tooltip.style.opacity = '0';
                }
                for (const slot of bars?.querySelectorAll('[data-active]') ?? []) {
                    slot.removeAttribute('data-active');
                }
            },
        };
    };
}

export function registerCharts(Alpine) {
    Alpine.data('uiChart', uiChart(Alpine));
    // The admin overview's name for the same component (x-admin.chart).
    Alpine.data('adminChart', uiChart(Alpine));
}
