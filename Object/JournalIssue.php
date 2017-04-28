<?php

namespace Vipa\DoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class JournalIssue
{
    /** @var  PublicationDate */
    public $publicationDate;

    /** @var  JournalVolume */
    public $journalVolume;

    /**
     * @var integer
     * @JMS\XmlElement(cdata=false)
     */
    public $issue;

    /** @var DoiData */
    public $doiData;

    public function __construct()
    {
        $this->journalVolume = new JournalVolume();
        $this->publicationDate = new PublicationDate();
    }
}
