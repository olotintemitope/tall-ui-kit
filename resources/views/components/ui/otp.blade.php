{{--
    One-time code entry: `length` single-digit boxes in a fieldset, bound as one string
    through x-modelable (`<x-ui.otp wire:model="code" />`). Typing a digit moves to the
    next box, Backspace in an empty box goes back and clears it, Left and Right move, and
    a paste or an OS one-time-code autofill (first box, autocomplete="one-time-code") of
    the whole code spreads across the boxes. When every box is filled it dispatches
    `otp-complete`. Setting the bound value to '' from the server clears the boxes and
    focuses the first. Boxes are 48px; disabled boxes are dashed on surface-subtle.
    Props: length (6), label, id (field-code; a second code on the same page without its
    own id gets field-code-2, so ids never repeat), error (message; marks every box aria-invalid).
--}}
@props(['length' => 6, 'label' => 'Verification code', 'id' => 'field-code', 'error' => null])

@php
    $otpCount = (int) request()->attributes->get('ui.otp.count', 0) + 1;
    request()->attributes->set('ui.otp.count', $otpCount);
    if ($id === 'field-code' && $otpCount > 1) {
        $id .= '-'.$otpCount;
    }
    $errorId = $id.'-error';
@endphp

<fieldset
    x-data="{
        length: {{ (int) $length }},
        value: '',
        digits: Array({{ (int) $length }}).fill(''),
        boxes() { return [...this.$root.querySelectorAll('[data-otp-digit]')] },
        paint() { this.boxes().forEach((box, i) => { box.value = this.digits[i] }) },
        focusBox(i) { const box = this.boxes()[i]; box?.focus(); box?.select() },
        sync() {
            this.value = this.digits.join('');
            if (this.digits.every((d) => d !== '')) this.$nextTick(() => this.$dispatch('otp-complete'));
        },
        fill(index, raw) {
            const typed = String(raw ?? '').replace(/\D/g, '');
            if (typed === '') {
                if (String(raw ?? '') === '') this.digits[index] = '';
                this.paint();
                this.sync();
                return;
            }
            let cursor = index;
            for (const digit of typed) {
                if (cursor >= this.length) break;
                this.digits[cursor] = digit;
                cursor++;
            }
            this.paint();
            this.focusBox(Math.min(cursor, this.length - 1));
            this.sync();
        },
        key(index, event) {
            if (event.key === 'Backspace' && this.digits[index] === '' && index > 0) {
                event.preventDefault();
                this.digits[index - 1] = '';
                this.paint();
                this.focusBox(index - 1);
                this.sync();
            } else if (event.key === 'ArrowLeft' && index > 0) {
                event.preventDefault();
                this.focusBox(index - 1);
            } else if (event.key === 'ArrowRight' && index < this.length - 1) {
                event.preventDefault();
                this.focusBox(index + 1);
            }
        },
    }"
    x-modelable="value"
    x-init="$watch('value', (v) => { if (v === '' && digits.some((d) => d !== '')) { digits = digits.map(() => ''); paint(); focusBox(0) } })"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model'])->class(['min-w-0']) }}
    @if ($error) data-invalid @endif
    data-otp
>
    <legend class="type-field-label mb-1.5">{{ $label }}</legend>

    <div class="flex gap-2">
        @for ($i = 0; $i < $length; $i++)
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                id="{{ $i === 0 ? $id : $id.'-'.$i }}"
                name="{{ $i === 0 ? 'one-time-code' : 'one-time-code-'.$i }}"
                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                aria-label="Digit {{ $i + 1 }} of {{ $length }}"
                @if ($error) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
                data-otp-digit
                data-ui="otp"
                x-on:input="fill({{ $i }}, $event.target.value)"
                x-on:keydown="key({{ $i }}, $event)"
                x-on:paste.prevent="fill({{ $i }}, $event.clipboardData.getData('text'))"
                x-on:focus="$event.target.select()"
                @class([
                    'h-12 w-full min-w-0 max-w-12 flex-1 rounded-md border bg-surface text-center font-body text-xl font-semibold text-ink tabular-nums outline-none transition-[border-color,box-shadow] duration-100',
                    'disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:text-ink-muted',
                    'border-danger focus:ring-2 focus:ring-danger/20' => $error,
                    'border-control hover:border-control-hover focus:border-focus focus:ring-2 focus:ring-primary/20' => ! $error,
                ])
            />
        @endfor
    </div>

    @if ($error)
        <p id="{{ $errorId }}" class="type-caption text-danger-text! mt-1" aria-live="polite">{{ $error }}</p>
    @endif
</fieldset>
