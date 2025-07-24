<?php
namespace Model;

class RubricTemplate {
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID for initializing the rubric template.
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
}
