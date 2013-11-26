<?php

namespace EB\FacebookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invitation
 *
 * @ORM\Table(name="eb_facebook_invitation")
 * @ORM\Entity()
 */
class Invitation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="friendId", type="bigint")
     */
    protected $friendId;
    
    /**
     * @ORM\ManyToOne(targetEntity="EB\FacebookBundle\Entity\User", inversedBy="invitation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sponsor;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set friendId
     *
     * @param integer $friendId
     * @return Invitation
     */
    public function setFriendId($friendId)
    {
        $this->friendId = $friendId;
    
        return $this;
    }

    /**
     * Get friendId
     *
     * @return integer 
     */
    public function getFriendId()
    {
        return $this->friendId;
    }

    /**
     * Set sponsor
     *
     * @param \EB\FacebookBundle\Entity\User $sponsor
     * @return Invitation
     */
    public function setSponsor(\EB\FacebookBundle\Entity\User $sponsor)
    {
        $this->sponsor = $sponsor;
    
        return $this;
    }

    /**
     * Get sponsor
     *
     * @return \EB\FacebookBundle\Entity\User 
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }
}