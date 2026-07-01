# GraphQL @since(5.30.0)

Use SmartLink Manager from a headless frontend without building a separate API. GraphQL can resolve a smart link the same way a browser hit would, list enabled smart links for a site, and expose selected smart links directly from entry queries.

There are three GraphQL surfaces:

- `smartlinkManagerResolveSmartLink` for SPA route handling and analytics-aware resolution
- `smartlinkManagerSmartLinks` for read-only lists of enabled smart links
- field output for the SmartLink Manager field and Craft's native Link field integration

## Before you query

GraphQL access is controlled by Craft schemas. SmartLink Manager does not have a separate GraphQL toggle.

Enable these on the schema used by your frontend token:

| Area | Required access |
|---|---|
| Sites | The site the frontend queries, for example `en` |
| SmartLink Manager | `Query SmartLink Manager data` |
| Entries | Only needed when querying entries that contain SmartLink fields or Craft Link fields |

The SmartLink Manager scope is:

| Scope | Purpose |
|---|---|
| `smartlinkManager.all:read` | Allows SmartLink Manager GraphQL queries |

For public frontend requests, use the GraphQL token for a schema that has the site and SmartLink Manager permission enabled. A logged-in Control Panel user may see different results in GraphiQL than an external client using a token.

## Resolve a smart link

Use `smartlinkManagerResolveSmartLink` when a frontend receives a smart link slug and needs the destination URL.

At minimum, pass the slug you want to resolve:

```graphql
{
  smartlinkManagerResolveSmartLink(slug: "summer-app") {
    resolvedDestinationUrl
  }
}
```

You can also pass a site ID:

```graphql
{
  smartlinkManagerResolveSmartLink(slug: "summer-app", siteId: 1) {
    resolvedDestinationUrl
  }
}
```

Or pass a site handle:

```graphql
{
  smartlinkManagerResolveSmartLink(slug: "summer-app", site: "en") {
    resolvedDestinationUrl
  }
}
```

By default, SmartLink Manager auto-detects the device, then chooses the destination URL the same way the public redirect route does. You can pass a platform when the frontend already knows which destination button the visitor selected:

```graphql
query ResolveSmartLink($slug: String!, $site: String, $platform: String) {
  smartlinkManagerResolveSmartLink(
    slug: $slug
    site: $site
    platform: $platform
  ) {
    id
    title
    slug
    url
    redirectUrl
    resolvedDestinationUrl
    resolvedPlatform
    clickType
    status
    site
    siteId
    hits
  }
}
```

Variables:

```json
{
  "slug": "summer-app",
  "site": "en",
  "platform": "ios"
}
```

You can pass either `site` or `siteId`. If both are present, `site` wins. Invalid explicit site handles or IDs return no result instead of falling back to another site.

When no smart link matches, `null` is returned:

```json
{
  "data": {
    "smartlinkManagerResolveSmartLink": null
  }
}
```

When a smart link matches, the requested fields are returned:

```json
{
  "data": {
    "smartlinkManagerResolveSmartLink": {
      "resolvedDestinationUrl": "https://apps.apple.com/app/example"
    }
  }
}
```

This query behaves like a real smart link hit:

- matched, enabled smart links increment `hits` when analytics tracking is enabled for the link and plugin
- matched, enabled smart links record analytics with `source = graphql`
- `platform: "auto"` uses device detection
- auto resolution falls back to `fallbackUrl` when the detected platform has no configured destination URL
- `platform: "ios"`, `android`, `huawei`, `amazon`, `windows`, `mac`, or `fallback` resolves the matching destination URL
- disabled, pending, expired, or site-disabled smart links return `null`

Because this query intentionally has hit-count and analytics side effects, SmartLink Manager disables Craft's GraphQL result cache for operations that include `smartlinkManagerResolveSmartLink`.

### Arguments

```graphql
smartlinkManagerResolveSmartLink(
  slug: "summer-app"
  siteId: 1
  site: "en"
  platform: "auto"
)
```

| Argument | Type | Required | Description |
|---|---|---|---|
| `slug` | `String` | Yes | Smart link slug to resolve |
| `siteId` | `Int` | No | Site ID to resolve against |
| `site` | `String` | No | Site handle to resolve against |
| `platform` | `String` | No | Platform to resolve for. Defaults to `auto` |

## List smart links

Use `smartlinkManagerSmartLinks` when a frontend needs a read-only list of enabled smart links for a site.

```graphql
query SmartLinks($siteId: Int, $limit: Int) {
  smartlinkManagerSmartLinks(siteId: $siteId, limit: $limit) {
    id
    title
    slug
    url
    redirectUrl
    qrCodeUrl
    fallbackUrl
    iosUrl
    androidUrl
    status
    site
    siteId
  }
}
```

The list query:

- returns enabled smart links for the requested site
- accepts `site` or `siteId`
- defaults to 100 results when `limit` is omitted
- caps `limit` at 500
- does not increment `hits`
- does not write analytics

### Arguments

```graphql
smartlinkManagerSmartLinks(siteId: 1, site: "en", limit: 20)
```

