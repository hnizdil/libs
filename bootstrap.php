<?php

use Nette\Framework;
use Nette\Http\UrlScript;
use Nette\Config\Configurator;
use Nette\Utils\AssertionException;
use Doctrine\DBAL\Types\Type;
use Hnizdil\Service\ClassLoader;

require LIBS_DIR . '/Nette/loader.php';
require LIBS_DIR . '/Hnizdil/Service/ClassLoader.php';

// autoloading podle SPR-0
$loader = new ClassLoader(NULL, LIBS_DIR);
$loader->register();
$loader = new ClassLoader('Symfony', LIBS_DIR . '/Doctrine/Symfony');
$loader->register();
$loader = new ClassLoader(NULL, APP_DIR);
$loader->register();
$loader = new ClassLoader('Entity', APP_DIR . '/models/Entity');
$loader->register();
$loader = new ClassLoader('Test', APP_DIR . '/../test');
$loader->register();

// Nette se obejde bez ini_set, pokud je to potřeba
Framework::$iAmUsingBadHost = !function_exists('ini_set');

// section může být nadefinována jako proměnná prostředí
if (getenv('NETTE_CONFIG_SECTION')) {
	$configSection = getenv('NETTE_CONFIG_SECTION');
}
// v CLI zkusíme jako section hostname
elseif (PHP_SAPI === 'cli') {
	$configSection = php_uname('n');
}
// jinak jsme v produkci
else {
	$configSection = 'production';
}

// configurator se vytváří funkcí, protože může být
// kvůli neexistující sekci nutné vytvořit ho znovu
$getConfigurator = function ($section) {

	$tempDirPathFile = realpath(APP_DIR . '/config/temp-dir-path.txt');
	if ($tempDirPathFile) {
		$tempDirPath = trim(file_get_contents($tempDirPathFile));
	}
	else {
		$tempDirPath = defined('TEMP_DIR') ? TEMP_DIR : APP_DIR . '/../temp';
	}
	if (!is_dir($tempDirPath) && !@mkdir($tempDirPath)) {
		throw new Exception("Temp dir '{$tempDirPath}' could not be created.");
	}

	$logDir = defined('LOG_DIR') ? LOG_DIR : APP_DIR . '/../log';

	$configurator = new Configurator;
	$configurator->setDebugMode($section !== 'production');
	$configurator->setTempDirectory($tempDirPath);
	$configurator->enableDebugger($logDir, 'hnizdil@gmail.com');
	$configurator->addParameters(array(
		'appDir'  => APP_DIR,
		'wwwDir'  => WWW_DIR,
		'libsDir' => LIBS_DIR,
	));

	$configurator->addConfig(APP_DIR . '/config/config.neon', $section);

	return $configurator;

};

// v případě CLI v produkci neznáme hostname, takže
// příslušná sekce možná chybí v config.neon
try {
	$container = $getConfigurator($configSection)->createContainer();
}
// sekce neexistuje, načteme production
catch (AssertionException $e) {
	$container = $getConfigurator('production')->createContainer();
}

// v CLI nastavíme URL pro generování linků podle configu
if (PHP_SAPI === 'cli' && isset($container->parameters['baseUri'])) {
	$url = new UrlScript($container->parameters['baseUri']);
}
// jinak se URL získá z HTTP požadavku
else {
	$url = $container->httpRequest->getUrl();
}

$container->parameters['libsDir']  = LIBS_DIR;
$container->parameters['baseUri']  = $url->baseUrl;
$container->parameters['basePath'] = $url->basePath;

// zaregistrování nadefinovaných doctrine typů
$types    = $container->parameters['doctrine']['dataTypes'];
$platform = $container->em->getConnection()->getDatabasePlatform();
foreach ($types as $type => $dataTypeClass) {
	Type::addType($type, $dataTypeClass);
	$platform->markDoctrineTypeCommented(Type::getType($type));
}

// odplevelení globálního prostoru kromě $container,
// který bude potřebovat bootstrap.php aplikace
unset (
	$loader,
	$configSection,
	$configurator,
	$url,
	$types,
	$platform,
	$type,
	$dataTypeClass);
