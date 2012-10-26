<?php

use Nette\Framework;
use Nette\Config\Configurator;
use Doctrine\DBAL\Types\Type;
use Hnizdil\Service\ClassLoader;

require LIBS_DIR . '/Nette/loader.php';
require LIBS_DIR . '/Hnizdil/Service/ClassLoader.php';

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
unset($loader);

Framework::$iAmUsingBadHost = !function_exists('ini_set');

if (!isset($configSection)) {
	$configSection = getenv('NETTE_CONFIG_SECTION');
}

$configurator = new Configurator;

$configurator->setDebugMode($configSection !== FALSE);
$configurator->setTempDirectory(APP_DIR . '/../temp');
$configurator->enableDebugger(APP_DIR . '/../log', 'hnizdil@gmail.com');

$configurator->addParameters(array(
	'appDir'  => APP_DIR,
	'wwwDir'  => WWW_DIR,
	'libsDir' => LIBS_DIR,
));

$configurator->addConfig(
	APP_DIR . '/config/config.neon',
	$configSection ?: 'production');

unset($configSection);

$container = $configurator->createContainer();

unset($configurator);

$url = $container->httpRequest->getUrl();
$container->parameters['libsDir']  = LIBS_DIR;
$container->parameters['baseUri']  = $url->baseUrl;
$container->parameters['basePath'] = $url->basePath;
unset($url);

foreach ($container->parameters['doctrine']['dataTypes']
	as $type => $dataTypeClass
) {
	Type::addType($type, $dataTypeClass);
}
