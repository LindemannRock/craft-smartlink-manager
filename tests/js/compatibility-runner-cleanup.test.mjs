import assert from 'node:assert/strict';
import {spawn, spawnSync} from 'node:child_process';
import {createHash} from 'node:crypto';
import {
    chmodSync,
    cpSync,
    existsSync,
    mkdirSync,
    mkdtempSync,
    readFileSync,
    readdirSync,
    realpathSync,
    rmSync,
    statSync,
    writeFileSync,
} from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';

const packageRoot = path.resolve(import.meta.dirname, '../..');
const runnerSource = path.join(packageRoot, 'scripts/test-craft-compat');
const canonicalRunnerSha256 = '11f17cd4c1ec9b4a1e07ae47e5ec6ca3080f6cd0587606e5741e3b4b202e3f40';
const canonicalRunnerBytes = 12444;

function executable(pathname, source) {
    writeFileSync(pathname, source, {mode: 0o700});
    chmodSync(pathname, 0o700);
}

function sha256(pathname) {
    return createHash('sha256').update(readFileSync(pathname)).digest('hex');
}

function fixture({
    install = false,
    failureMatch = '',
    cleanupExit = 0,
    filesystemCleanupExit = 0,
    waitMatch = '',
    requestedPhp = '8.3',
    actualPhp = requestedPhp,
    ddevAvailable = true,
    pluginEnabled = true,
    smokePresent = true,
    smokeCommand = '',
    tempRoot,
} = {}) {
    const root = mkdtempSync(path.join(os.tmpdir(), 'smartlink-manager-compat-runner-'));
    const fixturePackageRoot = path.join(root, 'package');
    const callerRoot = path.join(root, 'outside-package');
    const binRoot = path.join(root, 'bin');
    const ownedTempRoot = tempRoot ?? path.join(root, 'compat-temp');
    const resourceRoot = path.join(root, 'ddev-resources');
    const logPath = path.join(root, 'commands.log');
    const sentinelTempRoot = ownedTempRoot === '/' || ownedTempRoot === os.homedir()
        ? path.join(root, 'unsafe-temp-neighbor-root')
        : ownedTempRoot;
    const tempSentinel = path.join(sentinelTempRoot, 'neighbor-project');
    const resourceSentinel = path.join(resourceRoot, 'neighbor-resource');
    const tempSentinelBytes = Buffer.from('neighbor project\n\0exact bytes\n');
    const resourceSentinelBytes = Buffer.from('neighbor ddev\n\0exact bytes\n');
    const tempSentinelMode = 0o641;
    const resourceSentinelMode = 0o604;

    mkdirSync(path.join(fixturePackageRoot, 'scripts'), {recursive: true});
    mkdirSync(callerRoot, {recursive: true});
    mkdirSync(binRoot, {recursive: true});
    mkdirSync(ownedTempRoot, {recursive: true});
    mkdirSync(sentinelTempRoot, {recursive: true});
    mkdirSync(resourceRoot, {recursive: true});
    cpSync(runnerSource, path.join(fixturePackageRoot, 'scripts/test-craft-compat'));
    chmodSync(path.join(fixturePackageRoot, 'scripts/test-craft-compat'), 0o755);
    writeFileSync(path.join(fixturePackageRoot, 'composer.json'), JSON.stringify({
        name: 'lindemannrock/craft-smartlink-manager',
        type: 'craft-plugin',
        extra: {handle: 'smartlink-manager'},
    }));
    if (smokePresent) {
        executable(path.join(fixturePackageRoot, 'scripts/smoke-test'), '#!/bin/sh\nexit 0\n');
    }
    writeFileSync(tempSentinel, tempSentinelBytes, {mode: tempSentinelMode});
    chmodSync(tempSentinel, tempSentinelMode);
    writeFileSync(resourceSentinel, resourceSentinelBytes, {mode: resourceSentinelMode});
    chmodSync(resourceSentinel, resourceSentinelMode);

executable(path.join(binRoot, 'php'), `#!/bin/bash
script="\${2:-}"
if [[ "$script" == *'composer.lock'* ]]; then
  printf '%s\n' 'logging-library'
elif [[ "$script" == *'json_encode(["type"'* ]]; then
  printf '{"type":"path","url":"%s","options":{"symlink":false,"versions":{"%s":"%s"}}}' "$3" "$4" "$5"
elif [[ "$script" == *'["extra"]["handle"]'* ]]; then
  printf '%s' 'smartlink-manager'
elif [[ "$script" == *'["type"]'* ]]; then
  printf '%s' 'craft-plugin'
elif [[ "$script" == *'["name"]'* ]]; then
  printf '%s' 'lindemannrock/craft-smartlink-manager'
fi
`);

    executable(path.join(binRoot, 'composer'), `#!/bin/bash
printf 'composer:%s\n' "$*" >> "$SMARTLINK_MANAGER_COMPAT_TEST_LOG"
if [[ -n "$SMARTLINK_MANAGER_COMPAT_WAIT_MATCH" && "composer $*" == *"$SMARTLINK_MANAGER_COMPAT_WAIT_MATCH"* ]]; then
  while :; do sleep 1; done
fi
if [[ -n "$SMARTLINK_MANAGER_COMPAT_FAIL_MATCH" && "composer $*" == *"$SMARTLINK_MANAGER_COMPAT_FAIL_MATCH"* ]]; then exit 41; fi
if [[ "$1" == "create-project" ]]; then
  project_dir="$3"
  mkdir -p "$project_dir"
  printf '{"require-dev":{"fixture":"1"}}\n' > "$project_dir/composer.json"
  printf '{"packages":[]}\n' > "$project_dir/composer.lock"
fi
exit 0
`);

    executable(path.join(binRoot, 'rm'), `#!/bin/bash
printf 'rm:%s\n' "$*" >> "$SMARTLINK_MANAGER_COMPAT_TEST_LOG"
if [[ "$SMARTLINK_MANAGER_COMPAT_FILESYSTEM_CLEANUP_EXIT" -ne 0 ]]; then exit "$SMARTLINK_MANAGER_COMPAT_FILESYSTEM_CLEANUP_EXIT"; fi
/bin/rm "$@"
`);

    if (ddevAvailable) {
        executable(path.join(binRoot, 'ddev'), `#!/bin/bash
printf 'ddev:%s\n' "$*" >> "$SMARTLINK_MANAGER_COMPAT_TEST_LOG"
if [[ "$1" == "config" ]]; then
  project_name=""
  for argument in "$@"; do
    case "$argument" in --project-name=*) project_name="\${argument#*=}" ;; esac
  done
  case "$project_name" in ""|-*|*-|*[!a-z0-9-]*) exit 42 ;; esac
  printf '%s\n' "$project_name" > "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/current-project"
  mkdir -p "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name"
  touch "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name/database"
  touch "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name/container"
  touch "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name/network"
  touch "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name/volume"
  touch "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name/process"
fi
if [[ "$1" == "delete" ]]; then
  project_name="\${@: -1}"
  if [[ "$SMARTLINK_MANAGER_COMPAT_CLEANUP_EXIT" -ne 0 ]]; then exit "$SMARTLINK_MANAGER_COMPAT_CLEANUP_EXIT"; fi
  /bin/rm -rf "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/$project_name"
  /bin/rm -f "$SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT/current-project"
  exit 0
fi
if [[ -n "$SMARTLINK_MANAGER_COMPAT_WAIT_MATCH" && "ddev $*" == *"$SMARTLINK_MANAGER_COMPAT_WAIT_MATCH"* ]]; then
  while :; do sleep 1; done
fi
if [[ -n "$SMARTLINK_MANAGER_COMPAT_FAIL_MATCH" && "ddev $*" == *"$SMARTLINK_MANAGER_COMPAT_FAIL_MATCH"* ]]; then exit 41; fi
if [[ "$1" == "exec" && "$2" == "php" ]]; then
  case "$4" in
    *PHP_MAJOR_VERSION*) printf '%s' "$SMARTLINK_MANAGER_COMPAT_ACTUAL_PHP_ROW" ;;
    *PHP_VERSION*) printf '%s' "$SMARTLINK_MANAGER_COMPAT_ACTUAL_PHP_FULL" ;;
  esac
  exit 0
fi
if [[ "$1" == "exec" && "$2" == "test" && "$3" == "-f" ]]; then
  test -f "$4"
  exit $?
fi
if [[ "$1 $2" == "craft plugin/list" ]]; then
  printf ' logging-library fixture Yes Yes\n'
  if [[ "$SMARTLINK_MANAGER_COMPAT_PLUGIN_ENABLED" == "1" ]]; then
    printf ' smartlink-manager fixture Yes Yes\n'
  else
    printf ' smartlink-manager fixture Yes No\n'
  fi
fi
exit 0
`);
    }

    const argumentsList = ['^5.10', 'dev-main'];
    if (install) {
        argumentsList.push('--install');
    }
    argumentsList.push('--php-version', requestedPhp);
    if (smokeCommand !== '') {
        argumentsList.push('--smoke-command', smokeCommand);
    }
    const environment = {
        ...process.env,
        PATH: `${binRoot}:/usr/bin:/bin`,
        CRAFT_COMPAT_TEMP_ROOT: ownedTempRoot,
        SMARTLINK_MANAGER_COMPAT_TEST_LOG: logPath,
        SMARTLINK_MANAGER_COMPAT_FAIL_MATCH: failureMatch,
        SMARTLINK_MANAGER_COMPAT_CLEANUP_EXIT: String(cleanupExit),
        SMARTLINK_MANAGER_COMPAT_FILESYSTEM_CLEANUP_EXIT: String(filesystemCleanupExit),
        SMARTLINK_MANAGER_COMPAT_WAIT_MATCH: waitMatch,
        SMARTLINK_MANAGER_DDEV_RESOURCE_ROOT: resourceRoot,
        SMARTLINK_MANAGER_COMPAT_ACTUAL_PHP_ROW: actualPhp,
        SMARTLINK_MANAGER_COMPAT_ACTUAL_PHP_FULL: `${actualPhp}.30`,
        SMARTLINK_MANAGER_COMPAT_PLUGIN_ENABLED: pluginEnabled ? '1' : '0',
    };
    const command = '/bin/bash';
    const args = [path.join(fixturePackageRoot, 'scripts/test-craft-compat'), ...argumentsList];

    function logLines() {
        return existsSync(logPath) ? readFileSync(logPath, 'utf8').trim().split('\n').filter(Boolean) : [];
    }

    return {
        root,
        fixturePackageRoot,
        callerRoot,
        tempRoot: ownedTempRoot,
        sentinelTempRoot,
        resourceRoot,
        tempSentinel,
        resourceSentinel,
        tempSentinelBytes,
        resourceSentinelBytes,
        tempSentinelMode,
        resourceSentinelMode,
        command,
        args,
        environment,
        run(extraArgs = []) {
            return spawnSync(command, [...args, ...extraArgs], {
                cwd: callerRoot,
                env: environment,
                encoding: 'utf8',
            });
        },
        spawn() {
            return spawn(command, args, {
                cwd: callerRoot,
                env: environment,
                detached: true,
                stdio: ['ignore', 'pipe', 'pipe'],
            });
        },
        log: () => logLines().join('\n'),
        logLines,
        ownedIdentity(result) {
            const match = result.stdout.match(/^Owned project\/DDEV identity: (.+) \/ (.+)$/m);
            assert.ok(match, `${result.stdout}\n${result.stderr}`);
            return {project: match[1], ddev: match[2]};
        },
        assertNeighborsExact() {
            assert.deepEqual(readFileSync(tempSentinel), tempSentinelBytes);
            assert.deepEqual(readFileSync(resourceSentinel), resourceSentinelBytes);
            assert.equal(statSync(tempSentinel).mode & 0o777, tempSentinelMode);
            assert.equal(statSync(resourceSentinel).mode & 0o777, resourceSentinelMode);
        },
        assertOwnedStateRemoved({expectDdev = install, filesystemAttempts = 1} = {}) {
            assert.deepEqual(readdirSync(ownedTempRoot), ['neighbor-project']);
            assert.deepEqual(readdirSync(resourceRoot), ['neighbor-resource']);
            this.assertNeighborsExact();
            const lines = logLines();
            assert.equal(lines.filter((line) => line.startsWith('rm:')).length, filesystemAttempts, lines.join('\n'));
            assert.equal(lines.filter((line) => line.startsWith('ddev:delete ')).length, expectDdev ? 1 : 0, lines.join('\n'));
            assert.doesNotMatch(lines.join('\n'), /ddev:(?:list|describe)|docker:|rm:.*(?:\*|craft-compat-[^ ]+\*)/);
        },
        cleanup() {
            rmSync(root, {recursive: true, force: true});
        },
    };
}

