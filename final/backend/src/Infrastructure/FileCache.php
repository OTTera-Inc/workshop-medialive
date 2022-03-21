<?php
/**
 * @author Vinícius Campitelli <vinicius@ottera.tv>
 */

declare(strict_types=1);

namespace App\Infrastructure;

use stdClass;

class FileCache implements CacheInterface
{
    /**
     * @var stdClass
     */
    private stdClass $cache;

    /**
     * @param string $filename
     */
    public function __construct(private string $filename)
    {
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        $this->readCache();

        if (!isset($this->cache->{$key})) {
            return null;
        }

        $object = $this->cache->{$key};
        if ((!$object instanceof \stdClass) ||
            (!isset($object->value)) ||
            (!isset($object->ttl)) ||
            ($object->ttl < \time())) {
            return null;
        }

        return (array) $object->value;
    }

    /**
     * @param string $key
     * @param array $value
     * @param int $ttl
     * @return CacheInterface
     */
    public function set(string $key, array $value, int $ttl): CacheInterface
    {
        $this->readCache();
        $this->cache->{$key} = $this->toCacheObject($value, $ttl);
        return $this;
    }

    /**
     * @param array $value
     * @param int $ttl
     * @return stdClass
     */
    private function toCacheObject(array $value, int $ttl): stdClass
    {
        $object = new stdClass();
        $object->value = $value;
        $object->ttl = \time() + $ttl;
        return $object;
    }

    /**
     * @param string $key
     * @return CacheInterface
     */
    public function invalidate(string $key): CacheInterface
    {
        $this->readCache();
        unset($this->cache->{$key});
        return $this;
    }

    /**
     * Flushing data to file
     */
    public function __destruct()
    {
        if ((isset($this->filename)) && (!empty($this->cache))) {
            \file_put_contents($this->filename, \json_encode($this->cache), \LOCK_EX);
        }
    }

    /**
     * @return void
     */
    private function readCache(): void
    {
        if (!isset($this->cache)) {
            $cache = new stdClass();
            if (\is_file($this->filename)) {
                $temp = \file_get_contents($this->filename);
                if (!empty($temp)) {
                    $temp = \json_decode($temp, false);
                    if ($temp instanceof stdClass) {
                        $cache = $temp;
                    }
                }
            }
            $this->cache = $cache;
        }
    }
}
