<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Infrastructure\CacheInterface;
use App\Infrastructure\FileCache;
use App\MediaLive;
use App\MediaPackage;
use App\MediaStore;
use Aws\MediaLive\MediaLiveClient;
use Aws\MediaPackage\MediaPackageClient;
use Aws\MediaStore\MediaStoreClient;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    /**
     * @param ContainerInterface $c
     * @return array
     */
    function getAwsDefaultOptions(ContainerInterface $c): array
    {
        $settings = $c->get(SettingsInterface::class)->get('aws');
        if (empty($settings['key'])) {
            throw new \RuntimeException('Please define AWS_ACCESS_KEY_ID in .env file');
        }
        if (empty($settings['secret'])) {
            throw new \RuntimeException('Please define AWS_SECRET_ACCESS_KEY in .env file');
        }
        return [
            'credentials' => [
                'key' => $settings['key'],
                'secret' => $settings['secret'],
            ],
            'region' => $settings['region'] ?? 'us-east-1',
        ];
    }

    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        CacheInterface::class => function () {
            return new FileCache(__DIR__ . '/../var/cache/app.json');
        },
        MediaLive::class => function (ContainerInterface $c) {
            return new MediaLive(
                new MediaLiveClient(
                    [
                        'version' => '2017-10-14',
                    ] + getAwsDefaultOptions($c)
                )
            );
        },
        MediaPackage::class => function (ContainerInterface $c) {
            return new MediaPackage(
                new MediaPackageClient(
                    [
                        'version' => '2017-10-12',
                    ] + getAwsDefaultOptions($c)
                )
            );
        },
        MediaStore::class => function (ContainerInterface $c) {
            return new MediaStore(
                new MediaStoreClient(
                    [
                        'version' => '2017-09-01',
                    ] + getAwsDefaultOptions($c)
                )
            );
        },
    ]);
};
