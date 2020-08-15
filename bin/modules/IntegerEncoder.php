<?php

namespace Obfuscator\Modules;

/**
 * Модуль шифрования чисел
 */
class IntegerEncoder implements \Obfuscator\Module
{
    public function process ($token, array $tokens): string
    {
        if (is_array ($token) && $token[0] == T_LNUMBER)
            $token = $token[1];

        elseif (!is_int ($token))
            return is_array ($token) ?
                $token[1] : $token;

        if ($token == 0 && rand (0, 1) == 0)
            return '(int)false';

        elseif ($token == 1 && rand (0, 1) == 0)
            return '(int)true';

        else
        {
            $delta = rand (2, max ($token, 3));

            switch (rand (0, 9))
            {
                # Безопасно для использования в качестве параметров классов и функций
                case 0:
                    return ($token * $delta) .'/'. $delta;

                case 1:
                    return ($token - $delta) .'+'. $delta;

                case 2:
                    return ($token + $delta) .'-'. $delta;

                case 3:
                    return '0x'. dechex ($token);

                # Не безопасно
                case 4:
                    return 'sqrt('. ($token * $token) .')';

                case 5:
                    return 'base_convert(\''. base_convert ($token, 10, $base = rand (2, 32)) .'\','. $base .',10)';

                case 6:
                    return 'hexdec(\''. dechex ($token) .'\')';

                case 7:
                    return ($token ^ $delta) .'^'. $delta;

                case 8:
                    return 'ord(\''. addslashes (chr ($delta = rand (32, 126))) .'\')+'. ($token - $delta);

                case 9:
                    return '-ord(\''. addslashes (chr ($delta = rand (32, 126))) .'\')+'. ($token + $delta);
            }
        }
    }
}
