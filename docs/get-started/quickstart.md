# Quickstart

Create a working smart link after installation setup is complete. By the end of this guide you'll have your first smart link live and routing visitors to the right app store or URL based on their device.

## Before you start

Complete [Installation & Setup](installation.md#post-install-setup) first. The setup page should show that the IP hash salt is configured and all starter templates are present before you test public smart links or QR landing pages.

## 1. Create your first smart link

1. Go to **SmartLink Manager** in the Craft control panel
2. Click **New SmartLink**
3. Enter a **Title** and a unique **Slug** (e.g., `my-app`)
4. Add a **Fallback URL** — this is where users land when no platform-specific URL matches

## 2. Add platform URLs

On the smart link edit page, expand the **Platform URLs** section and add URLs for the platforms you want to target:

| Platform | Example |
|----------|---------|
| **iOS** | `https://apps.apple.com/app/my-app/id123456` |
| **Android** | `https://play.google.com/store/apps/details?id=com.myapp` |
| **Fallback** | `https://myapp.com/download` |

Leave any platform field empty and visitors on that platform will fall through to the **Fallback URL**.

## 3. Test it

Save the smart link and visit:

```
https://yoursite.com/go/my-app
```

SmartLink Manager detects the visitor's device and redirects them to the appropriate URL. Open the link from an iOS device to test the iOS redirect, or visit from a desktop browser to confirm the fallback URL works.

You should see the redirect happen and, after a moment, click data appear on the **Analytics** tab of the smart link.

## What's next

- [Configuration](configuration.md) — customize URL prefixes, QR defaults, analytics retention, and geo-detection
- [Custom templates](../developers/custom-templates.md) — customize the redirect and QR landing pages
- [Feature Tour](../feature-tour/overview.md) — explore QR codes, analytics, device detection, and integrations
- [Smart Links](../feature-tour/smart-links.md) — platform URLs, statuses, scheduling, and image attachments
