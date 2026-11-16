{{-- Copy fields: API keys and links --}}
<div class="max-w-lg space-y-4">
    <x-ui.field label="Test secret key" name="test-secret-key" hint="Shown once. Store it in your server's environment.">
        <x-ui.copy-field text="sk_test_••••••••3f9a" value="sk_test_example_3f9a" mono />
    </x-ui.field>
    <x-ui.field label="Referral link" name="referral-link">
        <x-ui.copy-field text="https://example.com/r/yetunde-balogun-ibadan" size="sm" icon-only />
    </x-ui.field>
</div>
