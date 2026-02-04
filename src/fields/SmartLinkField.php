<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use GraphQL\Type\Definition\Type;
use lindemannrock\smartlinkmanager\elements\db\SmartLinkQuery;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Smart Link Field
 *
 * @since 1.0.0
 */
class SmartLinkField extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|null The source keys that the field should be restricted to
     */
    public ?string $sources = '*';

    /**
     * @var int|null The maximum number of relations this field can have
     */
    public ?int $limit = null;

    /**
     * @var string Selection label shown in the element picker
     */
    public string $selectionLabel = 'Add a smart link';

    /**
     * @var bool Whether to allow multiple selections
     */
    public bool $allowMultiple = false;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getDisplayName();
    }

    /**
     * @inheritdoc
     */
    public static function icon(): string
    {
        return '@appicons/link.svg';
    }

    /**
     * @inheritdoc
     */
    public static function elementType(): string
    {
        return SmartLink::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('smartlink-manager', 'Add a smart link');
    }

    /**
     * @inheritdoc
     */
    public static function valueType(): string
    {
        return SmartLinkQuery::class;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('smartlink-manager/_components/fields/SmartLinkField/settings', [
            'field' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        if (!($value instanceof ElementQueryInterface)) {
            $value = $this->normalizeValue($value, $element);
        }

        $variables = $this->inputTemplateVariables($value, $element);

        return Craft::$app->getView()->renderTemplate('smartlink-manager/_components/fields/SmartLinkField/input', $variables);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        if (!($value instanceof ElementQueryInterface)) {
            $value = $this->normalizeValue($value, $element);
        }

        $smartLinks = $value->all();

        if (empty($smartLinks)) {
            return '';
        }

        if ($this->allowMultiple) {
            $html = '<ul class="bullets">';
            foreach ($smartLinks as $smartLink) {
                $html .= '<li>' . Craft::$app->getView()->renderTemplate('_elements/element', [
                    'element' => $smartLink,
                ]) . '</li>';
            }
            $html .= '</ul>';
            return $html;
        }

        return Craft::$app->getView()->renderTemplate('_elements/element', [
            'element' => $smartLinks[0],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if ($value instanceof ElementQueryInterface) {
            return $value;
        }

        $query = SmartLink::find();

        // Set the field context
        $query->fieldId = $this->id;

        if ($element && $element->id) {
            $query->ownerId = $element->id;
        } else {
            $query->id = false;
        }

        if (is_array($value)) {
            $query->id = array_values(array_filter($value));
        } elseif ($value !== '' && $value !== null) {
            $query->id = [$value];
        } else {
            $query->id = false;
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (!($value instanceof ElementQueryInterface)) {
            $value = $this->normalizeValue($value, $element);
        }

        return $value->ids();
    }

    /**
     * @inheritdoc
     */
    protected function inputTemplateVariables(mixed $value = null, ?ElementInterface $element = null): array
    {
        if (!($value instanceof ElementQueryInterface)) {
            $value = $this->normalizeValue($value, $element);
        }

        $variables = [
            'field' => $this,
            'id' => $this->getInputId(),
            'name' => $this->handle,
            'elements' => $value->all(),
            'elementType' => static::elementType(),
            'sources' => $this->sources,
            'limit' => $this->allowMultiple ? $this->limit : 1,
            'selectionLabel' => $this->selectionLabel ?: static::defaultSelectionLabel(),
        ];

        return $variables;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();

        if ($this->limit && $this->allowMultiple) {
            $rules[] = [
                'validateLimit',
            ];
        }

        return $rules;
    }

    /**
     * Validates that the number of selected elements doesn't exceed the limit
     *
     * @param ElementInterface $element
     * @since 1.0.0
     */
    public function validateLimit(ElementInterface $element): void
    {
        $value = $element->getFieldValue($this->handle);

        if (!($value instanceof ElementQueryInterface)) {
            return;
        }

        $count = $value->count();

        if ($count > $this->limit) {
            if (method_exists($element, 'addError')) {
                /** @var \craft\base\Element $element */
                $element->addError(
                    $this->handle,
                    Craft::t('smartlink-manager', 'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.', [
                        'limit' => $this->limit,
                    ])
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getContentGqlType(): Type|array
    {
        return Type::listOf(Type::string());
    }

    /**
     * @inheritdoc
     */
    public function getPreviewHtml(mixed $value, ElementInterface $element): string
    {
        if (!($value instanceof ElementQueryInterface)) {
            $value = $this->normalizeValue($value, $element);
        }

        $smartLinks = $value->limit(3)->all();

        if (empty($smartLinks)) {
            return '<span class="light">' . Craft::t('smartlink-manager', 'No smart links selected') . '</span>';
        }

        $html = '<div class="element small">';
        foreach ($smartLinks as $smartLink) {
            $html .= '<div class="chip"><span class="label">' . $smartLink->name . '</span></div>';
        }

        $total = $value->count();
        if ($total > 3) {
            $html .= '<span class="light">+' . ($total - 3) . ' more</span>';
        }

        $html .= '</div>';

        return $html;
    }
}
