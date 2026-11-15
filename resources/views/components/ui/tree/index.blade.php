{{--
    A nested list the user expands and collapses (docs/components/workflow.md, "Tree"), on
    the WAI-ARIA APG tree view pattern: role="tree" with role="treeitem" rows (aria-level,
    aria-setsize, aria-posinset, aria-expanded on parents) and role="group" children; one
    row in the Tab order (roving tabindex).

    Keyboard: Down/Up next/previous visible row; Right opens a closed parent, else moves
    to its first child; Left closes an open parent, else moves to the parent; Home/End
    first/last visible row; Enter selects (or follows the row's `href`, or toggles a
    parent when nothing is selectable); Space selects or, with `checkable`, toggles the
    check; * opens every sibling of the row; typing a letter jumps to the next row whose
    label starts with what was typed (500ms buffer).

    Selection (optional):
      selectable  one row at a time (aria-selected); wire:model gets the row's id
      checkable   a check per row (aria-checked true, false or mixed). Checking a parent
                  checks every loaded child; a parent with some children checked is mixed
                  (a dash). wire:model gets every checked id (parents that are fully
                  checked included)
    Rows can be links (`href`, followed with Enter or a click) when nothing is selectable.

    Lazy children: a node with `hasChildren` and no `children` loads them the first time
    it opens, by calling the Livewire method `lazy` with the node's id ($wire.call(lazy,
    id)). The method returns a list of nodes in the same shape (id, label, hasChildren,
    children, href, disabled) and must check that the user may see them: the id comes
    from the browser. While loading, the row is aria-busy and says "Loading"; a failure
    says so and Right (or a click) tries again. The rows are built with textContent,
    never HTML. A #[Renderless] method keeps the round trip to the JSON alone.

    The tree's rows belong to the browser after load (wire:ignore on the role=tree list),
    so expansion and lazily loaded rows survive re-renders of the surrounding component;
    the binding still flows both ways. Change the tree's wire:key to rebuild it from new
    data. One Alpine component per tree and no Alpine per row; the behaviour
    (resources/js/tree-core.js) loads lazily the first time a tree is on the page.

    Look: 32px rows (body text), 16px indent per level, a chevron that turns (--motion-sm,
    instant under reduced motion), hover `surface-subtle`, selected `surface-muted` with
    ink text and body-strong, checks in the x-ui.checkbox look, focus ring inset on the row.

    Props: nodes (list of [id, label, children, hasChildren, expanded, href, disabled]),
    label (the tree's name, required for screen readers), selectable, checkable, lazy
    (Livewire method), value (initial id, or ids with checkable), expanded (ids open on
    load), id.
--}}
@props(['nodes' => [], 'label' => 'Tree', 'selectable' => false, 'checkable' => false, 'lazy' => null, 'value' => null, 'expanded' => [], 'id' => null])

@php
    $id ??= 'tree-'.substr(md5($label.json_encode($nodes)), 0, 6);
    $mode = $checkable ? 'check' : ($selectable ? 'select' : 'none');
    $checked = collect($checkable ? (array) $value : [])->map(fn ($v) => (string) $v)->flip();
    $open = collect((array) $expanded)->map(fn ($v) => (string) $v)->flip();

    /* Normalises the nodes and works out each one's check state (true, false or mixed). */
    $prepare = function (array $list, bool $parentChecked) use (&$prepare, $checked, $open): array {
        return collect($list)->values()->map(function ($node) use (&$prepare, $parentChecked, $checked, $open): array {
            $nodeId = (string) ($node['id'] ?? '');
            $self = $parentChecked || $checked->has($nodeId);
            $children = isset($node['children']) && is_array($node['children']) ? $prepare($node['children'], $self) : null;
            $state = $self ? 'true' : 'false';
            if (! $self && $children) {
                $states = collect($children)->pluck('state');
                $state = $states->every(fn ($s) => $s === 'true') ? 'true' : ($states->contains(fn ($s) => $s !== 'false') ? 'mixed' : 'false');
            }

            return [
                'id' => $nodeId,
                'label' => (string) ($node['label'] ?? ''),
                'children' => $children,
                'lazy' => ! $children && ! empty($node['hasChildren']),
                'expanded' => (bool) ($node['expanded'] ?? $open->has($nodeId)),
                'href' => $node['href'] ?? null,
                'disabled' => (bool) ($node['disabled'] ?? false),
                'state' => $state,
            ];
        })->all();
    };
    $tree = $prepare($nodes, false);
    $selected = $mode === 'select' && $value !== null && ! is_array($value) ? (string) $value : null;
    $first = $tree[0]['id'] ?? null;
    $tabStop = $selected ?? ($mode === 'check' ? null : null) ?? $first;
@endphp

<div
    x-data="uiTree(@js(['mode' => $mode, 'lazy' => $lazy, 'id' => $id]))"
    data-value="{{ json_encode($mode === 'check' ? $checked->keys()->all() : $selected) }}"
    x-modelable="value"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model'])->class(['ui-tree min-w-0']) }}
    data-tree-root
>
    <ul
        role="tree"
        id="{{ $id }}"
        aria-label="{{ $label }}"
        @if ($mode === 'check') aria-multiselectable="true" @endif
        class="min-w-0"
        wire:ignore
        data-tree="{{ $mode }}"
    >
        @foreach ($tree as $i => $node)
            <x-ui.tree.node :node="$node" :level="1" :setsize="count($tree)" :posinset="$i + 1" :mode="$mode" :selected="$selected" :tab-stop="$tabStop" :tree-id="$id" />
        @endforeach
    </ul>
    <p class="sr-only" aria-live="polite" data-tree-live></p>
</div>
