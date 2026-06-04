# Console Commands

SmartLink Manager provides console commands for security setup and testing.

## Console Help

Use the plugin-level help command to see the available SmartLink Manager commands and focused guidance for each workflow:

```bash title="DDEV"
ddev craft smartlink-manager/help
ddev craft smartlink-manager/help security/generate-salt
ddev craft smartlink-manager/help demo/add-qr-click
```

Craft's native command help is still available when you need the exact Yii option signature:

```bash title="DDEV"
ddev craft help smartlink-manager/security/generate-salt
ddev craft help smartlink-manager/demo/add-qr-click
```

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
