# Integrations

SmartLink Manager integrates with SEOmatic, Redirect Manager, and Craft Link Field. Each integration is optional and can be enabled or disabled in **Settings → Integrations**.

## What you'll use it for

- **Tag-manager tracking** — push smart link interactions into the GTM/GA4 data layer through SEOmatic.
- **No broken links on slug changes** — let Redirect Manager create an automatic 301 when a smart link's slug changes.
- **Native field picking** — offer SmartLink as a type in Craft's Link field so editors can pick one inline.

![SmartLink Manager integrations settings](images/integrations-settings.webp)

## SEOmatic Integration

When SEOmatic is installed and the integration is enabled, SmartLink Manager registers SmartLinks as a SEOmatic content source and pushes structured data layer events to the GTM/GA4 data layer whenever a smart link is interacted with.

### Event Types

Three event types are dispatched to the data layer:

| Event Name | When It Fires |
|------------|--------------|
| `smart_links_redirect` | A visitor follows the redirect URL |
| `smart_links_qr_scan` | A visitor accesses the QR code endpoint |
| `smart_links_button_click` | A button that calls `renderSeomaticTracking()` is clicked |

The `smart_links_` prefix is configurable via the `seomaticEventPrefix` setting (default `smart_links`) — if you change it, the event names change to match (e.g. `myapp_redirect`).

### Data Layer Structure

Tracking is pushed **client-side** from the rendered redirect or QR page. A redirect event pushes this payload:

```json
{
    "event": "smart_links_redirect",
    "smart_link": {
        "slug": "my-app",
        "title": "My App",
        "platform": "auto",
        "source": "direct",
        "click_type": "redirect"
    }
}
```

QR scan events use `source: "qr"` and `click_type: "qr_scan"`. Button-click events use `click_type: "button_click"` and include the clicked platform, read from the tracked go URL — the `{platform}` segment of `…/smartlink-manager/redirect/go/{slug}/{platform}` (or a `?platform=` query parameter if your custom template adds one), falling back to `unknown` if neither is present.

### Configuration

Enable the integration in **Settings → Integrations → SEOmatic**. The integration is automatically detected. (When SEOmatic isn't installed, see [Integration requirements](#integration-requirements) below for what the card shows.)

### Content SEO and sitemaps

When the integration is enabled, SEOmatic adds a **SmartLinks** source (named after the plugin's display name) in **SEOmatic → Content SEO**. That source lets you manage the SEOmatic metadata bundle for rendered smart link and QR pages, including title, robots, canonical URL, and sitemap settings.

SmartLink Manager sets these defaults:

| Setting | Default |
|---------|---------|
| SEO Title | From the SmartLink title |
| Canonical URL | The smart link's public URL |
| Robots | `all` |
| Sitemap URLs | Off |

If you enable sitemap URLs in SEOmatic, the generated sitemap URLs use the same public URL builder as smart links and QR codes. That means `smartlinkBaseUrl`, custom domains, and multisite tokens such as `{siteHandle}`, `{siteId}`, and `{siteUid}` are respected.

SEOmatic only lists actual Craft field-layout fields as **Source Field** options. Native SmartLink properties such as the built-in description and image are not listed there. To manage SEO descriptions or images for smart links, use one of these approaches:

- Add a SEOmatic SEO field to the SmartLink field layout and edit metadata per smart link.
- Add your own text/asset fields to the SmartLink field layout, then map those fields in SEOmatic Content SEO.

Existing SEOmatic content bundles keep their saved settings. If you enabled the integration before changing defaults, resave or reset the SmartLinks source in SEOmatic to apply the current defaults.

### Using `renderSeomaticTracking()` in Templates

To fire a `smart_links_button_click` event when a user interacts with a specific element on your page, call `renderSeomaticTracking()` in your template:

```twig
<a href="{{ smartLink.getUrl() }}" {{ smartLink.renderSeomaticTracking('button_click')|raw }}>
    Download the App
</a>
```

This outputs a `<script>` block that pushes the event object to `window.dataLayer`. It does not add `data-gtm-*` attributes — use it when you want to fire the event from your own markup.

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

### Fallback lookup

When a visitor hits a smart link **landing URL** whose slug no longer resolves (the smart link was deleted or renamed), SmartLink Manager checks Redirect Manager for a matching redirect before falling back to `notFoundRedirectUrl`. This keeps old bookmarks and QR codes working after a slug change even if no automatic redirect was created.

This applies to unresolved **landing** smart-link URLs. Tracking-hop URLs (the `…/redirect/go/{slug}/{platform}` action) go straight to the configured 404 when they fail — they are not routed through Redirect Manager.

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

All integrations are detected automatically. If the required plugin is not installed, its card still shows with an **Install Plugin** link but the enable toggle is disabled, and no integration code runs until it is installed and enabled.

For the `IntegrationInterface` API reference, see [Integrations API](../developers/integrations.md).
