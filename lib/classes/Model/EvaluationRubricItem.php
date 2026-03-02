<?php
namespace Model;

class EvaluationRubricItem {
    /** @var int */
    private $id;

    /** @var int */
    private $fkEvaluationId;

    /** @var RubricItem */
    private $rubricItem;

    /** @var RubricItemOption */
    private $rubricItemOption;

    /** @var string */
    private $comments;

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
     * Get the value of fkEvaluationId
     *
     * @return int
     */
    public function getFkEvaluationId() {
        return $this->fkEvaluationId;
    }

    /**
     * Set the value of fkEvaluationId
     *
     * @param int $fkEvaluationId
     * @return self
     */
    public function setFkEvaluationId($fkEvaluationId) {
        $this->fkEvaluationId = $fkEvaluationId;
        return $this;
    }

    /**
     * Get the value of rubricItem
     *
     * @return RubricItem
     */
    public function getRubricItem() {
        return $this->rubricItem;
    }

    /**
     * Set the value of rubricItem
     *
     * @param RubricItem $rubricItem
     * @return self
     */
    public function setRubricItem($rubricItem) {
        $this->rubricItem = $rubricItem;
        return $this;
    }

    /**
     * Get the value of rubricItemOption
     *
     * @return RubricItemOption
     */
    public function getRubricItemOption() {
        return $this->rubricItemOption;
    }

    /**
     * Set the value of rubricItemOption
     *
     * @param RubricItemOption $rubricItemOption
     * @return self
     */
    public function setRubricItemOption($rubricItemOption) {
        $this->rubricItemOption = $rubricItemOption;
        return $this;
    }

    /**
     * Get the value of comments
     *
     * @return string
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * Set the value of comments
     *
     * @param string $comments
     * @return self
     */
    public function setComments($comments) {
        $this->comments = $comments;
        return $this;
    }
}