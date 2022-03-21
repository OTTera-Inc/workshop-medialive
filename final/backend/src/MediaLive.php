<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 * @since  2022-03-10
 */

declare(strict_types=1);

namespace App;

use Aws\Exception\AwsException;
use Aws\MediaLive\MediaLiveClient;
use Aws\Result;

/**
 * Facade for the MediaLive client
 */
class MediaLive
{
    /**
     * @param MediaLiveClient $client
     */
    public function __construct(private MediaLiveClient $client)
    {
    }

    /**
     * @param string $channelId
     * @return Result
     */
    public function describeSchedule(string $channelId): Result
    {
        return $this->client->describeSchedule([
            'ChannelId' => $channelId,
        ]);
    }

    /**
     * @return Result
     */
    public function listChannels(): Result
    {
        return $this->client->listChannels();
    }

    /**
     * @param string $channelId
     * @param string $name
     * @return $this
     * @throws AwsException
     */
    public function switchInputTo(string $channelId, string $name): self
    {
        $this->client->batchUpdateSchedule([
            'ChannelId' => $channelId,
            'Creates' => [
                'ScheduleActions' => [
                    [
                        'ActionName' => 'SWITCH_' .
                            \preg_replace('/[^A-Z0-9_]+/', '_', \strtoupper($name)) . '_' . \date('c'),
                        'ScheduleActionSettings' => [
                            'InputSwitchSettings' => [
                                'InputAttachmentNameReference' => $name,
                            ],
                        ],
                        'ScheduleActionStartSettings' => [
                            'ImmediateModeScheduleActionStartSettings' => [],
                        ],
                    ],
                ],
            ],
        ]);
        return $this;
    }

    /**
     * @param string $channelId
     * @param int $duration
     * @return int Generated event ID
     * @throws AwsException
     */
    public function insertAdBreak(string $channelId, int $duration): int
    {
        // Generating a random 32-bit int for the splice_event_id
        try {
            $eventId = \random_int(1, 2147483647);
        } catch (\Exception) {
            $eventId = \mt_rand();
        }

        $actionName = "AD_BREAK_{$eventId}_" . \date('c');

        $this->client->batchUpdateSchedule([
            'ChannelId' => $channelId,
            'Creates' => [
                'ScheduleActions' => [
                    [
                        'ActionName' => $actionName,
                        'ScheduleActionSettings' => [
                            'Scte35SpliceInsertSettings' => [
                                'SpliceEventId' => $eventId,
                                'Duration' => $duration,
                            ],
                        ],
                        'ScheduleActionStartSettings' => [
                            'ImmediateModeScheduleActionStartSettings' => [],
                        ],
                    ],
                    [
                        'ActionName' => "RETURN_{$actionName}",
                        'ScheduleActionSettings' => [
                            'Scte35ReturnToNetworkSettings' => [
                                'SpliceEventId' => $eventId,
                            ],
                        ],
                        'ScheduleActionStartSettings' => [
                            'FixedModeScheduleActionStartSettings' => [
                                'Time' => (new \DateTime())
                                    ->add(new \DateInterval("PT{$duration}S"))
                                    ->setTimezone(new \DateTimezone("UTC"))
                                    ->format("Y-m-d\TH:i:s\Z"),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $eventId;
    }
}
