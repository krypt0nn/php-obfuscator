<?php

namespace Obfuscator\Modules;

/**
 * Модуль шифрования вызовов функций
 */
class SilentCalls implements \Obfuscator\Module
{
    public function process ($token, array $tokens): string
    {
        if (!is_array ($token))
            return $token;

        else
        {
            switch ($token[0])
            {
                case T_STRING:
                    return function_exists ($token[1]) ?
                        $this->encodeString ($token[1]) : $token[1];

                    break;
            }

            return $token[1];
        }
    }

    /**
     * Шифрование названий функций
     * 
     * @param string - строка
     * 
     * @return string
     */
    protected function encodeString (string $string): string
    {
        $encoders = [
            'base64_encode' => 'base64_decode',
            'str_rot13'     => 'str_rot13',
            'strrev'        => 'strrev'
        ];

        $encoder = rand (0, sizeof ($encoders) - 1);

        return array_values ($encoders)[$encoder] .'(\''. addslashes (array_keys ($encoders)[$encoder] ($string)) .'\')';
    }
}
