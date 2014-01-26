<?php

namespace EB\FacebookBundle\Model;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        $this->count = 1;
        $this->offersEmail = 0;
        $this->offersSms = 0;
        $this->validated = false;
        $this->ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : NULL;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=15, nullable=true)
     */
    protected $ip;

    /**
     * @var $firstname
     *
     * @Assert\NotBlank(message = "eb_facebook.required")
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    protected $firstname;

    /**
     * @var $lastname
     *
     * @Assert\NotBlank(message = "eb_facebook.required")
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    protected $lastname;

    /**
     * @var $facebookId
     *
     * @ORM\Column(name="facebookId", type="string", length=255)
     */
    protected $facebookId;

    /**
     * @var $birthday
     *
     * @Assert\NotBlank(message = "eb_facebook.required")
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @var $city
     *
     * @Assert\NotBlank(message = "eb_facebook.required")
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @var $address
     *
     * @Assert\NotBlank(message = "eb_facebook.required")
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @var $zipcode
     *
     * @Assert\NotBlank(message = "eb_facebook.required")
     * @Assert\Regex(pattern="/^[0-9]{5}$/", message = "eb_facebook.invalid")
     * @ORM\Column(name="zipcode", type="string", length=5, nullable=true)
     */
    protected $zipcode_fr;

    /**
     * @var $phone
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * @var $offersEmail
     *
     * @ORM\Column(name="offers_email", type="boolean")
     */
    protected $offersEmail;

    /**
     * @var $offersSms
     *
     * @ORM\Column(name="offers_sms", type="boolean")
     */
    protected $offersSms;

    /**
     * @var $validated
     *
     * @ORM\Column(name="validated", type="boolean")
     */
    protected $validated;

    /**
     * @var count
     *
     * @ORM\Column(name="count", type="integer", options={"default" = 1})
     */
    protected $count;

    protected $invitation;

    /**
     * @param Array
     */
    public function setFBData($fbdata)
    {
        if (isset($fbdata['id'])) {
            $this->setFacebookId($fbdata['id']);
            $this->addRole('ROLE_FACEBOOK');
        }
        if (isset($fbdata['first_name'])) {
            $this->setFirstname($fbdata['first_name']);
        }
        if (isset($fbdata['last_name'])) {
            $this->setLastname($fbdata['last_name']);
        }
        if (isset($fbdata['location']['name'])) {
            $split = explode(',', $fbdata['location']['name']);
            $this->setCity(trim($split[0]));
        }
        if (isset($fbdata['birthday'])) {
            $date = new \DateTime($fbdata['birthday']);
            $this->setBirthday($date);
        }
        if (isset($fbdata['email'])) {
            $this->setEmail($fbdata['email']);
        }
        if (isset($fbdata['username']))
            $this->setUsername($fbdata['username']);
        else
            $this->setUsername($fbdata['id']);
    }

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
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set offers
     *
     * @param boolean $offers
     * @return User
     */
    public function setOffers($offers)
    {
        $this->offers = $offers;

        return $this;
    }

    /**
     * Get offers
     *
     * @return boolean
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return User
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set validated
     *
     * @param boolean $validated
     * @return User
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get validated
     *
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return User
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set offersEmail
     *
     * @param boolean $offersEmail
     * @return User
     */
    public function setOffersEmail($offersEmail)
    {
        $this->offersEmail = $offersEmail;

        return $this;
    }

    /**
     * Get offersEmail
     *
     * @return boolean
     */
    public function getOffersEmail()
    {
        return $this->offersEmail;
    }

    /**
     * Set offersSms
     *
     * @param boolean $offersSms
     * @return User
     */
    public function setOffersSms($offersSms)
    {
        $this->offersSms = $offersSms;

        return $this;
    }

    /**
     * Get offersSms
     *
     * @return boolean
     */
    public function getOffersSms()
    {
        return $this->offersSms;
    }

    /**
     * Set zipcode_fr
     *
     * @param string $zipcodeFr
     * @return User
     */
    public function setZipcodeFr($zipcodeFr)
    {
        $this->zipcode_fr = $zipcodeFr;

        return $this;
    }

    /**
     * Get zipcode_fr
     *
     * @return string
     */
    public function getZipcodeFr()
    {
        return $this->zipcode_fr;
    }

    /**
     * Add invitation
     *
     * @param \EB\FacebookBundle\Entity\Invitation $invitation
     * @return User
     */
    public function addInvitation(\EB\FacebookBundle\Entity\Invitation $invitation)
    {
        $this->invitation[] = $invitation;

        return $this;
    }

    /**
     * Remove invitation
     *
     * @param \EB\FacebookBundle\Entity\Invitation $invitation
     */
    public function removeInvitation(\EB\FacebookBundle\Entity\Invitation $invitation)
    {
        $this->invitation->removeElement($invitation);
    }

    /**
     * Get invitation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvitation()
    {
        return $this->invitation;
    }
}