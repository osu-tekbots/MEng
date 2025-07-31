<?php
namespace Model;

use Util\IdGenerator;

class Evaluation {
    /** @var string */
    private $id;

    /** @var string */
    private $fkStudentId;

    /** @var string */
    private $fkReviewerId;

    /** @var string */
    private $fkEvaluationUpload;

    /** @var string */
    private $dateCreated;

    /**
     * Constructor
     *
     * @param string|null $id Optional ID to initialize the Evaluation.
     */
    public function __construct($id = null) {
        if ($id === null) {
            $this->id = IdGenerator::generateSecureUniqueId(8);
        } else {
            $this->id = $id;
        }
    }

    /**
     * Get the value of id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param string $id
     * @return self
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of fkStudentId
     *
     * @return string
     */
    public function getFkStudentId() {
        return $this->fkStudentId;
    }

    /**
     * Set the value of fkStudentId
     *
     * @param string $fkStudentId
     * @return self
     */
    public function setFkStudentId($fkStudentId) {
        $this->fkStudentId = $fkStudentId;
        return $this;
    }

    /**
     * Get the value of fkReviewerId
     *
     * @return string
     */
    public function getFkReviewerId() {
        return $this->fkReviewerId;
    }

    /**
     * Set the value of fkReviewerId
     *
     * @param string $fkReviewerId
     * @return self
     */
    public function setFkReviewerId($fkReviewerId) {
        $this->fkReviewerId = $fkReviewerId;
        return $this;
    }

    /**
     * Get the value of fkEvaluationUpload
     *
     * @return string
     */
    public function getFkEvaluationUpload() {
        return $this->fkEvaluationUpload;
    }

    /**
     * Set the value of fkEvaluationUpload
     *
     * @param string $fkEvaluationUpload
     * @return self
     */
    public function setFkEvaluationUpload($fkEvaluationUpload) {
        $this->fkEvaluationUpload = $fkEvaluationUpload;
        return $this;
    }

    /**
     * Get the value of dateCreated
     *
     * @return string
     */
    public function getDateCreated() {
        return $this->dateCreated;
    }

    /**
     * Set the value of dateCreated
     *
     * @param int $dateCreated
     * @return self
     */
    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
        return $this;
    }
}