<?php

namespace Cache;

/**
 * Interface for caching object
 */
interface CacheItemInterface
{

    /**
     * Get the value of the object
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Check is cache item still relevant
     *
     * @return bool
     */
    public function isValid();


}