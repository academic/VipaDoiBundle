<?php

namespace OkulBilisim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;


class PersonName
{
    /**
     *
     * @JMS\XmlAttribute
     * @var string
     *
     */
    public $contributorRole = "author";

    /**
     *
     * @JMS\XmlAttribute
     * @var string
     *
     */
    public $sequence = 'additional';

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $givenName;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $surname;
}
