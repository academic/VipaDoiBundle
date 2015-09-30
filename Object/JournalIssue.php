<?php

namespace OkulBilisim\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class JournalIssue
{
    /** @var  PublicationDate */
    public $publicationDate;

    /** @var  JournalVolume */
    public $journalVolume;

    /** @var integer */
    public $issue;

    /** @var DoiData */
    public $doiData;

    public function __construct()
    {
        $this->journalVolume = new JournalVolume();
        $this->publicationDate = new PublicationDate();
    }
}
