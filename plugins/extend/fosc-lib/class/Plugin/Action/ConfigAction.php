<?php

namespace Fosc\Plugin\Action;

use Fosc\Util\FormHelper;
use Sunlight\Plugin\Action\ConfigAction as BaseConfigAction;
use Sunlight\Util\Form;

class ConfigAction extends BaseConfigAction
{
    /**
     * @param string $name snake_case name
     * @param string $label label for input field - the modifiers automatically use the _lang() function
     *
     * Available shorthand modifiers for $label
     * ========================================
     * `%p:` for prefix automatically followed by field name
     * `%k:` for translation key
     *
     * @param string $input allows field definition or use of shorthand for field auto-generation ('%[type]' or '%checkbox')
     * @param array|null $inputAttributes attributes ex. ['class'=>'smallinput', ...]
     *                                    and the 'select_options' and 'select_default' keys can be used for the 'select' field
     * @param string|null $type `text` and `checkbox` are handled automatically when using shorthand,
     *                          `null` is for custom mapping using 'Configuration::mapSubmittedValue()'
     * @return array
     */
    protected function generateField(
        string $name,
        string $label,
        string $input,
        ?array $inputAttributes = null,
        string $type = null
    ): array
    {
        // lang shorthand
        if ($label[0] === '%') {
            $modifier = mb_substr($label, 1, 2);
            switch ($modifier) {
                case 'k:': // key
                    $label = _lang(mb_substr($label, (mb_strlen($modifier) + 1)));
                    break;
                case 'p:': // prefix - combine with field name
                    $label = _lang(mb_substr($label, (mb_strlen($modifier) + 1)) . '.' . $name);
                    break;
                default:
                    throw new \InvalidArgumentException(
                        "Invalid modifier `%" . $modifier . "`! Available modifiers: `%p:` for prefix automatically followed by field name, `%k:` for translation key."
                    );
            }
        }

        // input shorthand
        if ($input[0] === '%') {
            $short = mb_substr($input, 1);
            switch ($short) {
                case 'color':
                case 'date':
                case 'datetime-local':
                case 'email':
                case 'month':
                case 'number':
                case 'password':
                case 'search':
                case 'tel':
                case 'text':
                case 'time':
                case 'url':
                case 'week':
                    $input = $this->generateInput($short, $name, $inputAttributes ?? []);
                    $type = 'text';
                    break;
                case 'checkbox':
                    $input = $this->generateCheckbox($name, $inputAttributes ?? []);
                    $type = 'checkbox';
                    break;
                case'select':
                    $_options = $inputAttributes['select_options'] ?? [];
                    $_default = $inputAttributes['select_default'] ?? null;
                    unset($inputAttributes['select_options'], $inputAttributes['select_default']);
                    $input = $this->generateSelect($name, $_options, $_default, $inputAttributes);
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid shorthand '" . $short . "'.");
            }
        }

        $result = [
            $name => [
                'label' => $label,
                'input' => $input
            ]
        ];

        if ($type !== null) {
            $result[$name]['type'] = $type;
        }

        return $result;
    }

    /**
     * Generates an array of fields of the same type
     *
     * @param array $names
     * @param string $langPrefix will be associated with the field name '$langPrefix.$name'
     * @param string $input field type valid for all supports shorthand 'FoscConfigAction::generateField()'
     * @param array|null $inputAttributes field attributes valid for all
     * @return array
     */
    protected function generateFields(
        array  $names,
        string $langPrefix,
        string $input,
        ?array $inputAttributes = null
    ): array
    {
        $fields = [];
        foreach ($names as $name) {
            $lang = $langPrefix . '.' . $name;
            $fields += $this->generateField($name, _lang($lang), $input, $inputAttributes ?? []);
        }
        return $fields;
    }

    protected function generateCheckbox(string $name, ?array $attributes = null): string
    {
        $attributes[] = Form::activateCheckbox($this->plugin->getConfig()->offsetGet($name));
        return FormHelper::generateCheckbox(
            'config[' . $name . ']',
            $attributes ?? []
        );
    }

    protected function generateInput(string $type, string $name, ?array $attributes = null): string
    {
        return FormHelper::generateInput(
            $type,
            'config[' . $name . ']',
            $this->plugin->getConfig()->offsetGet($name),
            $attributes ?? []
        );
    }

    /**
     * @param array $options see Form::prepareOptions()
     * @param mixed|null $default if null use plugin config default
     */
    protected function generateSelect(string $name, array $options, $default = null, array $attributes = []): string
    {
        return FormHelper::generateSelect(
            'config[' . $name . ']',
            $options,
            $default ?? $this->plugin->getConfig()->offsetGet($name),
            array_merge(['id' => _e($name)], $attributes)
        );
    }
}