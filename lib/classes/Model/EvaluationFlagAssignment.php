<?php
namespace Model;

class EvaluationFlagAssignment {
    /** @var int */
    private $id;

    /** @var string */
    private $fkEvaluationId;

    /** @var int */
    private $fkEvaluationFlagId;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the EvaluationFlagAssignment.
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
     * Get the value of fkEvaluationFlagId
     *
     * @return int
     */
    public function getFkEvaluationFlagId() {
        return $this->fkEvaluationFlagId;
    }

    /**
     * Set the value of fkEvaluationFlagId
     *
     * @param int $fkEvaluationFlagId
     * @return self
     */
    public function setFkEvaluationFlagId($fkEvaluationFlagId) {
        $this->fkEvaluationFlagId = $fkEvaluationFlagId;
        return $this;
    }
}