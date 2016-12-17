<?php

namespace Ojs\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class Pages
{
    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var integer
     *
     */
    public $firstPage;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var integer
     *
     */
    public $lastPage;
}
