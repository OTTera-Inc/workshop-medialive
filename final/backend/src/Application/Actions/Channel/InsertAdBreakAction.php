<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 */

declare(strict_types=1);

namespace App\Application\Actions\Channel;

use App\Application\Actions\Action;
use App\MediaLive;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class InsertAdBreakAction extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param MediaLive $mediaLive
     */
    public function __construct(
        LoggerInterface $logger,
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
        $duration = (int) ($this->getFormData()['duration'] ?? 0);
        if (($duration < 15) || ($duration > 300)) {
            throw new HttpBadRequestException($this->request, 'Duração inválida');
        }

        $this->mediaLive->insertAdBreak($channelId, $duration);

        $this->logger->info("Inserting ad break of {$duration} seconds in {$channelId}.");

        return $this->respondWithData();
    }
}
