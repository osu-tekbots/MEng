<?php
namespace Model;

class Rubric {
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $lastUsed;

    /** @var string */
    private $lastModified;

    /**
     * @var array RubricItem[]
     */
    public $items = [];

    /**
     * Constructor
     *
     * @param int|null $id Optional ID for initializing the rubric.
     */
    public function __construct($id = null) {
        if ($id !== null) {
            $this->id = $id;
        }
        $this->items = [];
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
     * Get the value of lastUsed
     *
     * @return string
     */
    public function getLastUsed() {
        return $this->lastUsed;
    }

    /**
     * Set the value of lastUsed
     *
     * @param string $lastUsed
     * @return self
     */
    public function setLastUsed($lastUsed) {
        $this->lastUsed = $lastUsed;
        return $this;
    }

    /**
     * Get the value of lastModified
     *
     * @return string
     */
    public function getLastModified() {
        return $this->lastModified;
    }

    /**
     * Set the value of lastModified
     *
     * @param string $lastModified
     * @return self
     */
    public function setLastModified($lastModified) {
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * Get the array of items
     *
     * @return array RubricItem[]
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Set the array of items
     *
     * @param array $items
     * @return self
     */
    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    /**
     * Add an item to the items array
     *
     * @param RubricItem $item
     * @return self
     */
    public function addItem($item) {
        $this->items[] = $item;
        return $this;
    }
}
