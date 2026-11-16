{{-- All done, dismissible --}}
<x-ui.checklist
    title="Launch checklist"
    name="docs-launch"
    persist="none"
    dismissible
    :heading-level="4"
    :items="[
        ['label' => 'Write the release notes', 'done' => true],
        ['label' => 'Run the final checks', 'done' => true],
        ['label' => 'Publish the release', 'done' => true],
    ]"
/>
