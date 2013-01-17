<?php

use Doctrine\ORM\Version;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Formatter\OutputFormatter;

require LIBS_DIR . '/Hnizdil/bootstrap.php';

$databasePlatform = $container->em->getConnection()->getDatabasePlatform();
$databasePlatform->registerDoctrineTypeMapping('varbinary', 'string');
$databasePlatform->registerDoctrineTypeMapping('binary',    'string');
$databasePlatform->registerDoctrineTypeMapping('enum',      'string');

$cli = new Application('Doctrine Command Line Interface', Version::VERSION);

$cli->setCatchExceptions(TRUE);

$cli->setHelperSet(new HelperSet([
	'em'     => new EntityManagerHelper($container->em),
	'db'     => new ConnectionHelper($container->em->getConnection()),
	'dialog' => new DialogHelper,
]));

ConsoleRunner::addCommands($cli);

$config = new Configuration(
	$container->em->getConnection(),
	new OutputWriter(function($message) {
		static $formatter = NULL;
		if ($formatter === NULL) {
			$formatter = new OutputFormatter(TRUE);
		}
		echo $formatter->format($message) . "\n";
	}));

$config->setName('Doctrine Migrations');
$config->setMigrationsNamespace('DoctrineMigrations');
$config->setMigrationsDirectory(
	$container->parameters['appDir'] . '/models/Migrations');

$config->registerMigrationsFromDirectory(
	$config->getMigrationsDirectory());

$commands = [
	new Command\DiffCommand,
	new Command\ExecuteCommand,
	new Command\GenerateCommand,
	new Command\MigrateCommand,
	new Command\StatusCommand,
	new Command\VersionCommand,
];

foreach ($commands as $command) {
	$command->setMigrationConfiguration($config);
}

$cli->addCommands($commands);

$cli->run();
