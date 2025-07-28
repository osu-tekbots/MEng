<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a user
 */
class User {

    /** @var string */
    private $id;

    /** @var string */
    private $uuid;

    /** @var string */
    private $osuId;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $onid;

    /** @var string */
    private $email;

    /** @var \DateTime|null */
    private $lastLogin;

    /**
     * Constructs a new instance of a user.
     *
     * @param string|null $id If null, a new ID will be generated.
     */
    public function __construct($id = null) {
        if ($id === null) {
            $this->setId(IdGenerator::generateSecureUniqueId(8));
        } else {
            $this->setId($id);
        }
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of uuid
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * Set the value of uuid
     *
     * @return  self
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * Get the value of osuId
     */
    public function getOsuId() {
        return $this->osuId;
    }

    /**
     * Set the value of osuId
     *
     * @return  self
     */
    public function setOsuId($osuId) {
        $this->osuId = $osuId;
        return $this;
    }

    /**
     * Get the value of firstName
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @return  self
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @return  self
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Returns the combination of the user's first and last name.
     *
     * @return string
     */
    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Get the value of onid
     */
    public function getOnid() {
        return $this->onid;
    }

    /**
     * Set the value of onid
     *
     * @return  self
     */
    public function setOnid($onid) {
        $this->onid = $onid;
        return $this;
    }

    /**
     * Get the value of onidEmail
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the value of onidEmail
     *
     * @return  self
     */
    public function setEmail($email) {
        $this->onidEmail = $email;
        return $this;
    }

    /**
     * Get the value of lastLogin
     */
    public function getLastLogin() {
        return $this->lastLogin;
    }

    /**
     * Set the value of lastLogin
     *
     * @return  self
     */
    public function setLastLogin($lastLogin) {
        $this->lastLogin = $lastLogin;
        return $this;
    }
}
