<?php

namespace App\Model;

/**
 *
 */
class ColorManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'color';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
}