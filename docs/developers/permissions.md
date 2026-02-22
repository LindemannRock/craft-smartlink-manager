# Permissions @since(5.0.0)

SmartLink Manager registers granular permissions that can be assigned to user groups via **Settings → Users → User Groups → [Group Name] → SmartLink Manager**.

## Permission Structure

### Smart Links

| Permission | Description |
|------------|-------------|
| **`smartLinkManager:manageLinks`** | Manage smart links (parent — grants CP section access) |
| └─ `smartLinkManager:createLinks` | Create smart links |
| └─ `smartLinkManager:editLinks` | Edit smart links |
| └─ `smartLinkManager:deleteLinks` | Delete smart links |

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
