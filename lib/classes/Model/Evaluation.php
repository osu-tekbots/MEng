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
    private $fkUploadId;

    /** @var string */
    private $dateCreated;

    /** @var array  */
    private $evaluationFlags = [];
    //Array of EvaluationFlag objects associated with this evaluation

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
     * Get the value of fkUploadId
     *
     * @return string
     */
    public function getFkUploadId() {
        return $this->fkUploadId;
    }

    /**
     * Set the value of fkUploadId
     *
     * @param string $fkUploadId
     * @return self
     */
    public function setFkUploadId($fkUploadId) {
        $this->fkUploadId = $fkUploadId;
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

    /**
     * Get the value of evaluationFlags
     *
     * @return array of EvaluationFlag
     */
    public function getEvaluationFlags() {
        return $this->evaluationFlags;
    }

    /**
     * Returns the highest status EvaluationFlag associated with this Evaluation
     * Returns null if no status flag is found
     *
     * @return EvaluationFlag|null
     */
    public function getHighestStatusFlag() {
        $maxFlag = null;
        foreach ($this->evaluationFlags as $flag) {
            if ($flag->getType() === 'Status') {
                if( $maxFlag === null || $flag->getArrangement() > $maxFlag->getArrangement() ) {
                    $maxFlag = $flag;
                }
            }
        }
        return $maxFlag;
    }
    /**
     * Set the value of evaluationFlags
     *
     * @param array of EvaluationFlag $evaluationFlags
     * @return self
     */
    public function setEvaluationFlags($evaluationFlags) {
        $this->evaluationFlags = $evaluationFlags;
        return $this;
    }

    /**
     * Add an EvaluationFlag to the evaluationFlags array
     *
     * @param EvaluationFlag $evaluationFlag
     * @return self
     */
    public function addEvaluationFlag($evaluationFlag) {
        $this->evaluationFlags[] = $evaluationFlag;
        return $this;
    }
}