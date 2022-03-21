<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use App\Application\Actions\Action;
use App\Infrastructure\CacheInterface;
use App\MediaLive;
use App\MediaStore;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListChannelsAction extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     * @param MediaLive $mediaLive
     * @param MediaStore $mediaStore
     */
    public function __construct(
        LoggerInterface $logger,
        private CacheInterface $cache,
        private MediaLive $mediaLive,
        private MediaStore $mediaStore,
    ) {
        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->respondWithData(
            $this->getResponse()
        );
    }

    /**
     * @return array
     */
    private function getResponse(): array
    {
        $response = $this->cache->get(__CLASS__);
        if ($response !== null) {
            $this->logger->info('Canais listados do cache');
            return $response;
        }

        $response = [];
        $result = $this->mediaLive->listChannels();
        if (!empty($result['Channels'])) {
            $response = $this->parseResponse($result['Channels']);
            if (!empty($response)) {
                $this->cache->set(__CLASS__, $response, 40);
                $this->logger->info('Canais listados da API da AWS e salvos no cache por 40 segundos');
            }
        }

        return $response;
    }

    /**
     * @param array $channels
     * @return array
     */
    private function parseResponse(array $channels): array
    {
        $result = $this->mediaStore->listContainers();
        $containers = $result['Containers'] ?? [];

        $getContainerByEndpoint = function (string $host) use ($containers): ?array {
            foreach ($containers as $container) {
                if (\parse_url($container['Endpoint'], \PHP_URL_HOST) === $host) {
                    return $container;
                }
            }
            return null;
        };

        $response = [];
        foreach ($channels as $channel) {
            if ((empty($channel['InputAttachments'])) || (empty($channel['Destinations']))) {
                continue;
            }

            $row = [
                'id' => $channel['Id'],
                'arn' => $channel['Arn'],
                'name' => $channel['Name'],
                'state' => $channel['State'],
                'running' => $channel['State'] === 'RUNNING',
                'inputs' => [],
                'destination' => null,
            ];

            // Only returning MediaStore channels
            foreach ($channel['Destinations'] as $destination) {
                if (empty($destination['Settings'][0]['Url'])) {
                    continue;
                }
                $components = \parse_url($destination['Settings'][0]['Url']);
                if ($components['scheme'] !== 'mediastoressl') {
                    continue;
                }
                // Removing protocol. 16 is the length of mediastoressl://
                $container = $getContainerByEndpoint($components['host']);
                if (empty($container)) {
                    continue;
                }
                $row['destination'] = $container['Endpoint'] . $components['path'] . '.m3u8';
                break;
            }

            if (empty($row['destination'])) {
                continue;
            }

            $currentInputName = $this->getCurrentInputName($channel);
            foreach ($channel['InputAttachments'] as $input) {
                $row['inputs'][] = [
                    'id' => $input['InputId'],
                    'name' => $input['InputAttachmentName'],
                    'active' => $input['InputAttachmentName'] === $currentInputName,
                ];
            }

            $response[] = $row;
        }

        return $response;
    }

    /**
     * @param array $channel
     * @return string|null
     */
    private function getCurrentInputName(array $channel): ?string
    {
        if (count($channel['InputAttachments']) === 1) {
            return current($channel['InputAttachments'])['InputAttachmentName'];
        }

        $schedule = $this->mediaLive->describeSchedule($channel['Id']);
        $last = null;
        foreach ($schedule['ScheduleActions'] as $action) {
            if (!empty($action['ScheduleActionSettings']['InputSwitchSettings']['InputAttachmentNameReference'])) {
                $last = $action['ScheduleActionSettings']['InputSwitchSettings']['InputAttachmentNameReference'];
            }
        }

        return $last;
    }
}
