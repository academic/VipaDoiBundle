<?php

namespace Vipa\DoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class Body
{
    /** @var Journal */
    public $journal;

    public function __construct()
    {
        $this->journal = new Journal();
    }
}
