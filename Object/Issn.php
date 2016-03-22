<?php

namespace BulutYazilim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class Issn
{
    /** @JMS\XmlAttribute */
    public $mediaType = 'print';

    /** @JMS\XmlValue(cdata=false) */
    public $value;
}
