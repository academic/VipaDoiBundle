<?php

namespace BulutYazilim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class JournalMetadata
{
    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $fullTitle;

    /**
     * @var Issn
     */
    public $issn;

    public function __construct()
    {
        $this->issn = new Issn();
    }
}
