<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @since 5.31.0
 */
#[CoversNothing]
class SeomaticTrackingTemplateTest extends TestCase
{
    public function testSeomaticTrackingTemplateRendersDirectly(): void
    {
        $template = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/_integrations/seomatic.twig');

        $this->assertStringNotContainsString('{% macro', $template);
        $this->assertStringNotContainsString('{% endmacro %}', $template);
        $this->assertStringContainsString("'seomatic' in enabledIntegrations", $template);
        $this->assertStringContainsString('eventType in trackingEvents', $template);
        $this->assertStringContainsString("slug: '{{ smartLink.slug|e('js') }}'", $template);
        $this->assertStringContainsString("title: '{{ smartLink.title|e('js') }}'", $template);
        $this->assertStringContainsString('window.dataLayer.push(eventData);', $template);
        $this->assertStringContainsString("pushSmartLinkEvent('redirect', 'auto', source);", $template);
        $this->assertStringContainsString('function platformFromTrackedUrl(url)', $template);
        $this->assertStringContainsString("var queryPlatform = url.searchParams.get('platform');", $template);
        $this->assertStringContainsString("return pathParts[goIndex + 2];", $template);
        $this->assertStringContainsString('var platform = platformFromTrackedUrl(url);', $template);
        $this->assertStringNotContainsString("var platform = url.searchParams.get('platform') || 'unknown';", $template);
        $this->assertStringNotContainsString('refresh-csrf', $template);
        $this->assertStringNotContainsString('Device detection failed', $template);
        $this->assertStringNotContainsString('data.isMobile', $template);
        $this->assertStringNotContainsString("actionUrl('smartlink-manager/redirect/go'", $template);
        $this->assertStringNotContainsString('window.location.replace', $template);
    }

    public function testRedirectTemplatesOwnTrackedAutoNavigation(): void
    {
        $pluginTemplate = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/redirect.twig');
        $autoRedirectTemplate = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/_frontend/auto-redirect.twig');

        $this->assertStringContainsString('smartLink.renderRedirectScript()', $pluginTemplate);
        $this->assertStringContainsString('smartLink.renderRedirectSeomaticTracking()', $pluginTemplate);
        $this->assertStringContainsString('{{ goUrls.ios }}', $pluginTemplate);
        $this->assertStringContainsString('{{ goUrls.fallback }}', $pluginTemplate);
        $this->assertStringNotContainsString('{{ goUrls.auto }}', $pluginTemplate);
        $this->assertStringNotContainsString('{% if autoRedirect %}', $pluginTemplate);
        $this->assertStringNotContainsString('var goUrl = {{ goUrl|json_encode|raw }};', $pluginTemplate);
        $this->assertStringNotContainsString('window.location.replace(goUrl);', $pluginTemplate);
        $this->assertStringNotContainsString('fetch(resolverUrl.toString(), {', $pluginTemplate);
        $this->assertStringNotContainsString("actionUrl('smartlink-manager/redirect/go'", $pluginTemplate);
        $this->assertStringNotContainsString("smartLink.renderSeomaticTracking(eventType ?? 'redirect')", $pluginTemplate);
        $this->assertStringNotContainsString('renderRedirectSeomaticTracking is defined', $pluginTemplate);
        $this->assertStringNotContainsString('DEBUG MODE', $pluginTemplate);
        $this->assertStringNotContainsString('debugMode', $pluginTemplate);

        $this->assertStringContainsString('var resolverUrl = new URL({{ autoRedirectUrl|json_encode|raw }}, window.location.href);', $autoRedirectTemplate);
        $this->assertStringContainsString('fetch(resolverUrl.toString(), {', $autoRedirectTemplate);
        $this->assertStringContainsString('cache: \'no-store\'', $autoRedirectTemplate);
        $this->assertStringContainsString('window.location.replace(data.goUrl);', $autoRedirectTemplate);
        $this->assertStringContainsString('{% if skipDebugRedirect %}', $autoRedirectTemplate);
    }

    public function testQrTemplateUsesIntentBasedSeomaticHelper(): void
    {
        $template = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/qr.twig');

        $this->assertStringContainsString('smartLink.renderQrSeomaticTracking()', $template);
        $this->assertStringNotContainsString("smartLink.renderSeomaticTracking('qr_scan')", $template);
        $this->assertStringNotContainsString('renderQrSeomaticTracking is defined', $template);
        $this->assertStringNotContainsString('DEBUG MODE', $template);
        $this->assertStringNotContainsString('debugMode', $template);
    }
}