async function waitForLog(current, pattern) {
    for (let attempt = 0; attempt < 200; attempt++) {
        if (pattern.test(current.log())) {
            return;
        }
        await new Promise((resolve) => setTimeout(resolve, 10));
    }
    throw new Error(`Timed out waiting for compatibility runner log:\n${current.log()}`);
}

test('runner has the canonical fingerprint, byte count, and executable mode', () => {
    assert.equal(sha256(runnerSource), canonicalRunnerSha256);
    assert.equal(statSync(runnerSource).size, canonicalRunnerBytes);
    assert.notEqual(statSync(runnerSource).mode & 0o111, 0);
});

test('caller location is irrelevant and repeated invocations receive distinct exact identities', () => {
    const current = fixture();
    try {
        const first = current.run();
        const second = current.run();
        assert.equal(first.status, 0, `${first.stdout}\n${first.stderr}`);
        assert.equal(second.status, 0, `${second.stdout}\n${second.stderr}`);
        const firstIdentity = current.ownedIdentity(first);
        const secondIdentity = current.ownedIdentity(second);
        assert.notEqual(firstIdentity.project, secondIdentity.project);
        assert.notEqual(firstIdentity.ddev, secondIdentity.ddev);
        assert.match(first.stdout, new RegExp(`Package source: ${realpathSync(current.fixturePackageRoot).replaceAll('/', '\\/')}`));
        assert.match(current.log(), /repositories\.local-package .*"symlink":false/);
        current.assertOwnedStateRemoved({expectDdev: false, filesystemAttempts: 2});
    } finally {
        current.cleanup();
    }
});

