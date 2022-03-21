<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 * @since  2022-03-15
 */

namespace App;

use Aws\MediaPackage\MediaPackageClient;
use Aws\Result;

class MediaPackage
{
    /**
     * @param MediaPackageClient $mediaPackageClient
     */
    public function __construct(private MediaPackageClient $mediaPackageClient)
    {
    }

    /**
     * @param string $channelId
     * @return Result
     */
    public function listEndpointsByChannel(string $channelId): Result
    {
        return $this->mediaPackageClient->listOriginEndpoints([
            'ChannelId' => $channelId,
        ]);
    }
}
