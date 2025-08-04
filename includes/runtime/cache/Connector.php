<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once dirname(__FILE__) . '/Connectors.php';

class Vtiger_Cache_Connector
{
    protected $connection;

    protected function __construct()
    {
        if (!$this->connection) {
            $this->connection = Vtiger_Cache_Connector_Memory::getInstance();
        }
    }

    protected function cacheKey($ns, $key)
    {
        if (is_array($key)) {
            $key = implode('-', $key);
        }

        return $ns . '-' . $key;
    }

    public function set($namespace, $key, $value)
    {
        $this->connection->set($this->cacheKey($namespace, $key), $value);
    }

    public function get($namespace, $key)
    {
        return $this->connection->get($this->cacheKey($namespace, $key));
    }

    public function delete($namespace, $key)
    {
        $this->connection->delete($this->cacheKey($namespace, $key));
    }

    public function has($namespace, $key)
    {
        return $this->get($namespace, $key) !== false;
    }

    public function flush()
    {
        $this->connection->flush();

        $time = time() + 1; //one second future
        while (time() < $time) {
            //sleep
        }
    }

    public static function getInstance()
    {
        static $singleton = null;
        if ($singleton === null) {
            $singleton = new self();
        }

        return $singleton;
    }
}