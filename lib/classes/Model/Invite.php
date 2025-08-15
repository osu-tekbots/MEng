<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing an Invite
 */
class Invite {

    /** @var string */
    private $id;

    /** @var string */
    private $email;

    /**
     * Constructs a new instance of an invite.
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
}
