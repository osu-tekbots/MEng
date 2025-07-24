<?php
namespace Model;

class EvaluationRubric {
    /** @var int */
    private $id;

    /** @var string */
    private $fkEvaluationId;

    /** @var string */
    private $name;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the EvaluationRubric.
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
     * @return string
     */
    public function getFkEvaluationId() {
        return $this->fkEvaluationId;
    }

    /**
     * Set the value of fkEvaluationId
     *
     * @param string $fkEvaluationId
     * @return self
     */
    public function setFkEvaluationId($fkEvaluationId) {
        $this->fkEvaluationId = $fkEvaluationId;
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
}
