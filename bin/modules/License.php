<?php

namespace Obfuscator\Modules;

/**
 * Модуль лицензирования кода
 * 
 * ! Вызывать в конце pipeline
 * Создаёт в начале кода строку $license = '';
 * Если в строку передана не действительная лицензия - код не будет работать
 */
class License implements \Obfuscator\Module
{
    // Секретный ключ
    public string $key = 'EFHJOIJO@I#JOIDWJADOIWAUDHISUHFOAWLDLIRUQ@H&@&@&*@&*';

    /**
     * [@var $verifyerFrequency = 7]  - соотношение длины кода к длине модуля проверки лицензии
     * @var array $obfuscatorPipeline - pipeline объекта Obfuscator, которым будут обработаны вставки кода проверки лицензии
     */
    public int $verifyerFrequency = 7;
    public array $obfuscatorPipeline;

    protected bool $firstCall = true;
    protected int $tokenPosition = -1;

    protected array $licenseTags = [];

    /**
     * @var string $licenseExpire   - код, вызываемый при окончании лицензии
     * @var string $licenseBreakout - код, вызываемый для проверки лицензии
     * @var string $licenseVerifyer - код, находящийся в начале кода (первая проверка лицензии)
     */
    protected string $licenseExpire   = '';
    protected string $licenseBreakout = '';
    protected string $licenseVerifyer = '';

    protected int $skippedTextLength = 0;

    public function __construct (array $params = [])
    {
        $chars = array_merge (range ('a', 'z'), range ('A', 'Z'), ['_']);

        $this->licenseTags = [
            $chars[array_rand ($chars)],
            $this->getRandomString (),
            $chars[array_rand ($chars)],
            $chars[array_rand ($chars)]
        ];

        $this->obfuscatorPipeline = [
            new StringEncoder,
            new SilentCalls
        ];

        $this->licenseExpire = 'die(\'Your license is expired\');';
        
        foreach ($params as $property => $value)
            if (isset ($this->$property))
                $this->$property = $value;

        if (!isset ($params['licenseBreakout']))
            $this->licenseBreakout = "\${$this->licenseTags[2]}=hexdec(@explode('-',\$GLOBALS['{$this->licenseTags[1]}'])[1]);\${$this->licenseTags[3]}=substr(md5(\${$this->licenseTags[2]}.'". addslashes ($this->key) ."'),0,15);if(time()>\${$this->licenseTags[2]}||strtoupper(\${$this->licenseTags[3]}.'-'.dechex(\${$this->licenseTags[2]}).'-'.substr(md5(\${$this->licenseTags[3]}^str_repeat('". addslashes ($this->key) ."',". ceil (15 / strlen (addslashes ($this->key))) .")),0,10))!=\$GLOBALS['{$this->licenseTags[1]}']){". $this->licenseExpire ."}";

        if (!isset ($params['licenseVerifyer']))
        {
            $this->licenseVerifyer = "(function(\${$this->licenseTags[0]}){\$GLOBALS['{$this->licenseTags[1]}']=\${$this->licenseTags[0]};". $this->licenseBreakout ."})(\${$this->licenseTags[0]});unset(\${$this->licenseTags[0]});";

            $this->licenseVerifyer = substr ((new \Obfuscator\Obfuscator ([
                'pipeline' => $this->obfuscatorPipeline
            ]))->obfuscate ('<?php '. $this->licenseVerifyer), 6);

            $this->licenseVerifyer = "\n\${$this->licenseTags[0]}='license goes here';\n". $this->licenseVerifyer;
        }

        if (!isset ($params['licenseBreakout']))
            $this->licenseBreakout = substr ((new \Obfuscator\Obfuscator ([
                'pipeline' => $this->obfuscatorPipeline
            ]))->obfuscate ('<?php '. $this->licenseBreakout), 6);
    }

    public function process ($token, array $tokens): string
    {
        ++$this->tokenPosition;

        if (!$this->firstCall)
        {
            $return = is_array ($token) ?
                $token[1] : $token;

            $this->skippedTextLength += strlen ($return);

            if ($this->skippedTextLength >= strlen ($this->licenseBreakout) * $this->verifyerFrequency && $return == ';')
            {
                $this->skippedTextLength = 0;

                $return .= $this->licenseBreakout;
            }

            return $return;
        }

        $namespace = false;

        foreach ($tokens as $id => $t)
            if ($t[0] == T_NAMESPACE)
            {
                $namespace = true;

                break;
            }
        
        if ($namespace)
        {
            if ($this->tokenPosition > $id && is_string ($token) && $token == ';')
            {
                $this->firstCall = false;

                return ';'. $this->licenseVerifyer;
            }

            return is_array ($token) ?
                $token[1] : $token;
        }

        else
        {
            $this->firstCall = false;

            return '<?php'. $this->licenseVerifyer;
        }
    }

    /**
     * Получение ключа лицензии
     * 
     * @param int $expireTimestamp - временная метка, до которой действует лицензия
     * 
     * @return string - возвращает лицензионный ключ
     */
    public function getLicense (int $expireTimestamp): string
    {
        $hash = substr (md5 ($expireTimestamp . $this->key), 0, 15);
        $mask = substr (md5 ($hash ^ str_repeat ($this->key, ceil (15 / strlen ($this->key)))), 0, 10);

        return strtoupper ($hash .'-'. dechex ($expireTimestamp) .'-'. $mask);
    }

    /**
     * Проверка лицензии
     * 
     * @param string $license - лицензионный ключ
     * 
     * @return bool
     */
    public function checkLicense (string $license): bool
    {
        $expireTimestamp = hexdec (@explode ('-', $license)[1]);

        return $expireTimestamp > time () && $this->getLicense ($expireTimestamp) == $license;
    }

    /**
     * Получение рандомной строки
     * 
     * [@param int $min = 3]
     * [@param int $max = 67]
     * 
     * @return string
     */
    protected function getRandomString (int $min = 3, int $max = 67): string
    {
        $chars = array_merge (range ('a', 'z'), range ('A', 'Z'), range (0, 9));

        $length = rand ($min, $max);
        $string = '';

        for ($i = 0; $i < $length; ++$i)
            $string .= $chars[array_rand ($chars)];

        return $string;
    }
}
