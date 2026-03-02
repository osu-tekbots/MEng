<?php
namespace Model;

class RubricItem {
    /** @var int */
    private $id;

    /** @var int */
    private $fkRubricId;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var bool */
    private $commentRequired;

	/**
     * @var array RubricItemOption[]
	 * Left as public for ease of use.
     */
    public $items = [];

    /**
     * Constructor
     *
     * @param int|null $id Optional ID for initializing the rubric  item.
     */
    public function __construct($id = null) {
        if ($id !== null) {
            $this->id = $id;
        }
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
     * Get the value of fkRubricId
     *
     * @return int
     */
    public function getFkRubricId() {
        return $this->fkRubricId;
    }

    /**
     * Set the value of fkRubricId
     *
     * @param int $fkRubricId
     * @return self
     */
    public function setFkRubricId($fkRubricId) {
        $this->fkRubricId = $fkRubricId;
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
     * Get the value of description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the value of commentRequired
     *
     * @return bool
     */
    public function getCommentRequired() {
        return $this->commentRequired;
    }

    /**
     * Set the value of commentRequired
     *
     * @param bool $commentRequired
     * @return self
     */
    public function setCommentRequired($commentRequired) {
        $this->commentRequired = $commentRequired;
        return $this;
    }
	
	/**
     * Get the array of items
     *
     * @return array RubricItem[]
     */
    public function getItemOptions() {
        return $this->items;
    }

    /**
     * Set the array of items
     *
     * @param array $items
     * @return self
     */
    public function setItemOptions($items) {
        $this->items = $items;
        return $this;
    }

    /**
     * Add an item to the items array
     *
     * @param RubricItem $item
     * @return self
     */
    public function addItemOptions($item) {
        $this->items[] = $item;
        return $this;
    }
}