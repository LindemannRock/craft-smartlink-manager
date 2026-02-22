# Integrations API @since(5.0.0)

SmartLink Manager provides a plugin integration system for third-party services. For a user-facing overview of built-in integrations, see [Integrations](../feature-tour/integrations-overview.md).

## Built-in Integrations

| Integration | Handle | Plugin | Description |
|-------------|--------|--------|-------------|
| `SeomaticIntegration` | `seomatic` | `nystudio107/craft-seomatic` | Pushes smart link events to the GTM/GA4 data layer |
| `RedirectManagerIntegration` | `redirect-manager` | `lindemannrock/craft-redirect-manager` | Creates 301 redirects when smart link slugs change |

## IntegrationInterface

All integrations implement `IntegrationInterface`:

| Method | Returns | Description |
|--------|---------|-------------|
| `isAvailable()` | `bool` | Whether the target plugin/service is installed |
| `isEnabled()` | `bool` | Whether the integration is enabled in settings |
| `getName()` | `string` | Display name |
| `getHandle()` | `string` | Unique identifier (must match `enabledIntegrations` config value) |
| `pushEvent(eventType, data)` | `bool` | Push an event to the third-party service |
| `getStatus()` | `array` | Configuration and status details |
| `validateEventData(eventType, data)` | `bool` | Validate event data before pushing |

## Event Types

Three event types are supported:

| Type | Required Fields | Triggered When |
|------|----------------|----------------|
| `redirect` | `slug`, `title`, `destinationUrl`, `platform`, `source` | Smart link redirect fires |
| `button_click` | `slug`, `title`, `destinationUrl`, `platform`, `buttonType` | Template button with tracking is clicked |
| `qr_scan` | `slug`, `title` | QR code endpoint is accessed |

## Internal Architecture

Built-in integrations extend `BaseIntegration` and are registered internally by the `IntegrationService`. There is currently no public registration event for third-party integrations.

> [!NOTE]
> Custom integration registration may be added in a future version. For now, use the `EVENT_BEFORE_REDIRECT` and `EVENT_AFTER_TRACK_ANALYTICS` events on `SmartLinksService` to hook into the redirect and analytics pipeline (see [Events](events.md)).

## BaseIntegration Helpers

`BaseIntegration` provides these protected methods:

| Method | Description |
|--------|-------------|
| `shouldTrackEvent(eventType)` | Checks if the event type is in `seomaticTrackingEvents` config |
| `formatEventData(eventType, data)` | Formats event data with event prefix, field mapping, device info, and geo data |
| `isPluginInstalled(handle)` | Checks if a plugin is enabled via `PluginHelper::isPluginEnabled()` |
| `validateEventData(eventType, data)` | Validates required fields are present for the event type |
