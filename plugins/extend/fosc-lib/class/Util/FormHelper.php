<?php

namespace Fosc\Util;

use Sunlight\Extend;
use Sunlight\Util\Form;

class FormHelper
{

    public static function generateCheckbox(string $name, array $attributes = []): string
    {
        return self::generateInput('checkbox', $name, 1, $attributes);
    }

    public static function generateInput(string $type, string $name, $value, array $attributes = []): string
    {
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
            unset($attributes['value']);
        }
        $attr = self::prepareAttributes($attributes);
        return '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" ' . implode(' ', $attr) . '>';
    }

    /**
     * Generate HTML select
     *
     * @param string $name name in 'snake_case' format
     * @param array $options {@see FormHelper::prepareOptions()}
     * @param mixed|null $selectedOption
     * @param array $attributes ['class'=>'selectmedium', ...]
     * @return string
     */
    public static function generateSelect(string $name, array $options, $selectedOption = null, array $attributes = []): string
    {
        if (!isset($attributes['id'])) {
            $attributes = array_merge(['id' => $name], $attributes);
        }
        $attr = self::prepareAttributes($attributes);
        $result = '<select name="' . _e($name) . '" ' . implode(' ', $attr) . ">\n";
        $result .= self::prepareOptions($name, $options, $selectedOption);
        $result .= '</select>';
        return $result;
    }

    /**
     * Generate options for select
     *
     * Items format
     * ============
     * it is possible to use a nested array to create an optgroup
     *
     * [
     * 'Optgroup1' => [
     *      'Value1' => 'Caption 1',
     *      'Value2' => 'Caption 2',
     * ],
     * 'Optgroup2' => [
     *      'Value3' => 'Caption 3',
     *      'Value4' => 'Caption 4',
     * ],
     *      'Value5' => 'Caption 5',
     *      'Value6' => 'Caption 6',
     * ]
     *
     * Data attributes format
     * ======================
     * ['option_value1' => ['attr-name1' => 'value1', ...], 'option_value2' => ... ]
     *
     * @param mixed|null $selectedOption
     */
    public static function prepareOptions(string $selectName, array $items, $selectedOption = null, array $dataAttributes = []): string
    {
        Extend::call('fosclib.formhelper.prepare_options', [
            'select_name' => $selectName,
            'items' => &$items,
            'selected' => &$selectedOption,
            'data_attributes' => &$dataAttributes,
        ]);

        $output = '';
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item = self::prepareOptions($selectName, $item, $selectedOption);
                $output .= '<optgroup label="' . _e($key) . "\">\n" . _e($item) . "</optgroup>\n";
            } else {
                $isSelected = ($key == $selectedOption);
                $output .= '<option value="' . _e($key) . '"'
                    . (!empty($dataAttributes) && isset($dataAttributes[$key])
                        ? implode(' ', self::prepareDataAttributes($dataAttributes[$key]))
                        : ''
                    )
                    . ($isSelected ? ' class="selected-option"' : '')
                    . Form::selectOption($isSelected)
                    . '>' . _e($item) . "</option>\n";
            }
        }
        return $output;
    }

    public static function prepareAttributes(array $attributes): array
    {
        $attr = [];
        foreach ($attributes as $k => $v) {
            $attr[] = (is_int($k) ? $v : $k) . '="' . $v . '"';
        }
        return $attr;
    }

    public static function prepareDataAttributes(array $dataAttributes): array
    {
        $attr = [];
        foreach ($dataAttributes as $key => $value) {
            $attr[] = 'data-' . _e($key) . '="' . _e($value) . '"';
        }
        return $attr;
    }
}