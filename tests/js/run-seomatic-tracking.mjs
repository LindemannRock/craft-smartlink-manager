// Executes the PHP-rendered templates without external navigation or analytics requests.
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const input = JSON.parse(readFileSync(0, 'utf8'));
const events = [{event: 'already_queued'}];
const timeline = [];
let now = 0;
const eventTimes = [];
events.push = function (event) {
    timeline.push(event.event);
    eventTimes.push({event: event.event, time: now});
    return Array.prototype.push.call(this, event);
};
const timers = [];
const listeners = new Map();
const clicks = new Map();
const navigations = [];
const navigationTimes = [];
const requests = [];
const location = {
    search: input.search ?? '',
    replace(url) {
        navigations.push(url);
        navigationTimes.push(now);
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
    dataLayer: input.dataLayerName && input.dataLayerName !== 'dataLayer' ? [{event: 'default_queue_untouched'}] : events,
    document: {
        addEventListener(name, listener) { listeners.set(name, listener); },
        querySelectorAll(selector) {
            if (selector !== 'a[href*="smartlink-manager/redirect/go"]') {
                throw new Error('Unexpected tracked-link selector: ' + selector);
            }
            return [link];
        },
    },
    setTimeout(callback, delay) { timers.push({callback, delay, due: now + delay}); },
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
context[input.dataLayerName ?? 'dataLayer'] = events;
for (const script of input.html.matchAll(/<script>([\s\S]*?)<\/script>/g)) {
    vm.runInContext(script[1], context, {timeout: 1000});
}
const arrivalEvents = events.map(event => event.event);
listeners.get('DOMContentLoaded')?.();
// Drain promises from the resolver before advancing the explicitly owned timers.
await new Promise(resolve => setImmediate(resolve));
const beforeTimers = events.map(event => event.event);
const timerDelays = [];
let clickPrevented = false;
function clickButton() {
    const event = {
        preventDefault() { clickPrevented = true; },
        target: {closest() { return link; }},
    };
    clicks.get('click')?.(event);
    listeners.get('click')?.(event);
    if (!clickPrevented) location.href = link.href;
}
function advanceClock(target) {
    timers.sort((a, b) => a.due - b.due);
    while (timers.length && timers[0].due <= target) {
        const timer = timers.shift();
        now = timer.due;
        timerDelays.push(timer.delay);
        timer.callback();
        timers.sort((a, b) => a.due - b.due);
    }
    now = target;
}
const checkpoints = [];
if (input.clickAt !== undefined) {
    advanceClock(input.clickAt);
    clickButton();
}
for (const time of input.checkpoints ?? []) {
    advanceClock(time);
    checkpoints.push({time, navigations: [...navigations], events: events.map(event => event.event)});
}
while (timers.length) {
    advanceClock(Math.min(...timers.map(timer => timer.due)));
}
if (input.click) {
    clickButton();
    while (timers.length) {
        advanceClock(Math.min(...timers.map(timer => timer.due)));
    }
}
process.stdout.write(JSON.stringify({
    arrivalEvents, beforeTimers, events, timeline, navigations, requests,
    timerDelays, clickPrevented, clickListener: clicks.has('click'),
    checkpoints, eventTimes, navigationTimes, defaultEvents: context.dataLayer,
}));
