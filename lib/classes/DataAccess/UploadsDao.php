<?php
namespace DataAccess;

use Model\Upload;
use Model\UploadFlag;

/**
 * Contains logic for database interactions with uploads data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class UploadsDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of an Upload Data Access Object.
     *
     * @param DatabaseConnection $connection the connection used to perform upload-related queries on the database
     * @param \Util\Logger $logger the logger to use for logging messages and errors associated with fetching upload data
     * @param boolean $echoOnError determines whether to echo an error whether or not a logger is present
     */
    public function __construct($connection, $logger = null, $echoOnError = false) {
        $this->logger = $logger;
        $this->conn = $connection;
        $this->echoOnError = $echoOnError;
    }

    /**
     * Gets an upload by id.
     *
     * @param string $id the ID of the upload to fetch
     * @return Upload|boolean an Upload object if the fetch succeeds, false otherwise
     */
    public function getUpload($id) {
        try {
            $sql = 'SELECT * FROM Uploads ';
            $sql .= 'WHERE id = :id ';
            $params = array(
                ':id' => $id
            );
            $result = $this->conn->query($sql, $params);

            return self::ExtractUploadFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch upload object: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Gets all uploads in Uploads table.
     *
     * @return Array|boolean Array of Upload objects if the fetch succeeds, false otherwise
     */
    public function getAllUploads() {
        try {
            $sql = 'SELECT * FROM Uploads ';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUploadFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch all upload objects: ' . $e->getMessage());
            return false;
        }
    }
  
     * Fetches a single document type with the given upload ID from the database.
     *
     * @param string $uploadId the ID of the document type to fetch
     * @return DocumentType|boolean the corresponding Document Type from the database if the fetch succeeds and the
     * document type exists, false otherwise
     */
    public function getDocumentType($uploadId) {
        try {
            $sql = "SELECT Upload_flags.* FROM Upload_flag_assignments ";
            $sql .= "INNER JOIN Upload_flags ON Upload_flag_assignments.fk_upload_flag_id = Upload_flags.id ";
            $sql .= "WHERE Upload_flag_assignments.fk_upload_id = :uploadId";
            $params = array(':uploadId' => $uploadId);
            $result = $this->conn->query($sql, $params);
            if (\count($result) == 0) {
                return false;
            }

            return self::ExtractUploadFlagFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single document type by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Gets an upload by User Id and Upload flag Id.
     *
     * @param string $userId the User Id of the upload to fetch
     * @param int $uploadFlagId the Upload Flag Id of the upload to fetch
     * @return Upload|boolean an Upload object if the fetch succeeds, false otherwise
     */
    public function getUserUploadByFlag($userId, $uploadFlagId) {
        try {
            $sql = 'SELECT Uploads.* FROM Uploads ';
            $sql .= 'RIGHT JOIN Upload_flag_assignments ON Uploads.id = Upload_flag_assignments.fk_upload_id ';
            $sql .= 'WHERE Uploads.fk_user_id = :userId ';
            $sql .= 'AND Upload_flag_assignments.fk_upload_flag_id = :uploadFlagId';
            $params = array(
                ':userId' => $userId,
                ':uploadFlagId' => $uploadFlagId
            );
            $result = $this->conn->query($sql, $params);

            if (count($result) == 0) {
                return false;
            } else {
                return self::ExtractUploadFromRow($result[0]);
            }
        } catch (\Exception $e) {
            $this->logError('Failed to fetch upload object: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Gets uploads that aren't assigned.
     *
     * @return Upload[]|boolean an array of Upload objects if the fetch succeeds, false otherwise
     */
    public function getAllUnassignedUploads() {
        try {
            $sql = 'SELECT * FROM Uploads ';
            $sql .= 'WHERE Uploads.id NOT IN ';
            $sql .= '(SELECT fk_upload_id FROM Evaluations ';
            $sql .= 'WHERE fk_upload_id = Uploads.id)';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUploadFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch upload objects: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches all the doc_type upload flags from the database.
     * 
     * If an error occurs during the fetch, the function will return `false`.
     *
     * @return UploadFlag[]|boolean an array of Upload Flag objects if the fetch succeeds, false otherwise
     */
    public function getAllDocumentTypes() {
        try {
            $sql = 'SELECT * FROM Upload_flags ';
            $sql .= 'WHERE flag_type = "doc_type"';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUploadFlagFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch document types: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new upload object to the database.
     *
     * @param \Model\Upload $upload the upload to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewUpload($upload) {
        try {

            $sql = 'INSERT INTO Uploads ';
            $sql .= '(id, fk_user_id, file_path, file_name, date_uploaded) ';
            $sql .= 'VALUES (:id,:fk_user_id,:file_path,:file_name,:date_uploaded)';
            $params = array(
                ':id' => $upload->getId(),
                ':fk_user_id' => $upload->getFkUserId(),
                ':file_path' => $upload->getFilePath(),
                ':file_name' => $upload->getFileName(),
                ':date_uploaded' => $upload->getDateUploaded()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new upload object: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new upload flag assignment object to the database.
     *
     * @param string $uploadId the ID of the upload
     * @param int $uploadFlagId the ID of the upload flag
     * @param string||null $value value of flag, null if not provided
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function assignUploadFlag($uploadId, $uploadFlagId, $flagValue = null) {
        try {

            $sql = 'INSERT INTO Upload_flag_assignments ';
            $sql .= '(fk_upload_id, fk_upload_flag_id, flag_value) ';
            $sql .= 'VALUES (:fk_upload_id,:fk_upload_flag_id,:flag_value)';
            $params = array(
                ':fk_upload_id' => $uploadId,
                ':fk_upload_flag_id' => $uploadFlagId,
                ':flag_value' => $flagValue
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to assign upload flag: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Updates an existing upload in the database. 
     * 
     * This function only updates database information on an upload
     *
     * @param \Model\Upload $upload the upload to update
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function updateUpload($upload) {
        try {
            $sql = 'UPDATE Uploads SET ';
            $sql .= 'fk_user_id = :fk_user_id, ';
            $sql .= 'file_path = :file_path, ';
            $sql .= 'file_name = :file_name, ';
            $sql .= 'date_uploaded = :date_uploaded ';
            $sql .= 'WHERE id = :id';
            $params = array(
                ':fk_user_id' => $upload->getFkUserId(),
                ':file_path' => $upload->getFilePath(),
                ':file_name' => $upload->getFileName(),
                ':date_uploaded' => $upload->getDateUploaded(),
                ':id' => $upload->getId()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update upload: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Deletes an upload by id.
     *
     * @param string $id the ID of the upload to delete
     * @return boolean true if the deletion succeeds, false otherwise
     */
    public function deleteUpload($id) {
        try {
            $sql = 'DELETE FROM Uploads ';
            $sql .= 'WHERE id = :id ';
            $params = array(
                ':id' => $id
            );
            $result = $this->conn->query($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to fetch upload object: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Upload object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing upload information
     * @return \Model\Upload
     */
    public static function ExtractUploadFromRow($row) {
		$upload = new Upload($row['id']);
        $upload->setFkUserId($row['fk_user_id'])
            ->setFilePath($row['file_path'])
            ->setFileName($row['file_name'])
            ->setDateUploaded($row['date_uploaded']);
        
        return $upload;
    }

    /**
     * Creates a new Upload Flag object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing upload flag information
     * @return \Model\UploadFlag
     */
    public static function ExtractUploadFlagFromRow($row) {
		$uploadflag = new UploadFlag($row['id']);
        $uploadflag->setFlagName($row['flag_name'])
            ->setFlagType($row['flag_type'])
            ->setIsActive($row['is_active']);
        
        return $uploadflag;
    }

    /**
     * Logs an error if a logger was provided to the class when it was constructed.
     * 
     * Essentially a wrapper around the error logging so we don't cause the equivalent of a null pointer exception.
     *
     * @param string $message the message to log.
     * @return void
     */
    private function logError($message) {
        if ($this->logger != null) {
            $this->logger->error($message);
        }
        if ($this->echoOnError) {
            echo "$message\n";
        }
    }
}
