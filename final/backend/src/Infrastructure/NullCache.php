<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 */

declare(strict_types=1);

namespace App\Infrastructure;

class NullCache implements CacheInterface
{
    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        return null;
    }

    /**
     * @param string $key
     * @param array $value
     * @param int $ttl
     * @return CacheInterface
     */
    public function set(string $key, array $value, int $ttl): CacheInterface
    {
        return $this;
    }

    /**
     * @param string $key
     * @return CacheInterface
     */
    public function invalidate(string $key): CacheInterface
    {
        return $this;
    }
}
