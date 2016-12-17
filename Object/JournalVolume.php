<?php

namespace Ojs\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class JournalVolume
{
    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $volume;

    /** @var DoiData */
    public $doiData;
}
