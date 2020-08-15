<?php

namespace Obfuscator;

/**
 * Интерфейс модуля
 */
interface Module
{
    /**
     * @param string|array $token
     * @param array $tokens
     * 
     * @return string
     */
    public function process ($token, array $tokens): string;
}
