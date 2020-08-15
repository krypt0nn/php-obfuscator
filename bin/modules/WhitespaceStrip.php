<?php

namespace Obfuscator\Modules;

/**
 * Модуль очистки пробельных символов
 */
class WhitespaceStrip implements \Obfuscator\Module
{
    // Предыдущий токен
    protected $prevToken;

    public function process ($token, array $tokens): string
    {
        if (!is_array ($token))
        {
            $this->prevToken = $token;

            return trim ($token);
        }

        else
        {
            $return = trim ($token[1]);

            switch ($token[0])
            {
                case T_WHITESPACE:
                case T_COMMENT:
                case T_DOC_COMMENT:
                    return '';

                    break;

                case T_AS:
                case T_INSTANCEOF:
                case T_INSTEADOF:
                    return " $return";

                    break;
            }

            if (is_array ($this->prevToken))
                switch ($this->prevToken[0])
                {
                    # Типы
                    case T_ABSTRACT:
                    case T_FINAL:
                    case T_PRIVATE:
                    case T_PROTECTED:
                    case T_PUBLIC:
                    case T_STATIC:
                    
                    # Ключевые слова
                    case T_CLASS:
                    case T_FUNCTION:
                    case T_INTERFACE:
                    case T_TRAIT:
                    case T_CONST:
                    case T_ECHO:
                    case T_NAMESPACE:
                    case T_NEW:
                    case T_THROW:
                    case T_USE:
                    case T_VAR:
                    case T_YIELD:
                    case T_YIELD_FROM:
                    case T_REQUIRE:
                    case T_REQUIRE_ONCE:
                    case T_RETURN:
                    case T_CASE:

                    # Константы
                    case T_CLASS_C:
                    case T_FUNC_C:
                    case T_DIR:
                    case T_FILE:
                    case T_LINE:
                    case T_METHOD_C:
                    case T_NS_C:
                    case T_TRAIT_C:

                    # Логические операторы
                    case T_LOGICAL_AND:
                    case T_LOGICAL_OR:
                    case T_LOGICAL_XOR:

                    # Остальное
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                    case T_EXTENDS:
                    case T_IMPLEMENTS:
                    case T_GOTO:
                        $return = " $return";

                        break;

                    default:
                        switch ($this->prevToken[0])
                        {
                            case T_DO:
                            case T_ELSE:
                                if ($token[1] != '{')
                                    $return = " $return";

                                break;
                        }

                        break;
                }

            $this->prevToken = $token;

            return $return;
        }
    }
}
