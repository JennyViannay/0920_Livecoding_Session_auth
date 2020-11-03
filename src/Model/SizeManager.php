<?php

namespace App\Model;

/**
 *
 */
class SizeManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'size';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
}