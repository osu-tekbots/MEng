<?php
namespace Model;

class EvaluationRubricItem {
    /** @var int */
    private $id;

    /** @var int */
    private $fkEvaluationRubricId;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $answerType;

    /** @var string */
    private $value;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the EvaluationRubricItem.
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
     * Get the value of fkEvaluationRubricId
     *
     * @return int
     */
    public function getFkEvaluationRubricId() {
        return $this->fkEvaluationRubricId;
    }

    /**
     * Set the value of fkEvaluationRubricId
     *
     * @param int $fkEvaluationRubricId
     * @return self
     */
    public function setFkEvaluationRubricId($fkEvaluationRubricId) {
        $this->fkEvaluationRubricId = $fkEvaluationRubricId;
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
     * @param string $answerType
     * @return self
     */
    public function setAnswerType($answerType) {
        $this->answerType = $answerType;
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
}