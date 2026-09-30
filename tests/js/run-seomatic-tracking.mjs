// Executes the PHP-rendered templates without external navigation or analytics requests.
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const input = JSON.parse(readFileSync(0, 'utf8'));
const events = [{event: 'already_queued'}];
const timeline = [];
events.push = function (event) {
    timeline.push(event.event);
    return Array.prototype.push.call(this, event);
};
const timers = [];
const listeners = new Map();
const clicks = new Map();
const navigations = [];
const requests = [];
const location = {
    search: input.search ?? '',
    replace(url) {
        navigations.push(url);
        timeline.push('navigate');
    },
    get href() { return 'https://links.example/site/custom/campaign' + this.search; },
    set href(url) { this.replace(url); },
};
const link = {
    href: input.buttonUrl ?? 'https://links.example/site/actions/smartlink-manager/redirect/go/campaign/ios?src=qr',
    addEventListener(name, listener) { clicks.set(name, listener); },
};
const context = vm.createContext({
    URL,
    URLSearchParams,
    console: {log() {}},
    location,
    dataLayer: events,
    document: {
        addEventListener(name, listener) { listeners.set(name, listener); },
        querySelectorAll(selector) {
            if (selector !== 'a[href*="smartlink-manager/redirect/go"]') {
                throw new Error('Unexpected tracked-link selector: ' + selector);
            }
            return [link];
        },
    },
    setTimeout(callback, delay) { timers.push({callback, delay}); },
    async fetch(url, options) {
        requests.push({url, options});
        if (input.fetchFailure) throw new Error('Resolver unavailable');
        return {
            ok: input.httpOk !== false,
            async json() { return input.response ?? {autoRedirect: false, goUrl: null}; },
        };
    },
});
context.window = context;
for (const script of input.html.matchAll(/<script>([\s\S]*?)<\/script>/g)) {
    vm.runInContext(script[1], context, {timeout: 1000});
}
const arrivalEvents = events.map(event => event.event);
listeners.get('DOMContentLoaded')?.();
// Drain promises from the resolver before advancing the explicitly owned timers.
await new Promise(resolve => setImmediate(resolve));
const beforeTimers = events.map(event => event.event);
const timerDelays = [];
while (timers.length) {
    const timer = timers.shift();
    timerDelays.push(timer.delay);
    timer.callback();
}
let clickPrevented = false;
if (input.click) {
    clicks.get('click')?.({preventDefault() { clickPrevented = true; }});
    if (!clickPrevented) location.href = link.href;
    while (timers.length) {
        const timer = timers.shift();
        timerDelays.push(timer.delay);
        timer.callback();
    }
}
process.stdout.write(JSON.stringify({
    arrivalEvents, beforeTimers, events, timeline, navigations, requests,
    timerDelays, clickPrevented, clickListener: clicks.has('click'),
}));
