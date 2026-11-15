/*
 * Native <dialog> behaviour for x-ui.dialog, .form, .steps, .fullscreen, x-ui.sheet,
 * x-ui.confirm (+ .destructive, .reason), x-ui.alert-dialog and the older
 * x-ui.confirm-dialog (docs/ui-kit.md "Dialogs and confirmations").
 *
 * showModal() gives the top layer, an inert page, the ::backdrop and Escape (the
 * `cancel` event, routed through close() so busy and dirty guards apply). Open with
 * $dispatch('open-dialog', 'name') from Alpine or $this->dispatch('open-dialog',
 * name: 'name') from Livewire; close with close-dialog. Confirmations also answer
 * open-confirm / close-confirm (the older names).
 *
 * Focus: on open the first field, or Cancel for destructive confirmations, or the
 * title for an alert dialog without fields; never the × button. On close the
 * browser returns focus to the trigger; if the trigger is gone, uiFocus.focusAfter
 * (resources/js/ui.js) falls back to the page heading.
 *
 * Async: run(method) calls a Livewire method with the dialog busy (its buttons
 * aria-busy, Escape and × ignored). The call's result decides: false or a string
 * (shown inline as the error) keeps it open, anything else closes it; a form
 * dialog also stays open while a field is invalid in it; when the invalid field is on
 * the page instead, it closes and focus goes to that field. Server actions re-check
 * permissions; the dialog is only the UI.
 */

import './placement';

const FIELD = 'input:not([type=hidden]):not([disabled]), select:not([disabled]), textarea:not([disabled])';
const FOCUSABLE = `${FIELD}, button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])`;

