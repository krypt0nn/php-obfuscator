<?php

namespace Obfuscator\Modules;

/**
 * Модуль шифрования строк
 */
class StringEncoder implements \Obfuscator\Module
{
    // Сжимать ли строки с помощью gzdeflate
    public bool $compress = true;

    // Стек токенов
    protected array $tokensStack = [];

    /**
     * Конструктор модуля
     * 
     * [@param array $params = []] - ассоциативный массив параметров модуля
     */
    public function __construct (array $params = [])
    {
        foreach ($params as $property => $value)
            if (isset ($this->$property))
                $this->$property = $value;
    }

    public function process ($token, array $tokens): string
    {
        if (!is_array ($token))
            return $token;

        else
        {
            if ($token[0] == T_WHITESPACE)
                return $token[1];
            
            switch ($token[0])
            {
                case T_CONSTANT_ENCAPSED_STRING:
                    $part = array_slice ($this->tokensStack, -2);
                    
                    return $part[0] == T_CONST && $part[1] == T_STRING ?
                        $token[1] : $this->encodeString (substr ($token[1], 1, -1));

                    break;
            }

            $this->tokensStack[] = $token[0];

            return $token[1];
        }
    }

    /**
     * Шифрование строки
     * 
     * @param string $string - строка для шифрования
     * 
     * @return string
     */
    protected function encodeString (string $string): string
    {
        if ($string == '')
            return '\'\'';
        
        if ($this->compress)
        {
            $compressed   = base64_encode (gzdeflate ($string, 9));
            $decompresser = 'gzinflate(base64_decode(\''. $compressed .'\'))';

            if (strlen ($decompresser) <= strlen ($string))
                return $decompresser;
        }

        # Функции шифрования
        $encoders = [
            'base64_encode' => 'base64_decode',
            'str_rot13'     => 'str_rot13',
            'strrev'        => 'strrev'
        ];

        # Кастомные функции шифрования
        $advancedEncoders = [
            function (string $string): string
            {
                $return = [];
                $length = strlen ($string);
                
                for ($i = 0; $i < $length; ++$i)
                    $return[] = 'chr('. ord ($string[$i]) .')';

                return '('. join ('.', $return) .')';
            },

            function (string $string) use ($encoders): string
            {
                return '~base64_decode(\''. base64_encode (~$string) .'\')';
            },

            function (string $string): string
            {
                $key = base64_encode (md5 (microtime (true), true));
                $key = substr (str_repeat ($key, ceil (($s = strlen ($string)) / strlen ($key))), 0, $s);

                return '(base64_decode(\''. base64_encode ($string ^ $key) .'\')^('. $this->encodeString ($key) .'))';
            }
        ];

        $encSize = sizeof ($encoders);
        $advSize = sizeof ($advancedEncoders);

        $encoder = rand (0, $encSize + $advSize - 1);

        return $encoder < $encSize ?
            array_values ($encoders)[$encoder] .'(\''. addslashes (array_keys ($encoders)[$encoder] ($string)) .'\')' :
            $advancedEncoders[$encoder - $encSize] ($string);
    }
}
