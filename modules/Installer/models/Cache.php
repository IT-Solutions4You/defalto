<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Cache_Model
{
    public string $cacheKey = '';
    public static array $cacheData = [];

    public static function getInstance(string|int|float|bool|null ...$keyParts): self
    {
        $instance = new self();
        $instance->cacheKey = hash('sha256', serialize($keyParts));

        return $instance;
    }

    public function has(): bool
    {
        return array_key_exists($this->cacheKey, self::$cacheData);
    }

    public function set(mixed $value): self
    {
        self::$cacheData[$this->cacheKey] = $value;

        return $this;
    }

    public function get(): mixed
    {
        return self::$cacheData[$this->cacheKey];
    }

    public function delete(): void
    {
        unset(self::$cacheData[$this->cacheKey]);
    }

    public static function clear(): void
    {
        self::$cacheData = [];
    }
}


