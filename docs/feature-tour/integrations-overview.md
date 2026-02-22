# Integrations

SmartLink Manager integrates with SEOmatic, Redirect Manager, and Craft Link Field. Each integration is optional and can be enabled or disabled in **Settings → Integrations**.

## SEOmatic Integration

When SEOmatic is installed and the integration is enabled, SmartLink Manager pushes structured data layer events to the GTM/GA4 data layer whenever a smart link is interacted with.

### Event Types

Three event types are dispatched to the data layer:

| Event Name | When It Fires |
|------------|--------------|
| `smart_links_redirect` | A visitor follows the redirect URL |
| `smart_links_qr_scan` | A visitor accesses the QR code endpoint |
| `smart_links_button_click` | A button that calls `renderSeomaticTracking()` is clicked |

### Data Layer Structure

Each event pushes the following payload:

```json
{
    "event": "smart_links_redirect",
    "smart_link_id": 42,
    "smart_link_slug": "my-app",
    "smart_link_title": "My App",
    "device_type": "phone",
    "device_os": "iOS",
    "platform": "ios",
    "language": "en"
}
```

### Configuration

Enable the integration in **Settings → Integrations → SEOmatic**. The integration is automatically detected — if SEOmatic is not installed, the option is not shown.

### Using `renderSeomaticTracking()` in Templates

To fire a `smart_links_button_click` event when a user interacts with a specific element on your page, call `renderSeomaticTracking()` in your template:

```twig
<a href="{{ smartLink.getUrl() }}" {{ smartLink.renderSeomaticTracking('button_click')|raw }}>
    Download the App
</a>
```

This renders the necessary `data-gtm-*` attributes or inline tracking markup that SEOmatic uses to push the event to the data layer.

## Redirect Manager Integration

When Redirect Manager is installed and the integration is enabled, SmartLink Manager automatically creates a 301 redirect whenever a smart link's slug is changed.

### How It Works

1. You edit a smart link and change its slug from `my-app` to `my-app-v2`
2. SmartLink Manager detects the slug change on save
3. A 301 redirect is registered in Redirect Manager: `/go/my-app` → `/go/my-app-v2`
4. Any existing links or QR codes using the old slug continue to work

This prevents broken links when you need to rename or reorganize your smart links.

### Configuration

Enable the integration in **Settings → Integrations → Redirect Manager**. The integration requires Redirect Manager to be installed.

> [!NOTE]
> The redirect is created using the `slugPrefix` setting. If you change the prefix, existing redirects created under the old prefix will still point to the old prefix path.

## Craft Link Field Integration

SmartLink Manager registers itself as a link type option for Craft's native [Link field](https://craftcms.com/docs/5.x/reference/field-types/link.html) (available in Craft CMS 5.3+).

Users can select a **SmartLink** as the link target in any Link field — anywhere a URL, entry, category, or asset would normally appear.

### Value Format

When a Link field contains a SmartLink selection, the field value resolves to the smart link's public redirect URL (`/{slugPrefix}/{slug}`), so it works transparently in templates:

```twig
{# In a matrix block with a Link field named 'ctaLink' #}
<a href="{{ block.ctaLink.url }}">{{ block.ctaLink.text }}</a>
```

If the smart link is disabled or expired, the URL returns `null` — the same behavior as a disabled entry in a Link field.

### SmartLinkType

The `SmartLinkType` class is registered automatically when Link Field is installed. There is no additional configuration required.

## Integration Requirements

| Integration | Required Plugin |
|-------------|----------------|
| SEOmatic | `nystudio107/craft-seomatic` |
| Redirect Manager | `lindemannrock/craft-redirect-manager` |
| Craft Link Field | Craft CMS 5.3+ (native Link field) |

All integrations are detected automatically. If the required plugin is not installed, the integration option is hidden in settings and no integration code runs.

For the `IntegrationInterface` API reference, see [Integrations API](../developers/integrations.md).
