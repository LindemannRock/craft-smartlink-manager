# Console Commands

SmartLink Manager provides console commands for security setup and testing.

## Console Help

Use the plugin-level help command to see the available SmartLink Manager commands:

```bash title="PHP"
php craft smartlink-manager/help
```

```bash title="DDEV"
ddev craft smartlink-manager/help
```

Pass a command name when you want focused guidance for one workflow:

Setup templates:

```bash title="PHP"
php craft smartlink-manager/help setup/copy-templates
```

```bash title="DDEV"
ddev craft smartlink-manager/help setup/copy-templates
```

Generate the analytics salt:

```bash title="PHP"
php craft smartlink-manager/help security/generate-salt
```

```bash title="DDEV"
ddev craft smartlink-manager/help security/generate-salt
```

Add a demo QR click:

```bash title="PHP"
php craft smartlink-manager/help demo/add-qr-click
```

```bash title="DDEV"
ddev craft smartlink-manager/help demo/add-qr-click
```

Craft's native command help is still available when you need the exact Yii option signature:

Generate the analytics salt:

```bash title="PHP"
php craft help smartlink-manager/security/generate-salt
```

```bash title="DDEV"
ddev craft help smartlink-manager/security/generate-salt
```

Setup templates:

```bash title="PHP"
php craft help smartlink-manager/setup/copy-templates
```

```bash title="DDEV"
ddev craft help smartlink-manager/setup/copy-templates
```

Add a demo QR click:

```bash title="PHP"
php craft help smartlink-manager/demo/add-qr-click
```

```bash title="DDEV"
ddev craft help smartlink-manager/demo/add-qr-click
```

## Copy Starter Templates @since(5.36.0)

Copies bundled starter templates into the configured paths in your site's `templates/` folder. This makes the redirect and QR pages render without Twig template loading errors.

```bash title="PHP"
php craft smartlink-manager/setup/copy-templates
```

```bash title="DDEV"
ddev craft smartlink-manager/setup/copy-templates
```

The command will:

1. Read the current SmartLink Manager template settings.
2. Copy only missing starter templates by default.
3. Create destination folders automatically.
4. Skip existing destination templates unless you target one template interactively or pass `--overwrite`.

| Option | Description |
|--------|-------------|
| `--template=redirect` | Copy only the redirect/landing template |
| `--template=qr` | Copy only the QR display template |
| `--overwrite` | Replace existing destination templates without prompting |

Copy one template:

```bash title="PHP"
php craft smartlink-manager/setup/copy-templates --template=redirect
```

```bash title="DDEV"
ddev craft smartlink-manager/setup/copy-templates --template=redirect
```

Replace one template:

```bash title="PHP"
php craft smartlink-manager/setup/copy-templates --template=qr --overwrite
```

```bash title="DDEV"
ddev craft smartlink-manager/setup/copy-templates --template=qr --overwrite
```

Use this after installing the plugin, after changing template paths in settings, or when the setup page reports missing starter templates. Review and customize copied templates before going live.

## Generate IP Hash Salt @since(5.1.0)

Generates a cryptographically secure 64-character hex salt for IP address hashing and optionally writes it to your `.env` file.

```bash title="PHP"
php craft smartlink-manager/security/generate-salt
```

```bash title="DDEV"
ddev craft smartlink-manager/security/generate-salt
```

The command will:

1. Generate a secure random salt
2. Check for an existing `SMARTLINK_MANAGER_IP_SALT` in `.env`
3. If found, ask for confirmation before replacing (warns about breaking unique visitor tracking)
4. If not found, append the new variable to `.env`

> [!WARNING]
> Changing the salt after analytics have been collected will break unique visitor tracking. All existing analytics use hash values from the old salt.

---

## Add Demo QR Click

Adds a simulated QR code scan to a smart link's analytics. Useful for testing analytics tracking and QR scan reporting during development.

> [!WARNING]
> This command writes analytics data. Use it in development or test environments unless you intentionally want demo rows in production analytics.

```bash title="PHP"
php craft smartlink-manager/demo/add-qr-click --id=42
```

```bash title="DDEV"
ddev craft smartlink-manager/demo/add-qr-click --id=42
```

| Option | Type | Description |
|--------|------|-------------|
| `--id` | `int` | Optional smart link ID. If omitted, uses the first smart link found |

The command creates a simulated iPhone/iOS QR scan event with device info and saves it to analytics.
