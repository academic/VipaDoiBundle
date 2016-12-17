<?php

namespace Ojs\OjsDoiBundle\Object;

use JMS\Serializer\Annotation as JMS;

class Journal
{
    /** @var  JournalMetadata */
    public $journalMetadata;

    /** @var  JournalIssue */
    public $journalIssue;

    /** @var  JournalArticle */
    public $journalArticle;

    public function __construct() {
        $this->journalMetadata = new JournalMetadata();
        $this->journalIssue = new JournalIssue();
        $this->journalArticle = new JournalArticle();
    }
}
