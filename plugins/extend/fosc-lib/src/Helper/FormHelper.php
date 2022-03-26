<?php

namespace Fosc\Helper;

class FormHelper
{

    /**
     * Generate HTML checkbox input
     *
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public static function generateCheckbox(string $name, array $attributes = []): string
    {
        return self::generateInput('checkbox', $name, 1, $attributes);
    }

    /**
     * Generate HTML input
     *
     * @param string $type
     * @param string $name
     * @param string|int $value
     * @param array $attributes
     * @return string
     */
    public static function generateInput(string $type, string $name, $value, array $attributes = []): string
    {
        $attr = [];
        foreach ($attributes as $k => $v) {
            $attr[] = (is_int($k) ? $v : $k) . '="' . $v . '"';
        }

        return '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" ' . implode(' ', $attr) . '>';
    }

    /**
     * Generate HTML select
     *
     * @param string $name name in 'snake_case' format
     * @param array $options see FormHelper::prepareOptions()
     * @param mixed|null $default
     * @return string
     */
    public static function generateSelect(string $name, array $options, $default = null): string
    {
        $result = "<select name='" . $name . "' id='" . $name . "'>\n";
        $result .= self::prepareOptions($options, $default);
        $result .= "</select>";
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
        $out = '';
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item = self::prepareOptions($item, $default);
                $out .= "<optgroup label='" . $key . "'>\n" . $item . "</optgroup>\n";
            } else {
                $out .= "<option value='" . $key . "'" . ($key == $default ? " selected" : "") . ">" . $item . "</option>\n";
            }
        }
        return $out;
    }

}