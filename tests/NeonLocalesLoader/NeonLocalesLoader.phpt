<?php declare(strict_types=1);

require '../bootstrap.php';

use blitzik\Router\LocalesLoader\NeonLocalesLoader;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;

$storage = new MemoryStorage();
$localesLoader = new NeonLocalesLoader(__DIR__  . '/withoutDefaultLocaleSpecified.neon', $storage);

Assert::same(['cs', 'ru', 'en', 'sk'], $localesLoader->loadLocales());
Assert::same('cs', $localesLoader->getDefaultLocale());

// -----

$storage = new MemoryStorage();
$localesLoader = new NeonLocalesLoader(__DIR__  . '/withDefaultLocaleSpecified.neon', $storage);

Assert::same(['cs', 'ru', 'en', 'sk'], $localesLoader->loadLocales());
Assert::same('en', $localesLoader->getDefaultLocale());