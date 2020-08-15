<?php

namespace Obfuscator;

use Obfuscator\Modules\{
    WhitespaceStrip,
    VarOptimizer,
    StringEncoder,
    SilentCalls,
    IntegerEncoder
};

/**
 * Основной класс обфускатора
 */
class Obfuscator
{
    // Список модулей, последовательно вызываемых обфускатором
    public array $pipeline = [];

    /**
     * Конструктор
     * 
     * [@param array $params = []] - массив параметров обфускатора
     */
    public function __construct (array $params = [])
    {
        foreach ($params as $property => $value)
            if (isset ($this->$property))
                $this->$property = $value;
    }

    /**
     * Обфускация кода
     * 
     * @param string $code - код для обфускации либо путь до файла
     * 
     * @return string - возвращает обфусцированный код
     */
    public function obfuscate (string $code): string
    {
        if (file_exists ($code))
            $code = file_get_contents ($code);

        foreach ($this->pipeline as $module)
        {
            $obfuscated = '';
            $tokens = token_get_all ($code);

            /**
             * token: "[char]" or [TOKEN_ID, "[char(s)]", [line]]
             */
            foreach ($tokens as $token)
                $obfuscated .= $module->process ($token, $tokens);
            
            $code = $obfuscated;
        }

        return $obfuscated;
    }

    /**
     * Обфускация кода
     * 
     * Данные метод может быть вызван статически как Obfuscator::process
     * Заданы стандартные параметры обфускатора
     * 
     * @param string $code - кода для обфускации либо путь до файла
     * [@param array $params = null] - массив параметров обфускатора
     * 
     * @return string - возвращает обфусцированный код
     */
    public static function process (string $code, array $params = null): string
    {
        return (new Obfuscator ($params ?: [
            'pipeline' => [
                new VarOptimizer,
                new SilentCalls,
                new StringEncoder,
                new IntegerEncoder,
                new WhitespaceStrip
            ]
        ]))->obfuscate ($code);
    }
}
