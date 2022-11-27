<?php

namespace Fosc\Util;

/**
 * Numerifier class
 *
 * @author ShiraNai7 <shira.cz>
 */
class Numerifier
{
    /** Float mode - treat as decimal separator */
    public const FLOAT_IF_SINGLE = 0;

    /** Float mode - treat as decimal separator if followed by exactly 3 digits */
    public const FLOAT_IF_NOT_THREE_DIGITS = 1;

    /**
     * Convert numeric string to a number
     *
     * What is $floatMode:
     *
     *      This argument affects decision about a single
     *      separator being the "decimal separator" or not.
     *
     *      Single separator case examples:
     *          - "1.000"
     *          - "1,000"
     *          - "5,43"
     *          - "52,222"
     *
     *      Float modes:
     *
     *          Numerifier::FLOAT_IF_SINGLE
     *              - single separator is ALWAYS considered a decimal separator
     *
     *          Numerifier::FLOAT_IF_NOT_THREE_DIGITS
     *              - single separator is considered a decimal separator IF it is
     *                followed by EXACTLY 3 digits
     *
     *          A character ("," or ".")
     *              - single separator is consideres a decimal separator IF it is
     *                equal to the given character
     *
     * @param string $string
     * @param string|int $floatMode see method description
     * @return int|float|bool false on failure
     */
    public static function numerify(string $string, $floatMode = self::FLOAT_IF_NOT_THREE_DIGITS)
    {
        // analyse
        $separators = self::analyse($string);
        if ($separators === false) {
            return false;
        }

        // decide unwanted chars and the decimal separator
        [$unwantedPattern, $decimalSeparator] = self::decide(
            $string,
            $separators,
            $floatMode
        );

        // format and return
        return self::format(
            $string,
            $unwantedPattern,
            $decimalSeparator
        );
    }

    /**
     * Analyse separators in a string
     *
     * @param string $string
     * @return array|bool false on failure
     */
    private static function analyse(string $string)
    {
        $separators = [];
        $separatorCount = 0;
        $allowedSeparators = [',' => true, '.' => true];
        $signChars = ['-' => true, '+' => true];

        // scan the string
        $insideNumber = false;
        $foundSign = false;
        $separated = false;
        $lastNonSpaceChar = null;

        for ($i = 0; isset($string[$i]); ++$i) {
            $char = $string[$i];

            $isDigit = ctype_digit($char);
            $isSpace = !$isDigit && ctype_space($char);

            // handle char type
            if (!$isDigit && !$isSpace) {
                // not a digit or a space
                if (isset($signChars[$char])) {
                    // sign char
                    if ($foundSign || $insideNumber) {
                        // failure - multiple sign chars or already inside the number
                        return false;
                    } else {
                        $foundSign = true;
                    }
                } elseif (
                    $separatorCount < 2 // when a second separator type is found, no more are allowed
                    && isset($allowedSeparators[$char]) // it must be a valid separator char
                    && !$separated // consecutive separators is not allowed
                    && $insideNumber // separators must appear inside the number
                ) {
                    // separator char
                    if (!isset($separators[$char])) {
                        $separators[$char] = true;
                        ++$separatorCount;
                    }
                    $separated = true;
                } else {
                    // failure
                    return false;
                }
            } elseif ($isDigit) {
                // a digit
                $insideNumber = true;
                $separated = false;
            }
            if (!$isSpace) {
                $lastNonSpaceChar = $char;
            }
        }

        // verify state
        if (
            !$insideNumber // must contain digits
            || $separated // must not end with a separator
            || !ctype_digit($lastNonSpaceChar) // last non-space character must be a digit
        ) {
            // failure
            return false;
        }

        // return separators
        return array_keys($separators);
    }

    /**
     * Decide unwanted chars and the decimal separator
     *
     * @param string $string
     * @param array $separators
     * @param string|int $floatMode
     * @return array unwanted pattern, decimal separator
     */
    private static function decide(string $string, array $separators, $floatMode): array
    {
        $separatorCount = count($separators);

        $unwantedPattern = ['\\s'];
        $decimalSeparator = null;

        if ($separatorCount > 0) {
            // there are some separators
            if ($separatorCount === 1) {
                // single separator, decide using float mode
                $singleSeparatorIsDecimal = false;
                if ($floatMode === self::FLOAT_IF_SINGLE) {
                    // always decimal if single
                    $singleSeparatorIsDecimal = true;
                } elseif (self::FLOAT_IF_NOT_THREE_DIGITS) {
                    // decimal if not followed by three digits
                    $separatorPosition = strpos($string, $separators[0]);
                    if ($separatorPosition === false) {
                        throw new \LogicException('Separator was not found?');
                    }
                    $digitCount = 0;
                    for ($i = $separatorPosition + 1; isset($string[$i]); ++$i) {
                        if (ctype_digit($string[$i]) && ++$digitCount > 3) {
                            break;
                        }
                    }
                    $singleSeparatorIsDecimal = ($digitCount !== 3);
                } elseif (is_string($floatMode)) {
                    // given separator is always decimal if used alone
                    $singleSeparatorIsDecimal = ($floatMode === $separators[0]);
                } else {
                    // invalid mode
                    throw new \InvalidArgumentException('Invalid float mode');
                }

                // handle decision
                if ($singleSeparatorIsDecimal) {
                    $decimalSeparator = $separators[0];
                } else {
                    $unwantedPattern[] = $separators[0];
                }
            } else {
                // multiple separators = no-brainer
                $decimalSeparator = $separators[1];
                $unwantedPattern[] = $separators[0];
            }
        }

        return [$unwantedPattern, $decimalSeparator];
    }

    /**
     * Format a string
     *
     * @param string $string
     * @param array $unwantedPattern array of unwanted character classes
     * @param string|null $decimalSeparator used decimal separator
     * @return int|float
     */
    private static function format(string $string, array $unwantedPattern, ?string $decimalSeparator)
    {
        // remove unwanted chars
        $string = preg_replace('~[' . implode($unwantedPattern) . ']~', '', $string);

        // normalize decimal separator
        if ($decimalSeparator !== null && $decimalSeparator !== '.') {
            $string = str_replace($decimalSeparator, '.', $string);
        }

        // convert to number
        if ($decimalSeparator !== null) {
            $number = (float)$string;
        } else {
            $number = (int)$string;
        }

        return $number;
    }
}