test('unsafe temporary roots are rejected before project or command creation', () => {
    const current = fixture({tempRoot: '/'});
    try {
        const result = current.run();
        assert.equal(result.status, 1);
        assert.match(result.stderr, /Refusing unsafe compatibility temporary root: \//);
        assert.equal(current.log(), '');
    } finally {
        current.cleanup();
    }
});

test('Composer-only lifecycle removes the exact project on success and failures', async (context) => {
    for (const [name, failureMatch, expected] of [
        ['success', '', 0],
        ['create-project failure', 'composer create-project', 41],
        ['Craft dependency failure', 'composer require craftcms/cms', 41],
        ['candidate repository configuration failure', 'composer config --json repositories.local-package', 41],
        ['candidate dependency failure', 'composer require lindemannrock/craft-smartlink-manager', 41],
    ]) {
        await context.test(name, () => {
            const current = fixture({failureMatch});
            try {
                const result = current.run();
                assert.equal(result.status, expected, `${result.stdout}\n${result.stderr}`);
                assert.doesNotMatch(result.stdout, /Project left at/);
                current.assertOwnedStateRemoved({expectDdev: false});
            } finally {
                current.cleanup();
            }
        });
    }
});

test('install lifecycle cleans every partial DDEV and filesystem failure path', async (context) => {
    for (const [name, options, expected] of [
        ['missing DDEV', {ddevAvailable: false}, 1],
        ['DDEV configuration failure', {failureMatch: 'ddev config '}, 41],
        ['DDEV start failure', {failureMatch: 'ddev start'}, 41],
        ['Craft install failure', {failureMatch: 'ddev craft install'}, 41],
        ['dependency plugin installation failure', {failureMatch: 'ddev craft plugin/install logging-library'}, 41],
        ['SmartLink installation failure', {failureMatch: 'ddev craft plugin/install smartlink-manager'}, 41],
        ['SmartLink enabled-state failure', {pluginEnabled: false}, 1],
    ]) {
        await context.test(name, () => {
            const current = fixture({install: true, ...options});
            try {
                const result = current.run();
                assert.equal(result.status, expected, `${result.stdout}\n${result.stderr}`);
                current.assertOwnedStateRemoved({expectDdev: options.ddevAvailable !== false});
            } finally {
                current.cleanup();
            }
        });
    }
});

test('requested PHP row must match actual DDEV PHP before installation and smoke', () => {
    const current = fixture({install: true, requestedPhp: '8.3', actualPhp: '8.4'});
    try {
        const result = current.run();
        assert.equal(result.status, 1, `${result.stdout}\n${result.stderr}`);
        assert.match(result.stdout, /Requested PHP row: 8\.3/);
        assert.match(result.stdout, /Actual DDEV PHP: 8\.4\.30/);
        assert.match(result.stderr, /DDEV PHP row mismatch: requested 8\.3, got 8\.4/);
        assert.doesNotMatch(current.log(), /ddev:craft install|ddev:exec env/);
        current.assertOwnedStateRemoved();
    } finally {
        current.cleanup();
    }
});

test('mandatory package smoke runs before optional additional smoke and propagates either failure', async (context) => {
    for (const [name, failureMatch, expected, expectAdditional] of [
        ['package smoke success', '', 0, true],
        ['package smoke failure', 'ddev exec env', 41, false],
        ['additional smoke failure', 'ddev exec bash -lc', 41, true],
    ]) {
        await context.test(name, () => {
            const current = fixture({
                install: true,
                failureMatch,
                smokeCommand: 'printf additional-smoke',
            });
            try {
                const result = current.run();
                assert.equal(result.status, expected, `${result.stdout}\n${result.stderr}`);
                const log = current.log();
                const packageSmoke = log.indexOf('ddev:exec env PLUGIN_NAME=lindemannrock/craft-smartlink-manager');
                const additionalSmoke = log.indexOf('ddev:exec bash -lc printf additional-smoke');
                assert.notEqual(packageSmoke, -1, log);
                if (expectAdditional) {
                    assert.ok(packageSmoke < additionalSmoke, log);
                } else {
                    assert.equal(additionalSmoke, -1, log);
                }
                assert.ok(log.indexOf('ddev:craft plugin/install logging-library') < log.indexOf('ddev:craft plugin/install smartlink-manager'));
                current.assertOwnedStateRemoved();
            } finally {
                current.cleanup();
            }
        });
    }
});

test('install mode rejects missing package smoke and offers no smoke-skip option', async (context) => {
    await context.test('missing package smoke', () => {
        const current = fixture({install: true, smokePresent: false});
        try {
            const result = current.run();
            assert.equal(result.status, 1, `${result.stdout}\n${result.stderr}`);
            assert.match(result.stderr, /--install requires package-owned meaningful runtime smoke/);
            current.assertOwnedStateRemoved();
        } finally {
            current.cleanup();
        }
    });
    await context.test('removed skip option', () => {
        const current = fixture();
        try {
            const result = current.run(['--skip-smoke']);
            assert.equal(result.status, 1);
            assert.match(result.stderr, /Unknown option: --skip-smoke/);
            assert.equal(current.log(), '');
            current.assertNeighborsExact();
        } finally {
            current.cleanup();
        }
    });
});

test('INT, TERM, and HUP retain signal status and clean exact DDEV and filesystem resources', async (context) => {
    for (const [signal, expected] of [['SIGINT', 130], ['SIGTERM', 143], ['SIGHUP', 129]]) {
        await context.test(signal, async () => {
            const current = fixture({install: true, waitMatch: 'ddev start'});
            try {
                const child = current.spawn();
                await waitForLog(current, /ddev:start/);
                process.kill(-child.pid, signal);
                const result = await new Promise((resolve) => child.once('close', (code, closeSignal) => resolve({code, signal: closeSignal})));
                assert.ok(result.code === expected || result.signal === signal, JSON.stringify(result));
                current.assertOwnedStateRemoved();
            } finally {
                current.cleanup();
            }
        });
    }
});

test('operational failure takes precedence over cleanup failure and cleanup-only failure is nonzero', async (context) => {
    await context.test('operational status is primary', () => {
        const current = fixture({install: true, failureMatch: 'ddev craft install', cleanupExit: 88});
        try {
            const result = current.run();
            assert.equal(result.status, 41, `${result.stdout}\n${result.stderr}`);
            assert.match(result.stderr, /Failed to remove owned DDEV project .* \(exit 88\)/);
            assert.deepEqual(readdirSync(current.tempRoot), ['neighbor-project']);
            assert.equal(current.logLines().filter((line) => line.startsWith('ddev:delete ')).length, 1);
            current.assertNeighborsExact();
        } finally {
            current.cleanup();
        }
    });
    await context.test('cleanup-only status fails', () => {
        const current = fixture({install: true, cleanupExit: 88});
        try {
            const result = current.run();
            assert.equal(result.status, 88, `${result.stdout}\n${result.stderr}`);
            assert.match(result.stderr, /Failed to remove owned DDEV project .* \(exit 88\)/);
            assert.deepEqual(readdirSync(current.tempRoot), ['neighbor-project']);
            assert.equal(current.logLines().filter((line) => line.startsWith('ddev:delete ')).length, 1);
            current.assertNeighborsExact();
        } finally {
            current.cleanup();
        }
    });
    await context.test('filesystem cleanup-only status fails', () => {
        const current = fixture({filesystemCleanupExit: 89});
        try {
            const result = current.run();
            assert.equal(result.status, 89, `${result.stdout}\n${result.stderr}`);
            assert.match(result.stderr, /Failed to remove owned compatibility project .* \(exit 89\)/);
            assert.equal(current.logLines().filter((line) => line.startsWith('rm:')).length, 1);
            current.assertNeighborsExact();
        } finally {
            current.cleanup();
        }
    });
});
