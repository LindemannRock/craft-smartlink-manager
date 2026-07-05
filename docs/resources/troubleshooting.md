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

6. **Are both SmartLink Manager and ShortLink Manager in root mode on the same host?** If both plugins have URL prefix disabled and share a host, root routes like `/{slug}` can collide. One plugin may capture requests meant for the other and trigger its own `notFoundRedirectUrl`.

---

## Twig says it cannot find `smartlink-manager/redirect`

If a public smart link or QR landing page fails with a Twig template loading error such as:

```text
Unable to find the template "smartlink-manager/redirect".
```

the starter templates have not been copied into your site's `templates/` folder, or the template path setting points at a custom location where no file exists.

Open **SmartLink Manager → Setup** and follow the template task, or run:

```bash title="PHP"
php craft smartlink-manager/setup/copy-templates
```

```bash title="DDEV"
ddev craft smartlink-manager/setup/copy-templates
```

The command copies missing starter templates into the paths configured in settings and skips existing files. If you changed `redirectTemplate` or `qrTemplate`, make sure the matching template exists at that configured path.

For template source paths, manual copy commands, and available variables, see [Custom templates](../developers/custom-templates.md).

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

1. **Is the IP hash salt configured?** Open **SmartLink Manager → Setup** if the salt is missing. Public smart links can still resolve when templates are present, but setup remains incomplete and analytics privacy features are limited until the salt is configured. Set the salt with:

    ```bash title="PHP"
    php craft smartlink-manager/security/generate-salt
    ```

    ```bash title="DDEV"
    ddev craft smartlink-manager/security/generate-salt
    ```

2. **Is analytics enabled globally?** In **Settings → Analytics**, confirm **Enable Analytics** is on.

3. **Is analytics enabled on the specific smart link?** On the smart link edit page, confirm **Track Analytics** is checked.

4. **Are requests reaching the tracked hop?** SmartLink click analytics are written synchronously on the internal `smartlink-manager/redirect/go` action route, not via the Craft queue. If counts are missing, inspect plugin logs and confirm your redirect/QR flow is still reaching that action.

5. **Check the logs** for database write errors that might indicate a missing `smartlinkmanager_analytics` table or permission issue.

---

## Scheduled Analytics Cleanup Does Not Reappear

SmartLink Manager schedules a recurring queue job for analytics cleanup. If the queue is empty after the cleanup job runs:

1. Confirm the queue worker is running.
2. Visit any CP page to let SmartLink Manager bootstrap the initial job.
3. Check that **Enable Analytics** is on.
4. Check that analytics retention is greater than `0`.

The queued job description shows when that specific queued row is due to run. Craft stores that description when the row is queued, so date/time format changes apply to newly queued rows. Existing delayed rows keep their old label until they run or are requeued. Queue labels stay compact: numeric months render numerically, while short and long month settings both render as short month names.

If a deployment or multiple web processes create duplicate pending cleanup rows, SmartLink Manager collapses the duplicate pending rows during bootstrap and keeps one row for the next scheduled run.

---

## Date Format Changes Do Not Appear in the Smart Links Index

The SmartLink Manager index uses the plugin date/time settings for its date columns, including **Post Date**, **Expiry Date**, **Date Created**, and **Date Updated**.

If a date-format setting does not appear to change the index:

1. Make sure you are changing **SmartLink Manager → Settings → Interface**, not only Craft's global locale preferences.
2. Check whether `config/smartlink-manager.php` sets `timeFormat`, `monthFormat`, `dateOrder`, `dateSeparator`, or `showSeconds`; config-file values override the CP settings.
3. Clear caches and reload the index.
4. Confirm the relevant column is enabled in the index column settings.

---

## Geo-Detection Not Working

**Symptom:** Country and city are blank in analytics even though clicks are recording.

**Quick checks:**

1. **Is geo-detection enabled?** In **Settings → Analytics → Geo Detection**, confirm **Enable Geo Detection** is on.

2. **Is the API provider configured?** Select a provider (`ip-api.com`, `ipapi.co`, or `ipinfo.io`). If using `ipapi.co` or `ipinfo.io`, an API key is required.

3. **Is the API reachable?** The Craft server must be able to make outbound HTTP requests to the provider's API. Check firewall rules or proxy settings on your server.

4. **Is the IP address a private/localhost address?** Local development requests (127.0.0.1, ::1, 192.168.x.x) cannot be geo-located automatically. SmartLink Manager leaves geo fields empty unless you set both defaults:

    ```bash
    SMARTLINK_MANAGER_DEFAULT_COUNTRY=US
    SMARTLINK_MANAGER_DEFAULT_CITY=New York
    ```

    If either value is missing or does not match a supported location, SmartLink Manager leaves the geo fields empty instead of inventing a fallback location.

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

## SmartLinks do not appear in SEOmatic Content SEO

**Symptom:** SEOmatic is installed, but SmartLinks are missing from **SEOmatic → Content SEO**, or only the title is available as a source field.

**Quick checks:**

1. **Is the SEOmatic integration enabled?** In **Settings → Integrations**, confirm the SEOmatic toggle is on. The content source is only registered when the integration is enabled.

2. **Did you recently enable it?** Clear SEOmatic metadata and sitemap caches, then reload the SEOmatic Content SEO screen.

