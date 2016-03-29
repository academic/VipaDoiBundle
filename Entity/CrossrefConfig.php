<?php

namespace BulutYazilim\OjsDoiBundle\Entity;

use Ojs\CoreBundle\Entity\BlameableTrait;
use Ojs\CoreBundle\Entity\TimestampableTrait;
use Ojs\JournalBundle\Entity\Journal;

class CrossrefConfig
{
    use BlameableTrait;
    use TimestampableTrait;

    /** @var integer */
    protected $id;

    /** @var  Journal */
    protected $journal;
    /**
     * crossref api username
     * @var string
     */
    protected $username;
    /**
     * crossref api password
     * @var string
     */
    protected $password;
    /**
     * DOI depositor name
     * @var string
     */
    protected $fullName;
    /**
     * DOI depositor email
     * @var string
     */
    protected $email;

    /** @var string */
    protected $prefix;

    /** @var string */
    protected $suffix = '%j.v%vi%i.%a';

    /**
     * @return Journal
     */
    public function getJournal()
    {
        return $this->journal;
    }

    /**
     * @param Journal $journal
     * @return CrossrefConfig
     */
    public function setJournal($journal)
    {
        $this->journal = $journal;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return CrossrefConfig
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return CrossrefConfig
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return CrossrefConfig
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return CrossrefConfig
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return CrossrefConfig
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     * @return CrossrefConfig
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return !empty($this->username) && !empty($this->password);
    }
}
