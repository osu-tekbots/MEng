<?php
namespace Model;

class RubricItemOption {
    /** @var int */
    private $id;

    /** @var string */
    private $fkRubricItemId;

    /** @var string */
    private $value;

    /** @var string */
    private $title;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID for initializing the rubric.
     */
    public function __construct($id = null) {
        if ($id !== null) {
            $this->id = $id;
        }
 //Removed 2/16/26       $this->items = [];
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
     * Get the value of fkRubricItemId
     *
     * @return string
     */
    public function getFkRubricItemId() {
        return $this->fkRubricItemId;
    }

    /**
     * Set the value of fkRubricItemId
     *
     * @param string $fkRubricItemId
     * @return self
     */
    public function setFkRubricItemId($fkRubricItemId) {
        $this->fkRubricItemId = $fkRubricItemId;
        return $this;
    }

    /**
     * Get the value of value
     *
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @param string $value
     * @return self
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the value of title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
}
