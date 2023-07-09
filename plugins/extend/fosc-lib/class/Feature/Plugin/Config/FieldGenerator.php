<?php

namespace Fosc\Feature\Plugin\Config;

use Fosc\Util\FormHelper;
use Sunlight\Plugin\Plugin;
use Sunlight\Util\Form;

class FieldGenerator
{
    /** @var Plugin */
    private $plugin;

    /** @var array */
    private $fields = [];

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Compatible with classic fields configuration (support multiple fields)
     * @param array $field [option_name => [label:string, input:string, type:string]]
     */
    public function field(array $field): self
    {
        $this->fields += $field;
        return $this;
    }

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
     * @param array $extra extra['before'=>'', 'after'=>''] adding extra content before or after a field
     * @return $this
     */
    public function generateField(
        string $name,
        string $label,
        string $input,
        ?array $inputAttributes = null,
        string $type = null,
        array  $extra = ['before' => '', 'after' => '']
    ): self
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
                    $input = $this->createInput($short, $name, $inputAttributes ?? []);
                    $type = 'text';
                    break;
                case 'checkbox':
                    $input = $this->createCheckbox($name, $inputAttributes ?? []);
                    $type = 'checkbox';
                    break;
                case 'select':
                    $_options = $inputAttributes['select_options'] ?? [];
                    $_default = $inputAttributes['select_default'] ?? null;
                    unset($inputAttributes['select_options'], $inputAttributes['select_default']);
                    $input = $this->createSelect($name, $_options, $_default, $inputAttributes);
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid shorthand '" . $short . "'.");
            }
        }

        $result = [
            $name => [
                'label' => $label,
                'input' => (!empty($extra['before']) ? $extra['before'] : '')
                    . $input
                    . (!empty($extra['after']) ? $extra['after'] : '')
            ]
        ];

        if ($type !== null) {
            $result[$name]['type'] = $type;
        }

        $this->fields += $result;

        return $this;
    }

    /**
     * Generates an array of fields of the same type
     *
     * @param array $names
     * @param string $langPrefix will be associated with the field name '$langPrefix.$name'
     * @param string $input field type valid for all supports shorthand {@see FieldGenerator::generateField()}
     * @param array|null $inputAttributes field attributes valid for all
     * @return $this
     */
    public function generateFields(
        array  $names,
        string $langPrefix,
        string $input,
        ?array $inputAttributes = null
    ): self
    {
        foreach ($names as $name) {
            $this->generateField($name, $langPrefix, $input, $inputAttributes ?? []);
        }
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function createCheckbox(string $name, ?array $attributes = null): string
    {
        $attributes[] = Form::activateCheckbox($this->plugin->getConfig()->offsetGet($name));
        return FormHelper::generateCheckbox(
            'config[' . $name . ']',
            $attributes ?? []
        );
    }

    public function createInput(string $type, string $name, ?array $attributes = null): string
    {
        return FormHelper::generateInput(
            $type,
            'config[' . $name . ']',
            $this->plugin->getConfig()->offsetGet($name),
            $attributes ?? []
        );
    }

    /**
     * @param array $options {@see FormHelper::prepareOptions()}
     * @param mixed|null $selectedOption if null use plugin config default
     */
    public function createSelect(string $name, array $options, $selectedOption = null, array $attributes = []): string
    {
        return FormHelper::generateSelect(
            'config[' . $name . ']',
            $options,
            $selectedOption ?? $this->plugin->getConfig()->offsetGet($name),
            array_merge(['id' => _e($name)], $attributes)
        );
    }
}