<?php
namespace Model;

class UserFlag {
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var int */
    private $arrangement;

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
     * Get the value of name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     * @return self
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the value of type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param string $type
     * @return self
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the value of arrangement
     *
     * @return string
     */
    public function getArrangement() {
        return $this->arrangement;
    }

    /**
     * Set the value of arrangement
     *
     * @param string $arrangement
     * @return self
     */
    public function setArrangement($arrangement) {
        $this->arrangement = $arrangement;
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
