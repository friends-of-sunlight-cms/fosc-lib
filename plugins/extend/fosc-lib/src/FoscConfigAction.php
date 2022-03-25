<?php

namespace Fosc;

use Fosc\Helper\FormHelper;
use Sunlight\Plugin\Action\ConfigAction;
use Sunlight\Util\Form;

class FoscConfigAction extends ConfigAction
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
     * @param string $input allows field definition or use of shorthand for field autogeneration ('%[type]' or '%checkbox')
     * @param array $inputAttributes attributes ex. ['class'=>'smallinput', ...]
     * @param string|null $type `text` and `checkbox` are handled automatically when using shorthand,
     *                          `null` is for custom mapping using 'ConfigAction::mapSubmittedValue()'
     * @return array
     */
    protected function generateField(string $name, string $label, string $input, array $inputAttributes = [], string $type = null): array
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
                    throw new \InvalidArgumentException("Invalid modifier `%" . $modifier . "`! Available modifiers: `%p:` for prefix automatically followed by field name, `%k:` for translation key.");
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
                    $input = $this->generateInput($short, $name, $inputAttributes);
                    $type = 'text';
                    break;
                case 'checkbox':
                    $input = $this->generateCheckbox($name, $inputAttributes);
                    $type = 'checkbox';
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
     * @param array $inputAttributes field attributes valid for all
     * @return array
     */
    protected function generateFields(array $names, string $langPrefix, string $input, array $inputAttributes = []): array
    {
        $fields = [];
        foreach ($names as $name) {
            $lang = $langPrefix . '.' . $name;
            $fields += $this->generateField($name, _lang($lang), $input, $inputAttributes);
        }
        return $fields;
    }

    /**
     * @param string $name
     * @param array $attributes
     * @return string
     */
    protected function generateCheckbox(string $name, array $attributes = []): string
    {
        $attributes[] = Form::activateCheckbox($this->plugin->getConfig()->offsetGet($name));
        return FormHelper::generateCheckbox(
            'config[' . $name . ']',
            $attributes
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @param array $attributes
     * @return string
     */
    protected function generateInput(string $type, string $name, array $attributes = []): string
    {
        return FormHelper::generateInput(
            $type,
            'config[' . $name . ']',
            $this->plugin->getConfig()->offsetGet($name),
            $attributes
        );
    }

    protected function generateSelect(string $name, array $options, $default): string
    {
        return FormHelper::generateSelect(
            'config[' . $name . ']',
            $options,
            $default
        );
    }
}