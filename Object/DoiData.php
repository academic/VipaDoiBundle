<?php

namespace OkulBilisim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class DoiData
{
    /**
     * DOI ID
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $doi;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var integer
     *
     */
    public $timestamp;

    /**
     * DOI URL
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $resource;

    public function __construct()
    {
        $this->timestamp = time();
    }


}
