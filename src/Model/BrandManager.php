<?php

namespace App\Model;

/**
 *
 */
class BrandManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'brand';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
}
