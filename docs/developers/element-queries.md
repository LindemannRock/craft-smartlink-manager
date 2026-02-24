# Element Queries

SmartLink Manager provides a `SmartLinkQuery` class for querying smart link elements in PHP and Twig. It extends Craft's standard `ElementQuery` and supports all built-in Craft query parameters in addition to smart link-specific ones.

## Basic Usage

In Twig, access queries through `craft.smartLinks`:

```twig
{# All enabled smart links #}
{% set links = craft.smartLinks.all() %}

{# One smart link by slug #}
{% set link = craft.smartLinks.slug('my-app').one() %}

{# First enabled smart link #}
{% set link = craft.smartLinks.active().one() %}
```

In PHP, use the element query directly:

```php
use lindemannrock\smartlinkmanager\elements\SmartLink;

$links = SmartLink::find()
    ->trackAnalytics(true)
    ->limit(10)
    ->all();
```

## Smart Link-Specific Parameters

These parameters are in addition to all standard Craft element query parameters (`id`, `title`, `status`, `orderBy`, `limit`, `offset`, `site`, etc.).

| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | `string\|string[]` | Filter by smart link slug |
| `trackAnalytics` | `bool\|null` | Filter by whether analytics tracking is enabled |
| `qrCodeEnabled` | `bool\|null` | Filter by whether QR code generation is enabled |

> [!NOTE]
> Smart links use a private `_smartLinkSlug` column in the plugin's own table, not the standard `elements_sites.slug` column. The `slug` parameter on the query correctly targets this internal column. Do not attempt to query by `elements_sites.slug` directly.

## Status Filtering

Smart links support four statuses. Use the standard Craft `status` parameter:

```twig
{# Only enabled links (live, not pending or expired) #}
{% set links = craft.smartLinks.status('enabled').all() %}

{# Only disabled links #}
{% set links = craft.smartLinks.status('disabled').all() %}

{# Pending links (postDate is in the future) #}
{% set links = craft.smartLinks.status('pending').all() %}

{# Expired links (dateExpired has passed) #}
{% set links = craft.smartLinks.status('expired').all() %}

{# Any status #}
{% set links = craft.smartLinks.status(null).all() %}
```

The `active()` shortcut on the Twig variable returns only enabled links:

```twig
{% set links = craft.smartLinks.active().all() %}
```

## Examples

### Get a Smart Link by Slug

```twig
{% set link = craft.smartLinks.getBySlug('my-app') %}
{% if link %}
    <a href="{{ link.getUrl() }}">{{ link.title }}</a>
{% endif %}
```

### Get All Links with QR Codes Enabled

```twig
{% set qrLinks = craft.smartLinks.qrCodeEnabled(true).all() %}
{% for link in qrLinks %}
    <img src="{{ link.getQrCodeUrl() }}" alt="{{ link.title }}">
{% endfor %}
```

### Get Links That Are Tracked

```twig
{% set trackedLinks = craft.smartLinks.trackAnalytics(true).orderBy('title').all() %}
```

### Count Total Smart Links

```twig
{% set total = craft.smartLinks.status(null).count() %}
<p>{{ total }} smart links in total</p>
```

### Eager Loading

Use Craft's standard eager loading to reduce queries when rendering lists:

```twig
{% set links = craft.smartLinks
    .with(['image'])
    .active()
    .all() %}

{% for link in links %}
    {% if link.image %}
        <img src="{{ link.image.url }}" alt="{{ link.title }}">
    {% endif %}
{% endfor %}
```

## Standard Craft Parameters

All standard Craft element query parameters work on `SmartLinkQuery`. Commonly used ones:

| Parameter | Example |
|-----------|---------|
| `id` | `.id(42)` |
| `title` | `.title('My App*')` |
| `orderBy` | `.orderBy('title ASC')` |
| `limit` | `.limit(10)` |
| `offset` | `.offset(20)` |
| `site` | `.site('english')` |
| `siteId` | `.siteId(1)` |
| `with` | `.with(['image'])` |

See the [Craft element query documentation](https://craftcms.com/docs/5.x/element-queries.html) for the full list.
