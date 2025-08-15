<?php
namespace Model;

class UserFlag {
    /** @var int */
    private $id;

    /** @var string */
    private $flagName;

    /** @var string */
    private $flagType;

    /** @var bool */
    private $isActive;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the UserFlag.
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
     * Get the value of flagName
     *
     * @return string
     */
    public function getFlagName() {
        return $this->flagName;
    }

    /**
     * Set the value of flagName
     *
     * @param string $flagName
     * @return self
     */
    public function setFlagName($flagName) {
        $this->flagName = $flagName;
        return $this;
    }

    /**
     * Get the value of flagType
     *
     * @return string
     */
    public function getFlagType() {
        return $this->flagType;
    }

    /**
     * Set the value of flagType
     *
     * @param string $flagType
     * @return self
     */
    public function setFlagType($flagType) {
        $this->flagType = $flagType;
        return $this;
    }

    /**
     * Get the value of isActive
     *
     * @return bool
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * Set the value of isActive
     *
     * @param bool $isActive
     * @return self
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
        return $this;
    }
}
