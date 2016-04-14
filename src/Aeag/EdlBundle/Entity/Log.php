<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="log")
 * @ORM\Entity
 */
class Log
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="log_ordre_seq", allocationSize=1, initialValue=1)
     */
    private $ordre;

    /**
     * @var string
     *
     * @ORM\Column(name="typ", type="string", length=12, nullable=false)
     */
    private $typ;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=1, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     */
    private $euCd;

    /**
     * @var string
     *
     * @ORM\Column(name="proposition_date", type="string", length=255, nullable=false)
     */
    private $propositionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=16, nullable=false)
     */
    private $code;



    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set typ
     *
     * @param string $typ
     *
     * @return log
     */
    public function setTyp($typ)
    {
        $this->typ = $typ;

        return $this;
    }

    /**
     * Get typ
     *
     * @return string
     */
    public function getTyp()
    {
        return $this->typ;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return log
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set euCd
     *
     * @param string $euCd
     *
     * @return log
     */
    public function setEuCd($euCd)
    {
        $this->euCd = $euCd;

        return $this;
    }

    /**
     * Get euCd
     *
     * @return string
     */
    public function getEuCd()
    {
        return $this->euCd;
    }

    /**
     * Set propositionDate
     *
     * @param string $propositionDate
     *
     * @return log
     */
    public function setPropositionDate($propositionDate)
    {
        $this->propositionDate = $propositionDate;

        return $this;
    }

    /**
     * Get propositionDate
     *
     * @return string
     */
    public function getPropositionDate()
    {
        return $this->propositionDate;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return log
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
