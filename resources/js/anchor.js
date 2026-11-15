/*
 * Lazy x-anchor.
 *
 * Alpine's anchor plugin (bundled with Livewire) starts Floating UI for every
 * `x-anchor` element as soon as Alpine initialises it: a computePosition (which
 * walks every ancestor with getComputedStyle) and an autoUpdate (scroll listeners
 * on every scrolling ancestor, a ResizeObserver and an IntersectionObserver).
 * Tooltips, dropdowns, popovers and the curator mini profile are closed on load,
 * so that work is wasted: on /admin/ui/marketplace it was about 1s of the first
 * long task, and every scroll recomputed every anchor.
 *
 * This wraps the plugin's directive before it is registered (`livewire:init`
 * fires before Livewire registers Alpine's plugins) so the real directive runs
 * only while its element is displayed. One shared ResizeObserver reports when a
 * floating element gets a box (x-show, a popover opening, a parent sheet opening)
 * and when it loses it again; positioning starts on the first and is released on
 * the second. Markup, modifiers and `$anchor` are unchanged.
 */

function patch(Alpine) {
    if (!Alpine || Alpine.__uiLazyAnchor) {
        return;
    }

    Alpine.__uiLazyAnchor = true;

    const states = new WeakMap();
    let observer = null;

    const sync = (el) => {
        const state = states.get(el);

        if (!state) {
            return;
        }

        const shown = el.getClientRects().length > 0;

        if (shown && !state.stop) {
            state.stop = state.start();
        } else if (!shown && state.stop) {
            state.stop();
            state.stop = null;
        }
    };

    const observe = (el) => {
        observer ??= new ResizeObserver((entries) => entries.forEach((entry) => sync(entry.target)));
        observer.observe(el);
    };

    const original = Alpine.directive.bind(Alpine);

    Alpine.directive = (name, handler) => {
        if (name !== 'anchor') {
            return original(name, handler);
        }

        const lazy = (el, directive, utils) => {
            // $anchor reads this before the first open.
            el._x_anchor ??= Alpine.reactive({ x: 0, y: 0 });

            const start = () => {
                const effects = [];
                const cleanups = [];

                handler(el, directive, {
                    ...utils,
                    effect: (callback) => {
                        const effect = Alpine.effect(callback);
                        effects.push(effect);

                        return effect;
                    },
                    cleanup: (callback) => cleanups.push(callback),
                });

                return () => {
                    effects.forEach((effect) => Alpine.release(effect));
                    cleanups.forEach((callback) => callback());
                };
            };

            states.set(el, { start, stop: null });
            observe(el);

            utils.cleanup(() => {
                observer?.unobserve(el);
                states.get(el)?.stop?.();
                states.delete(el);
            });
        };

        // Morph look-ahead clones keep the plugin's own clone behaviour.
        return original(name, Alpine.skipDuringClone(lazy, handler));
    };
}

document.addEventListener('livewire:init', () => patch(window.Alpine));
