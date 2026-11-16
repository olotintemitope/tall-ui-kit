{{-- Cards for plans --}}
<x-ui.radio-group name="rgPlan" label="Plan" variant="cards" selected="growth" :options="[
    ['value' => 'starter', 'label' => 'Starter', 'description' => 'Up to 5 campaigns a month. Free.'],
    ['value' => 'growth', 'label' => 'Growth', 'description' => 'Up to 25 campaigns a month. ₦15,000 a month.'],
    ['value' => 'pro', 'label' => 'Pro', 'description' => 'No limit. Talk to sales.', 'disabled' => true],
]" />
