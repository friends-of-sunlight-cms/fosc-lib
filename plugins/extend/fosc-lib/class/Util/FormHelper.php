<?php

namespace Fosc\Util;

class FormHelper
{

    public static function generateCheckbox(string $name, array $attributes = []): string
    {
        return self::generateInput('checkbox', $name, 1, $attributes);
    }

    public static function generateInput(string $type, string $name, $value, array $attributes = []): string
    {
        $attr = self::prepareAttributes($attributes);
        return '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" ' . implode(' ', $attr) . '>';
    }

    /**
     * Generate HTML select
     *
     * @param string $name name in 'snake_case' format
     * @param array $options {@see FormHelper::prepareOptions()}
     * @param mixed|null $default
     * @param array $attributes ['class'=>'selectmedium', ...]
     * @return string
     */
    public static function generateSelect(string $name, array $options, $default = null, array $attributes = []): string
    {
        $attr = self::prepareAttributes($attributes);
        $result = '<select name="' . $name . '" id="' . $name . '" ' . implode(' ', $attr) . ">\n";
        $result .= self::prepareOptions($options, $default);
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
     * @param array $items
     * @param mixed|null $default
     * @return string
     */
    public static function prepareOptions(array $items, $default = null): string
    {
        $output = '';
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item = self::prepareOptions($item, $default);
                $output .= '<optgroup label="' . $key . "\">\n" . $item . "</optgroup>\n";
            } else {
                $output .= '<option value="' . $key . '"' . ($key == $default ? 'class="selected-option" selected' : '') . '>' . $item . "</option>\n";
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

}