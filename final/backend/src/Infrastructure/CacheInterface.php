<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 */

declare(strict_types=1);

namespace App\Infrastructure;

interface CacheInterface
{
    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array;

    /**
     * @param string $key
     * @param array $value
     * @param int $ttl
     * @return CacheInterface
     */
    public function set(string $key, array $value, int $ttl): CacheInterface;

    /**
     * @param string $key
     * @return CacheInterface
     */
    public function invalidate(string $key): CacheInterface;
}
