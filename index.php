<?php
require 'app/functions.php';
$loader = require 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Redstage\Downloader\Command\DownloadCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


$containerBuilder = new ContainerBuilder();
$containerBuilder->setParameter('log.exception', 'var/log/exception.log');
$containerBuilder
    ->register('stream_handler', '\Monolog\Handler\StreamHandler')
    ->addArgument('%log.exception%')
    ->addArgument(\Monolog\Logger::ERROR);

$containerBuilder
    ->register('logger', '\Monolog\Logger')
    ->addArgument('exception');

$containerBuilder
    ->register('media', 'Redstage\Downloader\Model\Media\MediaSystem');

$containerBuilder
    ->register('downloader', '\Redstage\Downloader\Model\Download\Downloader')
    ->addArgument(new Reference('media'))
    ->addArgument(new Reference('logger'));

$containerBuilder
    ->register('downloader_command', '\Redstage\Downloader\Command\DownloadCommand')
    ->addArgument(new Reference('downloader'));

$downloaderCommand = $containerBuilder->get('downloader_command');

$application = new Application();

$application->add($downloaderCommand);

$application->run();