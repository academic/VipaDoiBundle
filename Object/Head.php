<?php

namespace Ojs\DoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class Head
{
    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $doiBatchId;

    public $timestamp;

    /** @var Depositor */
    public $depositor;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $registrant;

    public function __construct()
    {
        $this->timestamp = time();
        $this->depositor = new Depositor();
    }
}
