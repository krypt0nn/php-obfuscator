<?php

namespace Obfuscator\Modules;

/**
 * Модуль оптимизации имён переменных
 */
class VarOptimizer implements \Obfuscator\Module
{
    /**
     * Список переменных, которые нельзя переименовывать
     */
    public array $whitelist = [
        '$GLOBALS', '$_SERVER', '$_ENV',
        '$_GET',    '$_POST',   '$_REQUEST',
        '$_FILES',  '$_COOKIE', '$_SESSION',
        '$this'
    ];

    /** 
     * [@var $vars = []] - ассоциативный массив название переменных
     * [@var $tokensStack = []] - стек токенов
     */
    protected array $vars = [];
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
            switch ($token[0])
            {
                case T_VARIABLE:
                    if (in_array ($token[1], $this->whitelist))
                        return $token[1];

                    $part = array_slice ($this->tokensStack, -2);
                    
                    if (in_array ($part[0], [
                        T_PRIVATE,
                        T_PROTECTED,
                        T_PUBLIC,
                        T_STATIC,
                        T_VAR
                    ])) return $token[1];

                    if (!isset ($this->vars[$token[1]]))
                        $this->vars[$token[1]] = '$'. $this->getVarName ();
                    
                    return $this->vars[$token[1]];

                    break;
            }

            $this->tokensStack[] = $token[0];

            return $token[1];
        }
    }

    /**
     * Генератор названия переменной
     * 
     * @return string
     */
    protected function getVarName (): string
    {
        $firstLetters  = array_merge (range ('a', 'z'), range ('A', 'Z'), ['_']);
        $secondLetters = array_merge ($firstLetters, range (0, 9));

        $varsSize = sizeof ($this->vars);

        $flSize = sizeof ($firstLetters);
        $slSize = sizeof ($secondLetters);

        if ($varsSize < $flSize)
            return $firstLetters[$varsSize];

        else
        {
            $name     = $firstLetters[$varsSize % $flSize];
            $varsSize = intdiv ($varsSize, $flSize);

            do
            {
                $name    .= $secondLetters[$varsSize % $slSize];
                $varsSize = intdiv ($varsSize, $slSize);
            }

            while ($varsSize > 0);
        }

        return $name;
    }
}
