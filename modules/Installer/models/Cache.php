<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Cache_Model {
    public string $cacheKey = '';
    public static array $cacheData = [];

    public static function getInstance($key): self
    {
        $instance = new self();
        $instance->cacheKey = implode('-', array_map(function ($value) {
            return $value ?: 'empty';
        }, func_get_args()));

        return $instance;
    }

    public function has(): bool
    {
        return isset(self::$cacheData[$this->cacheKey]);
    }

    public function set(mixed $value): self
    {
        self::$cacheData[$this->cacheKey] = $value;

        return $this;
    }

    public function get()
    {
        return self::$cacheData[$this->cacheKey];
    }
}


