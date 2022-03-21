<?php

declare(strict_types=1);

namespace App\Domain\Channel;

interface ChannelRepository
{
    /**
     * @return Channel[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Channel
     * @throws ChannelNotFoundException
     */
    public function findChannelOfId(int $id): Channel;
}
