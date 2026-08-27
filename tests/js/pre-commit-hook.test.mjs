import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {spawnSync} from 'node:child_process';
import {chmodSync, cpSync, mkdirSync, mkdtempSync, readFileSync, readdirSync, rmSync, writeFileSync} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';

const packageRoot = path.resolve(import.meta.dirname, '../..');
const hookSource = path.join(packageRoot, '.githooks/pre-commit');

function executable(pathname, source) {
    writeFileSync(pathname, source, {mode: 0o700});
    chmodSync(pathname, 0o700);
}

function fileSnapshot(root) {
    const entries = [];
    const visit = (directory) => {
        for (const entry of readdirSync(directory, {withFileTypes: true})) {
            const pathname = path.join(directory, entry.name);
            if (entry.isDirectory()) {
                visit(pathname);
                continue;
            }
            const relative = path.relative(root, pathname);
            const digest = createHash('sha256').update(readFileSync(pathname)).digest('hex');
            entries.push(`${relative}:${digest}`);
        }
    };
    visit(root);
    return entries.sort();
}

function fixture({workspace = true, ddevExit = 0, provideDdev = true} = {}) {
    const root = mkdtempSync(path.join(os.tmpdir(), 'smartlink-manager-hook-'));
    const currentPackage = workspace
        ? path.join(root, 'plugins/smartlink-manager')
        : path.join(root, 'smartlink-manager');
    const binRoot = path.join(root, 'bin');
    const logPath = path.join(root, 'commands.log');
    mkdirSync(path.join(currentPackage, '.githooks'), {recursive: true});
    mkdirSync(binRoot, {recursive: true});
    cpSync(hookSource, path.join(currentPackage, '.githooks/pre-commit'));
    writeFileSync(path.join(currentPackage, 'sentinel.txt'), 'must remain byte-identical\n');
    if (workspace) {
        mkdirSync(path.join(root, '.ddev'), {recursive: true});
        writeFileSync(path.join(root, '.ddev/config.yaml'), 'php_version: "8.3"\n');
    }
    if (provideDdev) {
        executable(path.join(binRoot, 'ddev'), `#!/bin/sh\nprintf '%s\n' "$*" >> "$SMARTLINK_MANAGER_HOOK_TEST_LOG"\nexit ${ddevExit}\n`);
    }
    const before = fileSnapshot(currentPackage);
    const environment = {
        ...process.env,
        PATH: provideDdev ? `${binRoot}:/usr/bin:/bin` : '/usr/bin:/bin',
        SMARTLINK_MANAGER_HOOK_TEST_LOG: logPath,
    };

    return {
        packageRoot: currentPackage,
        before,
        run: () => spawnSync('/bin/bash', [path.join(currentPackage, '.githooks/pre-commit')], {
            cwd: currentPackage,
            encoding: 'utf8',
            env: environment,
        }),
        log: () => {
            try {
                return readFileSync(logPath, 'utf8');
            } catch {
                return '';
            }
        },
        snapshot: () => fileSnapshot(currentPackage),
        cleanup: () => rmSync(root, {recursive: true, force: true}),
    };
}

function assertReadOnly(current) {
    assert.deepEqual(current.snapshot(), current.before);
}

test('workspace hook runs only read-only composer ci through DDEV', () => {
    const current = fixture();
    try {
        const result = current.run();
        assert.equal(result.status, 0, result.stderr);
        assert.equal(current.log().trim(), 'exec cd plugins/smartlink-manager && composer ci');
        assertReadOnly(current);
    } finally {
        current.cleanup();
    }
});

test('workspace hook preserves DDEV failure without host fallback or mutation', () => {
    const current = fixture({ddevExit: 37});
    try {
        assert.equal(current.run().status, 37);
        assert.equal(current.log().trim(), 'exec cd plugins/smartlink-manager && composer ci');
        assertReadOnly(current);
    } finally {
        current.cleanup();
    }
});

test('hook fails clearly when the supported DDEV environment is unavailable', async (context) => {
    for (const [label, options, expected] of [
        ['workspace marker', {workspace: false}, 1],
        ['DDEV command', {provideDdev: false}, 127],
    ]) {
        await context.test(label, () => {
            const current = fixture(options);
            try {
                assert.equal(current.run().status, expected);
                assert.equal(current.log(), '');
                assertReadOnly(current);
            } finally {
                current.cleanup();
            }
        });
    }
});

test('hook source contains no mutable or expanded gate entry point', () => {
    const source = readFileSync(hookSource, 'utf8');
    assert.equal((source.match(/composer ci/g) ?? []).length, 1);
    assert.doesNotMatch(source, /(?:vendor\/bin\/(?:phpstan|ecs)|composer\s+(?:fix-cs|test|ci:full|quality-gate)|--fix|phpunit|npm\s|node\s|act\s)/i);
});
