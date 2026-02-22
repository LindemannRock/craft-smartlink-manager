# Events @since(5.0.0)

Events allow you to extend SmartLink Manager's behavior with custom logic.

## SmartLinkEvent Properties

All events below use the `SmartLinkEvent` class with these properties:

| Property | Type | Description |
|----------|------|-------------|
| `smartLink` | `SmartLink` | The smart link element |
| `device` | `DeviceInfo` | The visitor's device information |
| `redirectUrl` | `string` | The redirect URL (modifiable in `beforeRedirect`) |
| `metadata` | `array` | Additional metadata (language, referrer, source, IP) |

## `EVENT_BEFORE_REDIRECT`

Triggered before a smart link redirects the visitor. Modify `$event->redirectUrl` to change the redirect destination.

```php
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use lindemannrock\smartlinkmanager\events\SmartLinkEvent;
use yii\base\Event;

Event::on(
    SmartLinksService::class,
    SmartLinksService::EVENT_BEFORE_REDIRECT,
    function(SmartLinkEvent $event) {
        // Modify redirect URL based on device
        if ($event->device->platform === 'ios') {
            $event->redirectUrl = 'https://apps.apple.com/your-app';
        }
    }
);
```

## `EVENT_AFTER_TRACK_ANALYTICS`

Triggered after analytics are recorded for a redirect. Use this to send data to external services or trigger custom logic.

```php
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use lindemannrock\smartlinkmanager\events\SmartLinkEvent;
use yii\base\Event;

Event::on(
    SmartLinksService::class,
    SmartLinksService::EVENT_AFTER_TRACK_ANALYTICS,
    function(SmartLinkEvent $event) {
        // Send analytics to external service
        $smartLink = $event->smartLink;
        $device = $event->device;
        $metadata = $event->metadata;

        // $metadata contains: redirectUrl, language, referrer, source, ip
    }
);
```

## `EVENT_AFTER_SAVE_SETTINGS`

Triggered after plugin settings are saved. Defined on the `Settings` model.

```php
use lindemannrock\smartlinkmanager\models\Settings;
use yii\base\Event;

Event::on(
    Settings::class,
    Settings::EVENT_AFTER_SAVE_SETTINGS,
    function(Event $event) {
        // React to settings changes
    }
);
```
