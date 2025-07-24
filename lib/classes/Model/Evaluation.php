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

    /** @var int */
    private $fkDocumentType;

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
     * Get the value of fkDocumentType
     *
     * @return int
     */
    public function getFkDocumentType() {
        return $this->fkDocumentType;
    }

    /**
     * Set the value of fkDocumentType
     *
     * @param int $fkDocumentType
     * @return self
     */
    public function setFkDocumentType($fkDocumentType) {
        $this->fkDocumentType = $fkDocumentType;
        return $this;
    }
}