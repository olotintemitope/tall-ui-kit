{{--
    One row of x-ui.tree and its children (recursive). Internal: x-ui.tree prepares the
    node (id, label, children, lazy, expanded, href, disabled, state) and passes the
    level, set size, position, mode, the selected id and the tab stop. resources/js/tree-core.js
    builds lazily loaded rows with the same markup.
--}}
@props(['node', 'level', 'setsize', 'posinset', 'mode', 'selected' => null, 'tabStop' => null, 'treeId'])

@php
    $parent = $node['children'] !== null && count($node['children']) > 0 || $node['lazy'];
    $domId = $treeId.'-'.substr(md5($node['id']), 0, 8);
@endphp

{{-- Compact markup: every whitespace run between tags is a text node each morph walks. --}}
<li role="treeitem" id="{{ $domId }}" aria-labelledby="{{ $domId }}-label" aria-level="{{ $level }}" aria-setsize="{{ $setsize }}" aria-posinset="{{ $posinset }}" @if ($parent) aria-expanded="{{ $node['expanded'] ? 'true' : 'false' }}" @endif @if ($mode === 'select') aria-selected="{{ $selected === $node['id'] ? 'true' : 'false' }}" @endif @if ($mode === 'check') aria-checked="{{ $node['state'] }}" @endif @if ($node['disabled']) aria-disabled="true" @endif tabindex="{{ $tabStop === $node['id'] ? '0' : '-1' }}" class="ui-tree-item" data-id="{{ $node['id'] }}" @if ($node['lazy']) data-lazy @endif @if ($node['href']) data-href="{{ $node['href'] }}" @endif style="--tree-level: {{ $level - 1 }}"><div class="ui-tree-row"><span class="ui-tree-twisty" aria-hidden="true">@if ($parent)<x-ui.icon name="chevron-right" class="size-4" />@endif</span>@if ($mode === 'check')<span class="ui-tree-box" aria-hidden="true"><x-ui.icon name="check" :stroke-width="3" class="ui-tree-check size-3" /><x-ui.icon name="minus" :stroke-width="3" class="ui-tree-dash size-3" /></span>@endif<span id="{{ $domId }}-label" class="ui-tree-label">{{ $node['label'] }}</span><span class="ui-tree-status type-caption" aria-hidden="true" data-tree-status></span></div>@if ($node['children'])<ul role="group">@foreach ($node['children'] as $j => $child)<x-ui.tree.node :node="$child" :level="$level + 1" :setsize="count($node['children'])" :posinset="$j + 1" :mode="$mode" :selected="$selected" :tab-stop="$tabStop" :tree-id="$treeId" />@endforeach</ul>@elseif ($node['lazy'])<ul role="group"></ul>@endif</li>
