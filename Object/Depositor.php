<?php

namespace OkulBilisim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class Depositor
{
    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $name;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $emailAddress;

}
