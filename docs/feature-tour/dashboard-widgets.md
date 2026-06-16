# Dashboard Widgets

SmartLink Manager provides two Craft dashboard widgets for at-a-glance analytics without leaving the dashboard.

## What you'll use it for

- **Daily pulse** — keep total interactions, unique visitors, and engagement on your Craft dashboard.
- **Spot top performers** — see which links are getting the most clicks over a chosen date range.
- **Per-user views** — each editor adds the widgets they want; data respects their permissions and selected site scope.

## Adding Widgets

Add widgets via **Dashboard → New Widget** and selecting either widget from the SmartLink Manager section. Both widgets require the `smartLinkManager:viewAnalytics` permission to display data.

![SmartLink Manager dashboard widgets](images/dashboard-widgets-overview.webp)

## Analytics Summary Widget

The Analytics Summary widget shows an overview of click activity across your smart links over a configurable time period.

### What It Shows

Four summary tiles for the selected date range:

- **Total Interactions** — all recorded click/redirect events
- **Unique Visitors** — distinct visitors in the period
- **Active Links** — number of enabled smart links
- **Engagement Rate** — percentage of active links that received at least one interaction

Below the tiles, a **Top Performer** card highlights the single most-clicked link (its title links to the edit page, its slug links to the live redirect) and a **View full analytics** link to the analytics section.

### Configuration

Click the widget's settings icon to configure:

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| **Date Range** | `string` | `'last7days'` | Time period to summarize: `'today'`, `'yesterday'`, `'last7days'`, `'last30days'`, `'last90days'`, `'all'` |
| **Site** | `string` | `All Sites` | Site scope for the summary. `All Sites` includes the plugin-enabled sites available to the current user. |

### Multi-Site

In a multi-site setup, choose **All Sites** for a cross-site summary or select one site for a focused dashboard view. The site options follow SmartLink Manager's enabled-site configuration and the current user's site access.

## Top Links Widget

The Top Links widget shows which smart links received the most clicks during the selected period.

### What It Shows

A ranked table of the most-clicked links in the selected period. Each row shows:

- **Link** — the smart link's title (linked to its edit page) with its slug below (linked to the live redirect)
- **Interactions** — total clicks in the period, shown as a badge

A **View all {plural}** link sits below the table. When there are no links yet, the widget shows an empty-state prompt instead.

### Configuration

Click the widget's settings icon to configure:

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| **Date Range** | `string` | `'last7days'` | Time period: `'today'`, `'yesterday'`, `'last7days'`, `'last30days'`, `'last90days'`, `'all'` |
| **Site** | `string` | `All Sites` | Site scope for the ranked list. `All Sites` includes the plugin-enabled sites available to the current user. |
| **Number of Links** | `int` | `5` | How many top links to display (1–20) |

### Multi-Site

Like the Analytics Summary widget, Top Links can show **All Sites** or one selected site.

## Permissions

Both widgets require the `smartLinkManager:viewAnalytics` permission. Users without this permission will see a "You don't have permission to view analytics" message in place of widget content.

Admins always have access regardless of permission settings.

See [Permissions](../developers/permissions.md) for the full permission reference.
