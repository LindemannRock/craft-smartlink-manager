# Device Detection

SmartLink Manager identifies the visiting device using Matomo Device Detector and resolves the most appropriate redirect destination based on supported platform mappings.

## How Device Detection Works

When a visitor follows a smart link URL, SmartLink Manager reads the `User-Agent` HTTP header and passes it to the Matomo Device Detector library (included via the `lindemannrock/craft-plugin-base` dependency). The detector classifies the device into a type, operating system, and OS family, then SmartLink Manager maps that to a platform bucket.

The result is a `DeviceInfo` model that drives redirect URL resolution.

## Supported Platforms

| Platform Bucket | Matched When |
|----------------|-------------|
| **iOS** | OS family is iOS (iPhone, iPad, iPod) |
| **Android** | OS family is Android (non-Huawei, non-Amazon) |
| **Huawei** | Android device with Huawei-specific signals (EMUI, HMS) |
| **Amazon** | Android device running Amazon Fire OS |
| **Windows** | OS family is Windows (desktop) |
| **Mac** | OS family is macOS |
| **Fallback** | Any unmatched device or unknown user agent |

### Fallback Chain

Platforms with more specific variants fall back gracefully when no URL is configured:

| If no URL set for... | Falls back to... |
|---------------------|-----------------|
| Huawei | Android URL |
| Amazon | Android URL |
| Android | Fallback URL |
| iOS | Fallback URL |
| Windows | Fallback URL |
| Mac | Fallback URL |

This means you can set just an **Android URL** and **iOS URL** to cover the most common app store cases, and Huawei/Amazon users will still land at the Android store rather than the generic fallback.

## DeviceInfo Model Properties

The `DeviceInfo` model is available as the `device` variable in redirect templates and is returned by the redirect event. It provides:

| Property | Type | Description |
|----------|------|-------------|
| `platform` | `string` | Resolved platform bucket: `ios`, `android`, `huawei`, `windows`, `macos`, `linux`, `other` |
| `deviceType` | `string\|null` | Device category from Matomo: `smartphone`, `tablet`, `desktop`, `tv`, `console`, etc. |
| `isMobile` | `bool` | Whether the device is a mobile phone |
| `isTablet` | `bool` | Whether the device is a tablet |
| `isDesktop` | `bool` | Whether the device is a desktop |
| `isBot` | `bool` | Whether the user agent was identified as a bot/crawler |
| `isMobileApp` | `bool` | Whether the client is a mobile app (not a browser) |
| `osName` | `string\|null` | Operating system name (e.g., `iOS`, `Android`, `Windows`) |
| `osVersion` | `string\|null` | OS version string |
| `browser` | `string\|null` | Browser name (e.g., `Chrome`, `Safari`) |
| `browserVersion` | `string\|null` | Browser version string |
| `browserEngine` | `string\|null` | Browser engine (e.g., `WebKit`, `Blink`) |
| `brand` | `string\|null` | Device brand (e.g., `Apple`, `Samsung`) |
| `vendor` | `string\|null` | Device vendor |
| `model` | `string\|null` | Device model (e.g., `iPhone 14`, `Galaxy S24`) |
| `language` | `string\|null` | Detected 2-letter language code |
| `country` | `string\|null` | Detected 2-letter country code |
| `clientType` | `string\|null` | Client type: `browser`, `mobile app`, `feed reader`, etc. |
| `botName` | `string\|null` | Bot name if `isBot` is true |
| `userAgent` | `string` | Raw user agent string |

Bot traffic is recorded in analytics with `isBot: true` but still follows the normal redirect logic — bots will hit the fallback URL unless a specific platform URL matches.

## Language Detection

SmartLink Manager can detect a visitor's language to route them to a localized URL. Configure the detection method in **Settings → General → Language Detection**:

| Method | How It Works |
|--------|-------------|
| `browser` | Reads the `Accept-Language` HTTP header and parses the preferred language |
| `ip` | Queries the configured geo-detection API for the visitor's country, then maps country to a default language |
| `both` | Tries browser detection first; if that returns no result, falls back to IP-based detection |

```php
// config/smartlink-manager.php
return [
    'languageDetectionMethod' => 'browser',  // 'browser', 'ip', or 'both'
];
```

Language codes follow the IETF BCP 47 format (e.g., `en`, `de`, `fr`, `en-US`, `zh-Hant`). When a localized URL on the smart link matches the detected language, it takes priority over the platform URL.

## Device Detection Caching

Device detection results are cached to avoid parsing the same `User-Agent` repeatedly. The cache uses the same storage method as QR code caching (`'file'` or `'redis'`). The cache key is based on the `User-Agent` string.

```php
// config/smartlink-manager.php
return [
    'cacheStorageMethod' => 'file',  // or 'redis'
];
```

## Redirect URL Resolution

The full resolution order for a redirect URL:

1. **Language-specific URL** — if a localized URL matches the detected language
2. **Platform-specific URL** — if a URL is set for the detected platform
3. **Platform fallback** — Huawei → Android, Amazon → Android
4. **Fallback URL** — the smart link's generic fallback
5. **404** — no match found

## Redirect Template Variables

When building a custom redirect template, these variables are available:

| Variable | Type | Description |
|----------|------|-------------|
| `smartLink` | `SmartLink` | The smart link element being followed |
| `device` | `DeviceInfo` | The detected device information |
| `language` | `string\|null` | The detected language code, or `null` if undetected |

Example custom redirect template:

```twig
{# templates/smartlink-redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url={{ smartLink.getUrl() }}">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting to {{ smartLink.title }}...</p>
    <p><a href="{{ smartLink.getUrl() }}">Click here if not redirected</a></p>
</body>
</html>
```

## Modifying the Redirect URL

Use the `EVENT_BEFORE_REDIRECT` event to intercept and modify the resolved redirect URL before the response is sent:

```php
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use lindemannrock\smartlinkmanager\events\SmartLinkEvent;
use yii\base\Event;

Event::on(
    SmartLinksService::class,
    SmartLinksService::EVENT_BEFORE_REDIRECT,
    function (SmartLinkEvent $event) {
        // Append a UTM parameter to every redirect
        $event->redirectUrl .= '?utm_source=smartlink';
    }
);
```

See [Events](../developers/events.md) for the full event reference.

## Limitations

- Detection accuracy depends on the `User-Agent` string — some browsers and apps send minimal or no user agent data
- Headless browsers, server-side rendering, and prerendering tools may not send a meaningful user agent
- Language detection via IP requires a configured geo-detection provider (see [Analytics](analytics.md#geo-detection))
- Bot filtering identifies known crawlers but cannot detect all bots — some bot traffic will appear as real visits
