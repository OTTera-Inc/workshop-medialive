<?php

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use App\Application\Actions\Action;
use App\Infrastructure\CacheInterface;
use App\MediaLive;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class ChangeInputAction extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     * @param MediaLive $mediaLive
     */
    public function __construct(
        LoggerInterface $logger,
        private CacheInterface $cache,
        private MediaLive $mediaLive,
    ) {
        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $channelId = $this->resolveArg('id');
        $input = \trim($this->getFormData()['input'] ?? '');
        if (empty($input)) {
            throw new HttpBadRequestException($this->request, 'Entrada não informada');
        }

        $this->mediaLive->switchInputTo($channelId, $input);
        $this->cache->invalidate(ListChannelsAction::class);

        $this->logger->info("Switched input of {$channelId} to '{$input}'.");

        return $this->respondWithData();
    }
}
