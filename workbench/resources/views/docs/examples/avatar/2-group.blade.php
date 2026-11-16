{{-- Avatar group with overflow --}}
<div class="flex flex-wrap items-center gap-6">
    <x-ui.avatar-group label="Reviewers" :people="['Ada Obi', 'Bola Ade', 'Chidi Eze', 'Ngozi Umeh', 'Femi Lawal', 'Halima Musa']" />
    <x-ui.avatar-group label="Approvers" :max="3" size="default" :people="['Kemi Adeyemi', 'Tunde Bakare', 'Zainab Bello', 'Ifeanyi Okeke']" />
    <x-ui.avatar-group label="Owner" size="lg" :people="['Chiamaka Okafor']" />
</div>
