import { test } from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';

const source = readFileSync(new URL('../../public/sw.js', import.meta.url), 'utf8');
function worker(fetch) {
    const handlers = {};
    const deleted = [];
    vm.runInNewContext(source, {
        self: { addEventListener: (name, handler) => handlers[name] = handler, skipWaiting() {}, clients: { claim: async () => {} } },
        caches: { keys: async () => ['baskiyeri-shell-v1', 'baskiyeri-shell-v2', 'another-app'], delete: async (key) => deleted.push(key) },
        fetch, Response,
    });
    return { handlers, deleted };
}

test('navigation always loads current HTML instead of a stale authenticated shell', async () => {
    let result;
    const { handlers } = worker(async () => new Response('current page'));
    handlers.fetch({ request: { method: 'GET', mode: 'navigate' }, respondWith: (promise) => result = promise });
    assert.equal(await (await result).text(), 'current page');
});

test('offline navigation returns a useful retry page', async () => {
    let result;
    const { handlers } = worker(async () => { throw new Error('offline'); });
    handlers.fetch({ request: { method: 'GET', mode: 'navigate' }, respondWith: (promise) => result = promise });
    const response = await result;
    assert.equal(response.status, 503);
    assert.match(await response.text(), /Yeniden dene/);
});

test('activation removes only obsolete BaskıYeri caches', async () => {
    let completion;
    const { handlers, deleted } = worker();
    handlers.activate({ waitUntil: (promise) => completion = promise });
    await completion;
    assert.deepEqual(deleted, ['baskiyeri-shell-v1']);
});

test('mutations and build assets pass through without interception', () => {
    const { handlers } = worker();
    for (const request of [{ method: 'POST', mode: 'navigate' }, { method: 'GET', mode: 'cors' }]) {
        handlers.fetch({ request, respondWith: () => assert.fail('Unexpected interception') });
    }
});
