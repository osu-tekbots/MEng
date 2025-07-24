<?php
namespace Model;

class RubricTemplateItem {
    /** @var int */
    private $id;

    /** @var int */
    private $fkRubricTemplateId;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $answerType;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID for initializing the rubric template item.
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
     * Get the value of fkRubricTemplateId
     *
     * @return int
     */
    public function getFkRubricTemplateId() {
        return $this->fkRubricTemplateId;
    }

    /**
     * Set the value of fkRubricTemplateId
     *
     * @param int $fkRubricTemplateId
     * @return self
     */
    public function setFkRubricTemplateId($fkRubricTemplateId) {
        $this->fkRubricTemplateId = $fkRubricTemplateId;
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
     * Get the value of answerType
     *
     * @return string
     */
    public function getAnswerType() {
        return $this->answerType;
    }

    /**
     * Set the value of answerType
     *
     * @param string $answerType One of the valid enum values for the rubric item type
     * @return self
     */
    public function setAnswerType($answerType) {
        $this->answerType = $answerType;
        return $this;
    }
}