3. **Are you looking for description or image source fields?** SEOmatic only lists actual Craft custom fields from the SmartLink field layout, plus SEOmatic's built-in title source. Native SmartLink properties such as the built-in description and image are not listed. Add a SEOmatic SEO field for per-link metadata, or add your own text/asset fields and map those fields in SEOmatic Content SEO.

---

## Custom redirect template uses the wrong host

**Symptom:** The smart link landing page loads, but its app-store or fallback buttons point at Craft's default host instead of your configured smart link base URL.

**Quick checks:**

1. **Use the generated URLs from the controller.** Custom redirect templates should use `goUrls.ios`, `goUrls.android`, `goUrls.fallback`, etc. for buttons, and `smartLink.renderRedirectScript()` for the automatic forward. Do not rebuild action URLs manually in Twig.

2. **Check debug mode in development.** With the shipped template (`{{ smartLink.renderRedirectScript() }}`), `?debug=1` works **only when `devMode` is enabled** — it does nothing on staging or production. In a `devMode` environment, add `?debug=1` to a rendered smart link URL to stop the browser before the auto-forward; the script then logs the resolver URL in the browser console, so you can confirm custom domains and multisite site parameters. To debug on staging without `devMode`, switch your custom template to `{{ smartLink.renderRedirectScript(true) }}` (allows `?debug=1` regardless of `devMode`). Otherwise diagnose caching from the **response headers** — `curl -sI 'https://example.com/go/your-link'` and check `x-cache` (a `HIT`, or a non-zero `age`, means a CDN served a cached copy) and `cache-control`.

3. **Check your base URL setting.** If `smartlinkBaseUrl` has no `{siteHandle}`, `{siteId}`, or `{siteUid}` token, SmartLink Manager adds the current link's site as a query parameter on generated tracking URLs.

---

## Cache Not Clearing

**Symptom:** QR codes or device detection results show stale data after changing settings.

**Quick checks:**

1. **For file-based cache** — confirm the Craft web process has write permission to the cache directory. Check `storage/runtime/` permissions:

    ```bash title="DDEV"
    ddev exec ls -la storage/runtime/
    ```

2. **For Redis cache** — confirm the Redis connection is active. Check `config/app.php` or `config/app.web.php` for the Redis component configuration and verify the host/port are reachable. If SmartLink Manager is set to Redis but Craft is not using a Redis-backed cache component, the plugin logs a cache-component warning and skips Redis-specific cache operations until the component is fixed.

3. **Clear manually** — go to **Utilities → SmartLink Manager** and use the **Clear Cache** button. Alternatively, use **Utilities → Caches → Clear All Caches** to flush all Craft caches.

4. **Check the `clearCache` permission** — users need the `smartLinkManager:clearCache` permission to clear the cache from the CP. Admins always have access.

If the Servd Asset Storage plugin is installed and enabled, SmartLink Manager queues targeted Servd static-cache purges for the public smart link URL and QR landing URL when a smart link is saved, deleted, renamed, or when SmartLink Manager caches are cleared. This helps clear stale cached redirect and QR landing responses after content changes. Purging only runs on Servd's own hosting — it also requires the PHP `redis` extension and Servd's runtime environment variables, otherwise it is silently skipped (see [Integrations → Servd static cache](../feature-tour/integrations.md#servd-static-cache)).

---

## Settings Save Shows Numeric Field Errors

Numeric settings such as QR cache duration, device detection cache duration, QR size, logo size, and analytics retention must be whole numbers within the range shown in the field instructions.

If a settings save fails, keep the submitted form open and check the inline field errors. SmartLink Manager validates posted values before saving and does not partially save invalid settings.

---

## Smart Link or Import Row Rejected for Its URL or Markup

**Symptom:** Saving a smart link fails with a validation error on a URL field, or on the **Title** or **Description** ("… contains markup that is not allowed"). A CSV import marks rows as errors for the same fields.

**Cause:** SmartLink Manager validates link data before storing it, and the Control Panel form and the CSV importer enforce the same rules:

- **URLs** (platform URLs and the fallback URL) must be absolute `http://` or `https://` addresses. Other schemes — `mailto:`, `ftp:`, custom app/deep-link schemes, and executable schemes such as `javascript:` or `data:` — are rejected.
- **Title** and **Description** reject embedded HTML or script markup (for example a `<script>` tag or an HTML element with attributes). Plain text is fine — a lone `<` in everyday text like `price < $5` is not flagged.

**Fix:**

1. Use a full `https://` (or `http://`) URL for every platform and fallback link. For a non-web target such as a deep link, point the link at a web URL that performs the redirect rather than storing the custom scheme directly.
2. Remove HTML or script markup from the Title and Description and store plain text.
3. For imports, correct the flagged rows in your CSV and re-upload — the preview step lists each rejected row with its reason.

**Why it happens:** Restricting links to `http(s)` and rejecting markup in free-text fields prevents dangerous values (such as `javascript:` URLs or injected scripts) from being stored and later rendered.

---

## Multisite Custom Domain Resolves to Wrong Site

**Symptom:** In multisite, generated SmartLink URLs all resolve to one site unless the URL contains a site segment.

**Cause:** A non-tokenized `smartlinkBaseUrl` (for example `https://go.example.com`) generates identical URLs for all sites. Incoming requests are then resolved by host/current site context.

**Fix:** Use tokenized `smartlinkBaseUrl`:

```php
'smartlinkBaseUrl' => 'https://go.example.com/{siteHandle}',
```

Supported tokens: `{siteHandle}`, `{siteId}`, `{siteUid}`.

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