| Argument | Type | Required | Description |
|---|---|---|---|
| `siteId` | `Int` | No | Site ID to list smart links for |
| `site` | `String` | No | Site handle to list smart links for |
| `limit` | `Int` | No | Maximum number of smart links to return. Defaults to 100 and is capped at 500 |

## Query a SmartLink field

The SmartLink Manager field resolves to the same smart link object type used by the plugin queries. This is read-only field output and does not increment hits or write analytics.

Single-select fields return one object:

```graphql
query EntrySmartLink {
  entries(section: "mySection", site: "en", limit: 1) {
    title
    ... on mySection_Entry {
      mySmartLinkField {
        id
        title
        slug
        url
        redirectUrl
        qrCodeUrl
        fallbackUrl
        iosUrl
        androidUrl
        hits
      }
    }
  }
}
```

Multi-select fields return a list of the same object:

```graphql
query EntrySmartLinks {
  entries(section: "mySection", site: "en", limit: 1) {
    title
    ... on mySection_Entry {
      mySmartLinksField {
        id
        title
        slug
        url
      }
    }
  }
}
```

Replace `mySection`, `mySection_Entry`, `mySmartLinkField`, and `mySmartLinksField` with your section handle, concrete entry GraphQL type, and SmartLink Manager field handle.

## Query a Craft Link field

SmartLink Manager also integrates with Craft's native Link field. When a Craft Link field allows SmartLink Manager links and GraphQL mode is set to full data, Craft exposes its standard `LinkData` object. The selected SmartLink element is available through the nested `smartLink` field.

```graphql
query EntryNativeLinkField {
  entries(section: "mySection", site: "en", limit: 1) {
    title
    ... on mySection_Entry {
      myLinkField {
        type
        value
        label
        defaultLabel
        url
        link
        target
        title
        class
        ariaLabel
        elementId
        elementSiteId
        elementTitle
        smartLink {
          id
          title
          slug
          url
          redirectUrl
          fallbackUrl
          hits
        }
      }
    }
  }
}
```

Replace `mySection`, `mySection_Entry`, and `myLinkField` with your section handle, concrete entry GraphQL type, and Craft Link field handle.

The top-level fields are Craft's standard `LinkData` output:

- `label`, `target`, `title`, `class`, `ariaLabel`, and URL suffix values come from the Craft Link field value saved on the entry
- `url` includes the Link field URL suffix
- `link` is Craft's rendered anchor tag
- `smartLink` returns SmartLink Manager's smart link object for frontend code that needs smart-link-specific data

If full GraphQL data is not enabled for the Craft Link field, Craft exposes the field as its default string value instead of the object shape above.

## Field reference

The SmartLink Manager object exposes these fields in resolver queries, list queries, SmartLink field output, and nested Craft Link field output.

| Field | Type | Description |
|---|---|---|
| `id` | `Int` | Smart link ID |
| `site` | `String` | Site handle |
| `siteId` | `Int` | Site ID |
| `title` | `String` | Smart link title |
| `slug` | `String` | Public smart link slug |
| `description` | `String` | Smart link description |
| `url` | `String` | Public smart link URL when enabled |
| `redirectUrl` | `String` | Public redirect URL |
| `qrCodeUrl` | `String` | Public QR code URL |
| `resolvedDestinationUrl` | `String` | Destination selected for the current response |
| `resolvedPlatform` | `String` | Platform selected for the current response |
| `clickType` | `String` | `redirect` for auto resolution, `button` for explicit platform resolution |
| `fallbackUrl` | `String` | Fallback destination URL |
| `iosUrl` | `String` | iOS destination URL |
| `androidUrl` | `String` | Android destination URL |
| `huaweiUrl` | `String` | Huawei destination URL |
| `amazonUrl` | `String` | Amazon destination URL |
| `windowsUrl` | `String` | Windows destination URL |
| `macUrl` | `String` | macOS destination URL |
| `status` | `String` | Smart link status |
| `enabled` | `Boolean` | Whether the smart link is enabled |
| `trackAnalytics` | `Boolean` | Whether per-link analytics tracking is enabled |
| `hideTitle` | `Boolean` | Whether the public redirect page hides the title |
| `hits` | `Int` | Number of tracked hits |
| `dateExpired` | `String` | Expiry datetime |

## Troubleshooting

### `Cannot query field "entries" on type "Query"`

The schema does not allow entry queries. Enable the relevant entry section on the GraphQL schema, then retry a small query such as:

```graphql
query {
  entries(section: "mySection", site: "en", limit: 1) {
    title
  }
}
```

### `Schema doesn't have access to the site`

Enable the requested site on the GraphQL schema. This applies to both token-based requests and the public/default schema.

### `Cannot query field "myField" on type "EntryInterface"`

Custom fields usually live on the concrete entry type, not the generic `EntryInterface`. First query `__typename`, then use an inline fragment.

```graphql
query {
  entries(section: "mySection", site: "en", limit: 1) {
    __typename
    title
  }
}
```

Then replace `mySection_Entry` in the examples with the returned `__typename`.

### A Craft Link field returns a string

Set the Craft Link field's GraphQL mode to full data. In URL-only mode, Craft returns the rendered URL/string shape for backwards compatibility.