function visible(el) {
    return el instanceof HTMLElement && el.getClientRects().length > 0;
}

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    /*
     * The <dialog> is looked up from the host once and kept in this closure, not in
     * Alpine data (a proxied element cannot call showModal) and not via $refs (callers
     * may put their own x-data on the <dialog>, which would own the ref).
     */
    Alpine.data('uiDialog', (name, options = {}) => {
        let host = null;
        const el = () => host?.querySelector(':scope > dialog');
        const discardEl = () => host?.querySelector('[data-dialog-discard]');

        return {
        name,
        open: false,
        busy: false,
        dirty: false,
        error: '',
        discarding: false,
        step: 1,
        typed: '',
        reason: '',
        reasonOther: '',
        reasonError: '',
        steps: options.steps ?? 1,
        trigger: null,
        aliases: options.aliases ?? [],

        init() {
            host = this.$el;
            const dialog = el();

            this.onCancel = (event) => {
                event.preventDefault();
                // An alert dialog: Escape acknowledges, exactly like its button.
                const acknowledge = dialog.querySelector('[data-dialog-acknowledge]');
                if (acknowledge) {
                    acknowledge.click();

                    return;
                }
                this.close();
            };
            this.onClose = () => {
                if (this.open) {
                    this.open = false;
                }
                this.restoreFocus();
            };
            this.onBackdrop = (event) => {
                // A click on the <dialog> itself (not its content) is a click on the backdrop.
                if (options.dismissible && event.target === dialog) {
                    this.close();
                }
            };
            dialog.addEventListener('cancel', this.onCancel);
            dialog.addEventListener('close', this.onClose);
            dialog.addEventListener('click', this.onBackdrop);
            if (options.guard === 'dirty') {
                this.onInput = () => {
                    this.dirty = true;
                };
                dialog.addEventListener('input', this.onInput);
            }
        },

        destroy() {
            const dialog = el();
            dialog?.removeEventListener('cancel', this.onCancel);
            dialog?.removeEventListener('close', this.onClose);
            dialog?.removeEventListener('click', this.onBackdrop);
            if (this.onInput) dialog?.removeEventListener('input', this.onInput);
            if (dialog?.open) dialog.close();
        },

        matches(detail) {
            const target = typeof detail === 'string' ? detail : detail?.name;

            return target === this.name || this.aliases.includes(target);
        },

        show() {
            const dialog = el();
            if (this.open && dialog.open) {
                return;
            }
            this.trigger = document.activeElement;
            this.open = true;
            this.busy = false;
            this.error = '';
            this.dirty = false;
            this.discarding = false;
            this.step = 1;
            this.typed = '';
            this.reason = '';
            this.reasonOther = '';
            this.reasonError = '';
            if (!dialog.open) {
                dialog.showModal();
            }
            this.focusInitial();
        },

        focusInitial() {
            const dialog = el();
            const scope = dialog.querySelector('[data-dialog-step]:not([hidden])') ?? dialog;
            let target = null;

            if (options.initialFocus === 'cancel') {
                target = dialog.querySelector('[data-dialog-cancel]');
            }
            // An explicit [autofocus] wins over the first focusable (e.g. Close in the lightbox, not a new-tab link).
            target ??= [...scope.querySelectorAll('[autofocus]')].find(visible) ?? null;
            target ??= [...scope.querySelectorAll('[data-dialog-body] ' + FIELD.split(', ').join(', [data-dialog-body] '))].find(visible);
            target ??= options.initialFocus === 'title' ? dialog.querySelector('[data-dialog-title]') : null;
            target ??= [...dialog.querySelectorAll(FOCUSABLE)].find((el) => visible(el) && !el.hasAttribute('data-dialog-close'));
            target ??= dialog.querySelector('[data-dialog-title]');
            target?.focus();
        },

        /* Asks to close: refused while busy; a dirty form asks "Discard changes?" first. */
        close(force = false) {
            if (this.busy && !force) {
                return;
            }
            if (!force && options.guard === 'dirty' && this.dirty) {
                this.discarding = true;
                this.$nextTick(() => discardEl()?.showModal?.());
                this.$nextTick(() => discardEl()?.querySelector('[data-dialog-cancel]')?.focus());

                return;
            }
            this.open = false;
            const dialog = el();
            if (dialog.open) {
                dialog.close();
            }
        },

        keepEditing() {
            this.discarding = false;
            discardEl()?.close();
            this.focusInitial();
        },

        discard() {
            this.discarding = false;
            discardEl()?.close();
            this.dirty = false;
            this.$dispatch('dialog-discarded', this.name);
            this.close(true);
        },

        restoreFocus() {
            const trigger = this.trigger;
            this.trigger = null;
            requestAnimationFrame(() => {
                const active = document.activeElement;
                if (active && active !== document.body && !el().contains(active)) {
                    return;
                }
                if (window.uiFocus) {
                    window.uiFocus.focusAfter(trigger);
                } else if (trigger?.isConnected) {
                    trigger.focus();
                }
            });
        },

        /* Runs a Livewire method with the dialog busy; see the header comment. */
        async run(method, ...params) {
            if (this.busy) {
                return;
            }
            this.busy = true;
            this.error = '';
            let result;
            try {
                result = await this.$wire.call(method, ...params);
            } catch (failure) {
                result = options.errorMessage ?? 'That did not work. Try again.';
            }
            this.busy = false;
            await this.$nextTick();

            if (typeof result === 'string' && result !== '') {
                this.error = result;
                this.$nextTick(() => el().querySelector('[data-dialog-error]')?.focus());

                return;
            }
            if (result === false || el().querySelector('[data-dialog-body] [aria-invalid="true"]')) {
                el().querySelector('[data-dialog-body] [aria-invalid="true"]')?.focus();

                return;
            }
            // A confirm for fields on the page (e.g. Record refund): if the method failed
            // validation there, close and send focus to the first invalid field, not the trigger.
            const invalidOnPage = this.$wire.$errors?.any?.()
                ? [...(host.closest('[wire\\:id]')?.querySelectorAll('[aria-invalid="true"]') ?? [])].find((field) => !field.closest('dialog') && visible(field))
                : null;
            this.dirty = false;
            this.close(true);
            if (invalidOnPage) {
                // After the browser has returned focus to the trigger.
                requestAnimationFrame(() => invalidOnPage.focus());
            }
        },

        /*
         * x-ui.confirm.reason: a reason is required, and "Other" needs text. Returns
         * the reason to send, or null after showing the error and focusing the field.
         */
        chosenReason() {
            const dialog = el();
            if (this.reason === '') {
                this.reasonError = 'Choose a reason.';
                this.$nextTick(() => dialog.querySelector('[data-reason-select]')?.focus());

                return null;
            }
            if (this.reason === 'other' && this.reasonOther.trim() === '') {
                this.reasonError = 'Describe the reason.';
                this.$nextTick(() => dialog.querySelector('[data-reason-other]')?.focus());

                return null;
            }
            this.reasonError = '';

            return this.reason === 'other' ? this.reasonOther.trim() : this.reason;
        },

        next() {
            if (this.step < this.steps) {
                this.step += 1;
                this.$nextTick(() => this.focusInitial());
            }
        },

        back() {
            if (this.step > 1) {
                this.step -= 1;
                this.$nextTick(() => this.focusInitial());
            }
        },
        };
    });
});
