<?php
namespace Model;

use Util\IdGenerator;

class EvaluationUpload {
    /** @var string */
    private $id;

    /** @var string */
    private $fkUserId;

    /** @var int */
    private $fkDocumentType;

    /** @var string */
    private $filePath;

    /** @var string */
    private $fileName;

    /** @var string */
    private $dateUploaded;

    /**
     * Constructor
     *
     * @param string|null $id Optional ID to initialize the EvaluationUpload.
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
     * Get the value of fkUserId
     *
     * @return string
     */
    public function getFkUserId() {
        return $this->fkUserId;
    }

    /**
     * Set the value of fkUserId
     *
     * @param string $fkUserId
     * @return self
     */
    public function setFkUserId($fkUserId) {
        $this->fkUserId = $fkUserId;
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

    /**
     * Get the value of filePath
     *
     * @return string
     */
    public function getFilePath() {
        return $this->filePath;
    }

    /**
     * Set the value of filePath
     *
     * @param string $filePath
     * @return self
     */
    public function setFilePath($filePath) {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Get the value of fileName
     *
     * @return string
     */
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * Set the value of fileName
     *
     * @param string $fileName
     * @return self
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get the value of dateUploaded
     *
     * @return string
     */
    public function getDateUploaded() {
        return $this->dateUploaded;
    }

    /**
     * Set the value of dateUploaded
     *
     * @param string $dateUploaded
     * @return self
     */
    public function setDateUploaded($dateUploaded) {
        $this->dateUploaded = $dateUploaded;
        return $this;
    }
}
