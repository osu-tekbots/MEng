<?php
namespace Model;

class UserFlagAssignment {
    /** @var int */
    private $id;

    /** @var string */
    private $fkUserId;

    /** @var int */
    private $fkUserFlagId;

    /** @var string */
    private $flagValue;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the UserFlagAssignment.
     */
    public function __construct($id = null) {
        $this->id = $id;
    }

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     * @return self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of fkUserId
     *
     * @return string
     */
    public function getFkUserId() {
        return $this->fkUserId;
    }

    /**
     * Set the value of fkUserId
     *
     * @param string $fkUserId
     * @return self
     */
    public function setFkUserId($fkUserId) {
        $this->fkUserId = $fkUserId;
        return $this;
    }

    /**
     * Get the value of fkUserFlagId
     *
     * @return int
     */
    public function getFkUserFlagId() {
        return $this->fkUserFlagId;
    }

    /**
     * Set the value of fkUserFlagId
     *
     * @param int $fkUserFlagId
     * @return self
     */
    public function setFkUserFlagId($fkUserFlagId) {
        $this->fkUserFlagId = $fkUserFlagId;
        return $this;
    }

    /**
     * Get the value of flagValue
     *
     * @return string
     */
    public function getFlagValue() {
        return $this->flagValue;
    }

    /**
     * Set the value of flagValue
     *
     * @param string $flagValue
     * @return self
     */
    public function setFlagValue($flagValue) {
        $this->flagValue = $flagValue;
        return $this;
    }
}
