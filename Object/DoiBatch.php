<?php

namespace BulutYazilim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("doi_batch")
 * @JMS\XmlNamespace(uri="http://www.crossref.org/schema/4.3.7")
 * @JMS\XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 *
 */
class DoiBatch
{
    /** @JMS\XmlAttributeMap */
    protected $id = array(
        'version' => '4.3.6',
        'xsi:schemaLocation' => 'http://www.crossref.org/schema/4.3.7 http://doi.crossref.org/schemas/crossref4.3.7.xsd',
    );

    /** @var Head */
    public $head;

    /** @var Body */
    public $body;

    public function __construct()
    {
        $this->head = new Head();
        $this->body = new Body();
    }

}
