{{-- After a submit with more than three errors --}}
@php
    // On this static page there is no request: fill the error bag as a failed submit would.
    $errors->put('default', new \Illuminate\Support\MessageBag([
        'summaryName' => 'Enter the account name.',
        'summaryBank' => 'Choose a bank.',
        'summaryAccount' => 'Enter a 10-digit account number.',
        'summaryEmail' => 'Enter an email like name@company.ng.',
    ]));
@endphp
<x-ui.form x-on:submit.prevent class="flex max-w-md flex-col gap-4">
    <x-ui.error-summary :fields="['summaryName' => 'Account name', 'summaryBank' => 'Bank', 'summaryAccount' => 'Account number', 'summaryEmail' => 'Remittance email']" />
    <x-ui.field label="Account name" name="summaryName"><x-ui.input width="full" /></x-ui.field>
    <x-ui.field label="Bank" name="summaryBank">
        <x-ui.select placeholder="Choose a bank" :options="['gtb' => 'GTBank', 'zenith' => 'Zenith Bank', 'access' => 'Access Bank']" />
    </x-ui.field>
    <x-ui.field label="Account number" name="summaryAccount"><x-ui.input value="01234" width="sm" /></x-ui.field>
    <x-ui.field label="Remittance email" name="summaryEmail"><x-ui.input type="email" value="finance@" width="full" /></x-ui.field>
</x-ui.form>
