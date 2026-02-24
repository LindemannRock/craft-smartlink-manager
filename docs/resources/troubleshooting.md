# Troubleshooting

Common issues encountered when setting up and using SmartLink Manager, with steps to diagnose and fix each one.

## Smart Link Returns 404

**Symptom:** Visiting `/go/{slug}` returns a 404 error.

**Quick checks:**

1. **Is the smart link enabled?** Go to **SmartLink Manager** in the CP and confirm the link's status is **Enabled** (green). Disabled, pending, and expired links return 404.

2. **Is the slug correct?** The URL segment must match the slug exactly. Check for typos, uppercase letters, or trailing slashes. Slugs are case-sensitive.

3. **Does the slugPrefix match?** The default prefix is `go`. If you changed `slugPrefix` in your config file, use the new prefix in your URL. Verify the setting in `config/smartlink-manager.php`:

    ```php
    return [
        'slugPrefix' => 'go',  // check this matches your URL
    ];
    ```

4. **Is the plugin installed and enabled?** Go to **Settings → Plugins** and confirm SmartLink Manager is installed and enabled.

5. **Is the route registered?** SmartLink Manager registers its routes on plugin load. If you recently installed the plugin without running a full Craft init, try clearing the Craft caches: **Utilities → Caches → Clear All Caches**.

---

## QR Code Not Generating

**Symptom:** The QR endpoint (`/qr/{slug}`) returns a blank page, error, or 500.

**Quick checks:**

1. **Is the Imagick PHP extension installed?** QR code generation requires the `imagick` PHP extension. Check via:

    ```bash title="PHP"
    php -m | grep imagick
    ```

    ```bash title="DDEV"
    ddev exec php -m | grep imagick
    ```

    If not listed, install Imagick for your PHP version.

2. **Is QR code enabled on the smart link?** On the smart link edit page, confirm **QR Code Enabled** is checked.

3. **Is QR code enabled globally?** In **Settings → QR Codes**, confirm QR generation is not globally disabled.

4. **Is the logo asset valid?** If you configured a logo overlay, confirm the asset exists and the volume is readable by the web process. Try disabling the logo temporarily to isolate the issue.

5. **Check the logs.** Go to **SmartLink Manager → Logs** (or **Utilities → Logs** if enabled) for Imagick-specific error messages.

---

## Analytics Not Recording Clicks

**Symptom:** Clicks happen but the analytics tab shows 0 clicks, or click counts don't increment.

**Quick checks:**

1. **Is the IP hash salt configured?** This is the most common cause. Check your `.env` file for:

    ```bash
    SMARTLINK_MANAGER_IP_SALT=your-salt-here
    ```

    If missing, generate one:

    ```bash title="PHP"
    php craft smartlink-manager/security/generate-salt
    ```

    ```bash title="DDEV"
    ddev craft smartlink-manager/security/generate-salt
    ```

2. **Is analytics enabled globally?** In **Settings → Analytics**, confirm **Enable Analytics** is on.

3. **Is analytics enabled on the specific smart link?** On the smart link edit page, confirm **Track Analytics** is checked.

4. **Is the queue running?** Analytics writes may be processed via the Craft queue. Check **Utilities → Queue** for stuck or failed jobs.

5. **Check the logs** for database write errors that might indicate a missing `smartlinkmanager_analytics` table or permission issue.

---

## Geo-Detection Not Working

**Symptom:** Country and city are blank in analytics even though clicks are recording.

**Quick checks:**

1. **Is geo-detection enabled?** In **Settings → Analytics → Geo Detection**, confirm **Enable Geo Detection** is on.

2. **Is the API provider configured?** Select a provider (`ip-api.com`, `ipapi.co`, or `ipinfo.io`). If using `ipapi.co` or `ipinfo.io`, an API key is required.

3. **Is the API reachable?** The Craft server must be able to make outbound HTTP requests to the provider's API. Check firewall rules or proxy settings on your server.

4. **Is the IP address a private/localhost address?** Local development requests (127.0.0.1, ::1, 192.168.x.x) cannot be geo-located. Geo-detection will return the default country and city from your `.env`:

    ```bash
    SMARTLINK_MANAGER_DEFAULT_COUNTRY=US
    SMARTLINK_MANAGER_DEFAULT_CITY=New York
    ```

5. **Have you hit the API rate limit?** Free tiers on geo APIs have request limits. Check the provider's dashboard for rate limit errors.

---

## SEOmatic Tracking Not Firing

**Symptom:** GTM/GA4 data layer events (`smart_links_redirect`, etc.) are not appearing.

**Quick checks:**

1. **Is the SEOmatic integration enabled?** In **Settings → Integrations**, confirm the SEOmatic toggle is on.

2. **Is SEOmatic installed and enabled?** Go to **Settings → Plugins** and confirm SEOmatic is installed. The integration option only appears when SEOmatic is present.

3. **Is your GTM/GA4 container configured?** SmartLink Manager pushes to `window.dataLayer`. GTM must be configured to listen for events with the names `smart_links_redirect`, `smart_links_qr_scan`, or `smart_links_button_click`.

4. **Are you testing on the redirect page?** The `smart_links_redirect` event fires on the redirect landing page before the visitor is sent to the destination. If the redirect is a 302 with no interstitial, the event fires but the page unloads immediately. Use GTM Preview mode to catch it.

5. **Is `renderSeomaticTracking()` included for button clicks?** The `smart_links_button_click` event only fires if you call `{{ smartLink.renderSeomaticTracking('button_click')|raw }}` in your template. Check that it's present and that the `|raw` filter is applied.

---

## Cache Not Clearing

**Symptom:** QR codes or device detection results show stale data after changing settings.

**Quick checks:**

1. **For file-based cache** — confirm the Craft web process has write permission to the cache directory. Check `storage/runtime/` permissions:

    ```bash title="DDEV"
    ddev exec ls -la storage/runtime/
    ```

2. **For Redis cache** — confirm the Redis connection is active. Check `config/app.php` or `config/app.web.php` for the Redis component configuration and verify the host/port are reachable.

3. **Clear manually** — go to **Utilities → SmartLink Manager** and use the **Clear Cache** button. Alternatively, use **Utilities → Caches → Clear All Caches** to flush all Craft caches.

4. **Check the `clearCache` permission** — users need the `smartLinkManager:clearCache` permission to clear the cache from the CP. Admins always have access.

---

## "No Smart Links Found" When Querying in Templates

**Symptom:** `craft.smartLinks.all()` returns an empty array even though smart links exist in the CP.

**Quick checks:**

1. **Default status filter** — by default, `craft.smartLinks.all()` only returns **enabled** smart links. If all your links are disabled, pending, or expired, the result will be empty. Use `.status(null)` to return all:

    ```twig
    {% set links = craft.smartLinks.status(null).all() %}
    ```

2. **Site scope** — in a multi-site setup, queries default to the current site. Smart links created under a different site won't appear. Use `.site('*')` to query across all sites:

    ```twig
    {% set links = craft.smartLinks.site('*').all() %}
    ```

3. **Permissions** — anonymous Twig queries are not restricted by user permissions, but if you're debugging from a logged-in user context, confirm no unexpected permission checks are filtering results.
