# Template Variables

SmartLink Manager provides the `craft.smartLinks` Twig variable for querying smart links and analytics in your templates.

## `craft.smartLinks`

### `find(criteria)`

Returns a new `SmartLinkQuery` instance, optionally configured with the given criteria.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `criteria` | `array` | `[]` | Query parameters (same as element query params) |

**Returns:** `SmartLinkQuery`

```twig
{% set query = craft.smartLinks.find({ limit: 10 }) %}
{% set links = query.all() %}
```

---

### `all(criteria)`

Returns all smart links matching the given criteria.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `criteria` | `array` | `[]` | Query parameters |

**Returns:** `SmartLink[]`

```twig
{% set allLinks = craft.smartLinks.all() %}
{% set recent = craft.smartLinks.all({ limit: 5, orderBy: 'dateCreated DESC' }) %}
```

---

### `one(criteria)`

Returns the first smart link matching the given criteria, or `null`.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `criteria` | `array` | `[]` | Query parameters |

**Returns:** `SmartLink|null`

```twig
{% set link = craft.smartLinks.one({ slug: 'my-campaign' }) %}
```

---

### `getById(id)`

Returns a smart link by its element ID, or `null`.

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | `int` | Smart link element ID |

**Returns:** `SmartLink|null`

```twig
{% set link = craft.smartLinks.getById(123) %}
```

---

### `getBySlug(slug)`

Returns a smart link by its slug, or `null`. Executes the query immediately.

| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | `string` | Smart link slug |

**Returns:** `SmartLink|null`

```twig
{% set link = craft.smartLinks.getBySlug('summer-sale') %}
{% if link %}
    <a href="{{ link.getUrl() }}">{{ link.title }}</a>
{% endif %}
```

---

### `slug(slug)`

Returns a `SmartLinkQuery` filtered by slug. Unlike `getBySlug()`, this returns the query for further chaining.

| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | `string` | Smart link slug |

**Returns:** `SmartLinkQuery`

```twig
{% set link = craft.smartLinks.slug('summer-sale').one() %}
```

---

### `active()`

Returns a query for only active (enabled) smart links.

**Returns:** `SmartLinkQuery`

```twig
{% set activeLinks = craft.smartLinks.active().all() %}
```

---

### `create(config)`

Creates a new unsaved `SmartLink` element. Does not save to the database — useful for previews or form defaults.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `config` | `array` | `[]` | Element attributes to set |

**Returns:** `SmartLink`

---

### `getAnalytics(smartLink, criteria)`

Returns analytics data for a specific smart link.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `smartLink` | `SmartLink` | — | The smart link element |
| `criteria` | `array` | `[]` | Filter criteria for analytics query |

**Returns:** `array`

```twig
{% set link = craft.smartLinks.getBySlug('summer-sale') %}
{% set stats = craft.smartLinks.getAnalytics(link) %}
```

---

### `getModule()`

Returns the plugin module instance.

**Returns:** `SmartLinkManager`

---

### `getSettings()`

Returns the plugin settings model.

**Returns:** `Settings`

```twig
{% set settings = craft.smartLinks.getSettings() %}
{{ settings.slugPrefix }} {# 'go' #}
```
