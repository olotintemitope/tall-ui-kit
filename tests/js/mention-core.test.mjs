// Run by tests/Feature/CommunityComponentsTest.php through `node --test` (no npm packages needed).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { mentionAt, matchPeople, insertMention } from '../../resources/js/mention-core.js';

test('finds the mention being typed at the caret', () => {
    assert.deepEqual(mentionAt('@', 1), { start: 0, query: '' });
    assert.deepEqual(mentionAt('Hi @ad', 6), { start: 3, query: 'ad' });
    assert.deepEqual(mentionAt('Hi @Ada and', 6), { start: 3, query: 'Ad' });
    assert.deepEqual(mentionAt('Line\n@Chi', 9), { start: 5, query: 'Chi' });
    assert.deepEqual(mentionAt('@Ọlá', 4), { start: 0, query: 'Ọlá' });
});

test('ignores email addresses and finished words', () => {
    assert.equal(mentionAt('ada@example', 11), null);
    assert.equal(mentionAt('Hi @Ada ', 8), null);
    assert.equal(mentionAt('no mention here', 15), null);
    assert.equal(mentionAt('', 0), null);
});

test('matches people by name start, then word start, then anywhere', () => {
    const people = [
        { key: 'u1', name: 'Bisi Adeyemi' },
        { key: 'u2', name: 'Adaeze Okafor' },
        { key: 'u3', name: 'Grace Ade' },
        { key: 'u4', name: 'Tunde Bakare' },
    ];
    assert.deepEqual(matchPeople(people, 'ad').map((p) => p.key), ['u2', 'u1', 'u3']);
    assert.deepEqual(matchPeople(people, 'BAK').map((p) => p.key), ['u4']);
    assert.deepEqual(matchPeople(people, '').map((p) => p.key), ['u1', 'u2', 'u3', 'u4']);
    assert.deepEqual(matchPeople(people, 'zz'), []);
    assert.equal(matchPeople(people, '', 2).length, 2);
    assert.deepEqual(matchPeople(null, 'a'), []);
});

test('inserts the chosen name and moves the caret after it', () => {
    assert.deepEqual(insertMention('Hi @ad', 3, 6, 'Adaeze Okafor'), { text: 'Hi @Adaeze Okafor ', caret: 18 });
    assert.deepEqual(insertMention('@b thanks', 0, 2, 'Bisi'), { text: '@Bisi thanks', caret: 6 });
});
