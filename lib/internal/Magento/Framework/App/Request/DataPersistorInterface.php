<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

interface DataPersistorInterface
{
    /**
     * Store data by key
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function set($key, $data);

    /**
     * Retrieve data by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Clear data by key
     *
     * @param string $key
     * @return void
     */
    public function clear($key);
}
