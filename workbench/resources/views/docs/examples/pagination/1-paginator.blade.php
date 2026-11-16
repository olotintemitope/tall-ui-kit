{{-- Pagination for a paginator --}}
@php($payouts = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 20), 312, 20, 3, ['path' => '']))
<x-ui.pagination :paginator="$payouts" />
