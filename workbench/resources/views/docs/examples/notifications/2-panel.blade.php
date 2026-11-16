{{-- The panel with day groups (static: model="") --}}
<div class="max-w-[380px] rounded-lg border border-panel-border bg-popover">
    <x-ui.notification-panel model="" :unread="2" view-all="#notifications">
        <x-ui.notification-group label="Today">
            <x-ui.notification-item id="n-301" title="Payout approved" message="PAY-9F3C21AB for ₦1,250,000 was approved by Ada Obi." time="2026-11-16 09:12:00" type="Payouts" url="#payout" />
            <x-ui.notification-item id="n-300" title="New submission" message="Burna Vibes sent a track to Lagos Nights." time="2026-11-16 07:40:00" type="Campaigns" url="#submission" />
        </x-ui.notification-group>
        <x-ui.notification-group label="Earlier">
            <x-ui.notification-item id="n-288" title="Statement ready" message="Your October statement can be downloaded." time="2026-11-10 18:00:00" type="Billing" :read="true" />
        </x-ui.notification-group>
    </x-ui.notification-panel>
</div>
