# SmartLink Field

The SmartLink Field is a custom Craft field type that lets editors attach smart links to any element (entries, products, categories, etc.). It works like a standard Craft element picker — select one or more existing smart links from a modal.

## What It Does

When you add a SmartLink Field to an entry's field layout, editors can pick from existing smart links using the familiar Craft element selector. The field stores a relation to the selected smart link(s), so you can render the smart link's redirect URL, QR code, or analytics data directly in your templates.

## Adding the Field

1. Go to **Settings → Fields → New Field**
2. Choose **SmartLink** as the field type
3. Configure field settings
4. Add the field to the desired field layout via **Settings → [Entry Type] → Field Layout**

## Field Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| **Sources** | `string\|null` | `'*'` | Source key filter for selectable smart links (`'*'` = all) |
| **Limit** | `int\|null` | `null` | Maximum number of smart links that can be selected (null = unlimited) |
| **Allow Multiple** | `bool` | `false` | Whether multiple smart links can be selected |
| **Selection Label** | `string` | `'Add a smart link'` | The label text shown on the "Add" button |

## Accessing Field Values in Templates

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

### Rendering a Download Button

A common pattern is rendering a download button on a product entry using a SmartLink field:

```twig
{% set downloadLink = entry.downloadSmartLink.one() %}

{% if downloadLink %}
    <a href="{{ downloadLink.getUrl() }}" class="btn">
        Download Now
    </a>

    {# QR code for the same link #}
    <img src="{{ downloadLink.getQrCodeDataUri({size: 200}) }}" alt="QR Code">
{% endif %}
```

> [!TIP]
> The `getUrl()` method always returns the smart link's public redirect URL. Visitors who click it are automatically detected and routed to the correct platform. You don't need to write device-detection logic in your template.

## Craft Link Field

SmartLink Manager also registers as a link type in Craft's native Link field (Craft CMS 5.3+). See [Integrations](integrations.md) for details.

## Developer Reference

For the full field API, query methods, and GQL type, see [Fields](../developers/fields.md).
