{{-- Status cell on the workbench /tables page: a status badge from TableGallery::STATUS_BADGES. --}}
@php $badge = \Workbench\App\Livewire\TableGallery::STATUS_BADGES[$row->status] ?? ['label' => $row->status, 'variant' => 'neutral']; @endphp
<x-ui.badge lean size="sm" :variant="$badge['variant']">{{ $badge['label'] }}</x-ui.badge>
