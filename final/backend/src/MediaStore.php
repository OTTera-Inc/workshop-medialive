<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 * @since  2022-03-20
 */

namespace App;

use Aws\MediaStore\MediaStoreClient;
use Aws\Result;

class MediaStore
{
    /**
     * @param MediaStoreClient $mediadStoreClient
     */
    public function __construct(private MediaStoreClient $mediadStoreClient)
    {
    }

    /**
     * @return Result
     */
    public function listContainers(): Result
    {
        return $this->mediadStoreClient->listContainers();
    }

    /**
     * @param string $containerName
     * @return Result
     */
    public function describeContainer(string $containerName): Result
    {
        return $this->mediadStoreClient->describeContainer([
            'ContainerName' => $containerName,
        ]);
    }
}
