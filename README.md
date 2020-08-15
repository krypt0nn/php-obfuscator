<h1 align="center">🚀 php-obfuscator</h1>

**php-obfuscator** - библиотека для обфускации PHP кода

## Установка

```cmd
php qero.phar i KRypt0nn/php-obfuscator
```

```php
<?php

require 'qero-packages/autoload.php';
```

[Что такое Qero?](https://github.com/KRypt0nn/Qero)

<p align="center">или</p>

Скачайте репозиторий и подключите главный файл пакета:

```php
<?php

require 'php-obfuscator/obfuscator.php';
```

## Быстрый старт

Библиотека **php-obfuscator** построена по принципу расширяемости. Для обфускации кода можно использовать два варианта класса `Obfuscator\Obfuscator`:

```php
use Obfuscator\Obfuscator;

use Obfuscator\Modules\{
    WhitespaceStrip,
    License
};

$obfuscator = new Obfuscator ([
    'pipeline' => [
        new WhitespaceStrip,
        new License ([
            'key' => '#JDFALK:ESF#(UR(URPFOIASJEF*#IWY*&RT(*&WHJKFD'
        ])
    ]
]);

$obfuscated = $obfuscator->obfuscate ($code);
```

или

```php
use Obfuscator\Obfuscator;

use Obfuscator\Modules\{
    WhitespaceStrip,
    License
};

$obfuscated = Obfuscator::process ($code, [
    'pipeline' => [
        new WhitespaceStrip,
        new License ([
            'key' => '#JDFALK:ESF#(UR(URPFOIASJEF*#IWY*&RT(*&WHJKFD'
        ])
    ]
]);
```

При этом в качестве алгоритмов обфускации кода выступают модули, передаваемые в параметр pipeline. Процесс обфускации выглядит следующим образом: сперва код обрабатывает первый модуль из pipeline, затем второй, третий и так по порядку

Каждый модуль может иметь свои настройки. За подробностями можно обратиться к коду

## Пример работы

```php
use Obfuscator\Obfuscator;

echo Obfuscator::process (file_get_contents (__FILE__));
```

Вывод:

```php
<?php require str_rot13('boshfpngbe.cuc');use Obfuscator\Obfuscator;echo Obfuscator::process(base64_decode((chr(hexdec('5a')).chr(base_convert('11001',3,10)).chr(base_convert('1230',4,10)).chr(0x73).chr(31^69).chr(4386/51).chr(101-44).chr(3^109).chr(-ord('T')+174).chr(sqrt(7744)).chr(23+59).chr(hexdec('66')).chr(1780/20).chr(ord('B')+-16).chr(ord('u')+-60).chr(0x75).chr(190-90).chr(26+45).chr(ord('y')+-35).chr(ord('N')+39).chr(base_convert('3d',29,10)).chr(97-25).chr(0x4d).chr(-ord('6')+115)))(__FILE__));
```

Автор: [Подвирный Никита](https://vk.com/technomindlp). Специально для [Enfesto Studio Group](https://vk.com/hphp_convertation)