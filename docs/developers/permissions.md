# Permissions

SmartLink Manager registers granular permissions that can be assigned to user groups via **Settings → Users → User Groups → [Group Name] → SmartLink Manager**.

## Permission Structure

### Smart Links

| Permission | Description |
|------------|-------------|
| **`smartLinkManager:manageLinks`** | Manage smart links (parent — grants CP section access) |
| └─ `smartLinkManager:createLinks` | Create smart links |
| └─ `smartLinkManager:editLinks` | Edit smart links |
| └─ `smartLinkManager:deleteLinks` | Delete smart links |

### Import/Export

| Permission | Description |
|------------|-------------|
| **`smartLinkManager:manageImportExport`** | Manage import/export (parent — grants Import/Export section access) |
| └─ `smartLinkManager:importLinks` | Import links from a CSV file |
| └─ `smartLinkManager:exportLinks` | Export links to a CSV file |
| └─ `smartLinkManager:clearImportHistory` | Clear the import history log |

### Analytics

| Permission | Description |
|------------|-------------|
| **`smartLinkManager:viewAnalytics`** | View analytics (parent) |
| └─ `smartLinkManager:exportAnalytics` | Export analytics data |
| └─ `smartLinkManager:clearAnalytics` | Clear analytics data |

### Cache

| Permission | Description |
|------------|-------------|
| `smartLinkManager:clearCache` | Clear QR code and device detection caches |

### Logs

| Permission | Description |
|------------|-------------|
| **`smartLinkManager:viewLogs`** | View logs (parent) |
| └─ **`smartLinkManager:viewSystemLogs`** | View system logs |
| &nbsp;&nbsp;&nbsp;&nbsp;└─ `smartLinkManager:downloadSystemLogs` | Download system log files |

### Settings

| Permission | Description |
|------------|-------------|
| `smartLinkManager:manageSettings` | Manage plugin settings |

## Checking Permissions

In Twig:

```twig
{% if currentUser.can('smartLinkManager:manageLinks') %}
    {# User can access smart links section #}
{% endif %}

{% if currentUser.can('smartLinkManager:exportAnalytics') %}
    {# User can export analytics data #}
{% endif %}
```

In PHP:

```php
if (Craft::$app->getUser()->checkPermission('smartLinkManager:manageLinks')) {
    // User has permission
}

// In a controller
$this->requirePermission('smartLinkManager:manageLinks');
```

## Nested Permission Pattern

Craft's nested permissions are a UI convenience — the parent permission does not automatically grant child permissions.

- **"Manage" / "View" permissions** are top-level parents (grants access to the CP section)
- **Child permissions** (create, edit, delete, export, clear) control specific operations

To give a user read-only access to analytics, grant `viewAnalytics` without `exportAnalytics` or `clearAnalytics`. For full access, grant the parent plus the specific child permissions needed.

## Multisite: the native `editSite` permission

On multi-site installs, SmartLink Manager's own permissions are not the whole story. Saving, deleting, or duplicating a smart link also requires Craft's **native site permission** (`editSite:<site-uid>` — the "Edit site" checkbox under the site's name in the user group's permissions) for the site the link belongs to. A user with `editLinks` but no edit access to the link's site cannot modify that link.

The same site scoping applies across the plugin:

- **CSV export** includes only links from sites that are both enabled for the plugin *and* editable by the exporting user. If no sites qualify, the export shows "No smart links to export."
- **CSV import** counts a row as failed when it targets a site the importing user can't edit (or a site not enabled for the plugin) — see [Import & Export](../feature-tour/import-export.md).

On single-site installs none of this applies — the plugin's own permissions are sufficient.
