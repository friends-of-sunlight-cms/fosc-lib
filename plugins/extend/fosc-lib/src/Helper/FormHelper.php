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
    static public function generateCheckbox(string $name, array $attributes = []): string
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
    static public function generateInput(string $type, string $name, $value, array $attributes = []): string
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
     * @param array $options ['caption 1' => 'value 1', 'caption 2' => 'value 2', ...]
     * @param mixed $default
     * @return string
     */
    static public function generateSelect(string $name, array $options, $default): string
    {
        $result = "<select name='" . $name . "'>";
        foreach ($options as $key => $value) {
            $result .= "<option value='" . $value . "'" . ($default == $value ? " selected" : "") . ">" . $key . "</option>";
        }
        $result .= "</select>";
        return $result;
    }

}