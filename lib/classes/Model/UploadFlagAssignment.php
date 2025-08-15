<?php
namespace Model;

class UploadFlagAssignment {
    /** @var int */
    private $id;

    /** @var string */
    private $fkUploadId;

    /** @var int */
    private $fkUploadFlagId;

    /** @var string */
    private $flagValue;

    /**
     * Constructor
     *
     * @param int|null $id Optional ID to initialize the UploadFlagAssignment.
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
     * Get the value of fkUploadFlagId
     *
     * @return int
     */
    public function getFkUploadFlagId() {
        return $this->fkUploadFlagId;
    }

    /**
     * Set the value of fkUploadFlagId
     *
     * @param int $fkUploadFlagId
     * @return self
     */
    public function setFkUploadFlagId($fkUploadFlagId) {
        $this->fkUploadFlagId = $fkUploadFlagId;
        return $this;
    }

    /**
     * Get the value of flagValue
     *
     * @return string
     */
    public function getFlagValue() {
        return $this->flagValue;
    }

    /**
     * Set the value of flagValue
     *
     * @param string $flagValue
     * @return self
     */
    public function setFlagValue($flagValue) {
        $this->flagValue = $flagValue;
        return $this;
    }
}
