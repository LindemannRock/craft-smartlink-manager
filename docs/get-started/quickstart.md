# Quickstart

Get SmartLink Manager running in under 5 minutes. By the end of this guide you'll have your first smart link live and routing visitors to the right app store or URL based on their device.

## 1. Install the Plugin

See [Installation](installation.md) for full details including DDEV and manual options.

## 2. Generate the IP Hash Salt

SmartLink Manager hashes IP addresses for privacy. You need to generate a unique salt and add it to your `.env` file before analytics will work.

```bash title="PHP"
php craft smartlink-manager/security/generate-salt
```

```bash title="DDEV"
ddev craft smartlink-manager/security/generate-salt
```

Copy the output and add it to your `.env` file:

```bash
# .env
SMARTLINK_MANAGER_IP_SALT=your-generated-salt-here
```

> [!IMPORTANT]
> Without the IP salt, analytics tracking will not record clicks. Do this step before creating your first link.

## 3. Create Your First Smart Link

1. Go to **SmartLink Manager** in the Craft control panel
2. Click **New Smart Link**
3. Enter a **Title** and a unique **Slug** (e.g., `my-app`)
4. Add a **Fallback URL** — this is where users land when no platform-specific URL matches

## 4. Add Platform URLs

On the smart link edit page, expand the **Platform URLs** section and add URLs for the platforms you want to target:

| Platform | Example |
|----------|---------|
| **iOS** | `https://apps.apple.com/app/my-app/id123456` |
| **Android** | `https://play.google.com/store/apps/details?id=com.myapp` |
| **Fallback** | `https://myapp.com/download` |

Leave any platform field empty and visitors on that platform will fall through to the **Fallback URL**.

## 5. Test It

Save the smart link and visit:

```
https://yoursite.com/go/my-app
```

SmartLink Manager detects the visitor's device and redirects them to the appropriate URL. Open the link from an iOS device to test the iOS redirect, or visit from a desktop browser to confirm the fallback URL works.

You should see the redirect happen and, after a moment, click data appear on the **Analytics** tab of the smart link.

## What's Next

- [Configuration](configuration.md) — customize URL prefixes, QR defaults, analytics retention, and geo-detection
- [Feature Tour](../feature-tour/overview.md) — explore QR codes, analytics, device detection, and integrations
- [Smart Links](../feature-tour/smart-links.md) — platform URLs, statuses, language detection, and image attachments
