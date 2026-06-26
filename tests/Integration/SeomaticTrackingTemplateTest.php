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
        $this->assertStringContainsString("actionUrl('smartlink-manager/redirect/go'", $template);
    }
}
