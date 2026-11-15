<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

/*
 * x-ui.confirm's `action`: the Confirm button must call run('<method>', ...params) on the
 * owning Livewire component. A Collection::implode over Js objects once dropped the
 * method name and rendered run(), so no confirm with an action did anything.
 */
it('renders the action and its params into the Confirm button', function (string $component, string $expected) {
    $html = html_entity_decode(Blade::render($component));

    expect($html)->toContain($expected)->not->toContain('run()');
})->with([
    'confirm' => ['<x-ui.confirm name="pause" title="Pause?" action="pauseSample" />', "run('pauseSample')"],
    'with params' => ['<x-ui.confirm name="pause" title="Pause?" action="pause" :params="[12, \'now\']" />', "run('pause', 12, 'now')"],
    'destructive' => ['<x-ui.confirm.destructive name="del" title="Delete?" action="deleteSample" confirm-text="Lagos" />', "run('deleteSample')"],
    'older alias' => ['<x-ui.confirm-dialog name="old" title="Delete?" action="remove" />', "run('remove')"],
    'reason' => ['<x-ui.confirm.reason name="sus" title="Suspend?" action="suspendSample" :reasons="[\'spam\' => \'Spam\']" />', "run('suspendSample', chosen)"],
]);
