# Fields

SmartLink Manager provides a **SmartLinkField** — a custom element picker field that lets editors attach smart links to any Craft element.

## SmartLinkField

The SmartLinkField is a standard Craft element relation field. Add it to any field layout through **Settings → Fields → New Field → SmartLink Manager → SmartLink**.

### Field Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| **Sources** | `string\|null` | `'*'` | Source key filter for selectable smart links (`'*'` = all) |
| **Limit** | `int\|null` | `null` | Maximum number of smart links that can be selected (null = unlimited) |
| **Allow Multiple** | `bool` | `false` | Whether multiple smart links can be selected |
| **Selection Label** | `string` | `'Add a smart link'` | The label text shown on the "Add" button |

### Template Usage

The field returns a `SmartLinkQuery` in Twig. Use `.one()` to get a single element or `.all()` to get a list:

```twig
{# Single smart link (limit: 1 field) #}
{% set smartLink = entry.smartLinkField.one() %}
{% if smartLink %}
    <a href="{{ smartLink.getUrl() }}">{{ smartLink.title }}</a>
{% endif %}

{# Multiple smart links #}
{% set smartLinks = entry.smartLinkField.all() %}
{% for smartLink in smartLinks %}
    <a href="{{ smartLink.getUrl() }}">{{ smartLink.title }}</a>
{% endfor %}
```

### Checking if a Field is Set

Since the field returns a query object, always check the result of `.one()` before using it:

```twig
{% set smartLink = entry.smartLinkField.one() %}
{% if smartLink %}
    {# Link is set and the element exists #}
    <a href="{{ smartLink.getUrl() }}">{{ smartLink.title }}</a>
{% endif %}
```

### Full Example

A common pattern is rendering a download button on a product entry using a SmartLink field:

```twig
{% set downloadLink = entry.downloadSmartLink.one() %}

{% if downloadLink %}
    <div class="download-buttons">
        {% if downloadLink.iosUrl %}
            <a href="{{ downloadLink.getUrl() }}" class="btn btn--ios">
                Download on the App Store
            </a>
        {% endif %}

        {% if downloadLink.androidUrl %}
            <a href="{{ downloadLink.getUrl() }}" class="btn btn--android">
                Get it on Google Play
            </a>
        {% endif %}

        <a href="{{ downloadLink.getUrl() }}" class="btn btn--fallback">
            Download Now
        </a>
    </div>
{% endif %}
```

> [!TIP]
> The `getUrl()` method always returns the smart link's public redirect URL. Visitors who click it are automatically detected and routed to the correct platform. You don't need to write device-detection logic in your template.

## Craft Link Field Integration

If the [Craft Link Field](https://github.com/verbb/link-field) plugin by Verbb is installed, SmartLink Manager also registers a **SmartLink** option in the Link field's type selector.

This is separate from the SmartLinkField — it allows editors to pick a smart link in any Link field alongside URLs, entries, categories, and assets.

```twig
{# Link field returns the public redirect URL of the selected smart link #}
<a href="{{ entry.ctaLink.url }}">{{ entry.ctaLink.text }}</a>
```

See [Integrations](../feature-tour/integrations-overview.md) for setup details.

## Querying by Field

In PHP, you can query smart links that are attached to a specific field or element:

```php
use lindemannrock\smartlinkmanager\elements\SmartLink;

// All smart links attached to a specific field
$links = SmartLink::find()
    ->fieldId($fieldId)
    ->all();

// Smart links owned by a specific entry
$links = SmartLink::find()
    ->ownerId($entry->id)
    ->all();
```

See [Element Queries](element-queries.md) for the full query API.
