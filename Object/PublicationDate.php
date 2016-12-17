<?php

namespace Ojs\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class PublicationDate
{
    /** @JMS\XmlAttribute */
    public $mediaType = "online";

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $month;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $day;

    /**
     *
     * @JMS\XmlElement(cdata=false)
     * @var string
     *
     */
    public $year;

    public function setDate(\DateTime $dateTime = null) {
        if(!$dateTime) {
            return;
        }
        $this->day = $dateTime->format('d');
        $this->month = $dateTime->format('m');
        $this->year = $dateTime->format('Y');
    }
}
