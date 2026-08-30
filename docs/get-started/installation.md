# Installation & Setup

> [!NOTE]
> SmartLink Manager is in active development and not yet available on the Craft Plugin Store. Install via Composer for now.

## Composer

Add the package to your project using Composer and the command line.

1. Open your terminal and go to your Craft project:

```bash
cd /path/to/project
```

2. Then tell Composer to require the plugin, and Craft to install it:

```bash title="Composer"
composer require lindemannrock/craft-smartlink-manager && php craft plugin/install smartlink-manager
```

```bash title="DDEV"
ddev composer require lindemannrock/craft-smartlink-manager && ddev craft plugin/install smartlink-manager
```

3. **Optional** — Enable [Logging Library](https://github.com/LindemannRock/craft-logging-library) for log viewing:

> [!NOTE]
> Logging Library is required by Composer. Install or activate it in Craft to enable log viewing.

```bash title="PHP"
php craft plugin/install logging-library
```

```bash title="DDEV"
ddev craft plugin/install logging-library
```

Or via the Control Panel: **Settings → Plugins → Logging Library → Install**

## Post-Install Setup

After installing, open **SmartLink Manager → Setup** in the Control Panel before creating public smart links or QR landing pages. The setup page checks the required privacy salt and starter templates.

Until both checks pass, every SmartLink Manager screen shows a **"Setup incomplete"** notice with an **Open setup** button — that's expected, not an error. Complete the two steps below and the notice disappears everywhere at once.

### Generate an IP hash salt

Generate a secure salt for analytics privacy and unique visitor tracking:

```bash title="PHP"
php craft smartlink-manager/security/generate-salt
```

```bash title="DDEV"
ddev craft smartlink-manager/security/generate-salt
```

This writes `SMARTLINK_MANAGER_IP_SALT` to your `.env` file. For an existing `.env`, SmartLink Manager first prepares and verifies the complete update in the same directory, including the original file permissions, and replaces `.env` only after those checks pass. If the update fails, the original remains unchanged and the command prints the assignment for you to add manually. Check the directory permissions and available storage before trying again.

SmartLink Manager does not create a persistent `.env` backup. If an older direct-write version damaged the file, restore it from your deployment source, secret manager, or backup. Keep the same salt across all environments — changing it resets unique visitor tracking.

### Copy starter templates

Copy the bundled starter templates into your site's `templates/` folder:

```bash title="PHP"
php craft smartlink-manager/setup/copy-templates
```

```bash title="DDEV"
ddev craft smartlink-manager/setup/copy-templates
```

The command installs only a global fallback for any template that an enabled SmartLink Manager site cannot resolve. It skips copying when every enabled site already has either a `templates/{siteHandle}/...` override or the global fallback, and it never creates or replaces per-site overrides. Review and customize copied templates before going live.

For the full template reference, available variables, and manual copy paths, see [Custom templates](../developers/custom-templates.md).

### Review configuration

See [Configuration](configuration.md) for all available settings. Most can be managed from **SmartLink Manager → Settings** without a config file.
