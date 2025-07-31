<?php
namespace DataAccess;

use Model\EvaluationUpload;

/**
 * Contains logic for database interactions with uploads data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class EvaluationUploadsDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a Evaluation Upload Data Access Object.
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
     * Gets an evaluation upload by id.
     *
     * @param string $id the ID of the evaluation upload to fetch
     * @return EvaluationUpload|boolean an Evaluation Upload object if the fetch succeeds, false otherwise
     */
    public function getAllUnassignedEvaluationUploads($id) {
        try {
            $sql = 'SELECT * FROM Evaluation_uploads ';
            $sql .= 'WHERE id = :id';
            $params = array(
                ':id' => $id
            );
            $result = $this->conn->query($sql, $params);

            return self::ExtractEvaluationUploadFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch evaluation upload object: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Gets evaluation uploads that aren't assigned.
     *
     * @return EvaluationUpload[]|boolean an array of Evaluation Upload objects if the fetch succeeds, false otherwise
     */
    public function getAllUnassignedEvaluationUploads() {
        try {
            $sql = 'SELECT * FROM Evaluation_uploads ';
            $sql .= 'WHERE Evaluation_uploads.id NOT IN ';
            $sql .= '(SELECT fk_evaluation_upload FROM Evaluations ';
            $sql .= 'WHERE fk_evaluation_upload = Evaluation_uploads.id)';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractEvaluationUploadFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch evaluation upload objects: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new evaluation upload object to the database.
     *
     * @param \Model\EvaluationUpload $evaluationUpload the upload to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewEvaluationUpload($evaluationUpload) {
        try {

            $sql = 'INSERT INTO Evaluation_uploads ';
            $sql .= '(id, fk_user_id, fk_document_type, file_path, file_name, date_uploaded ';
            $sql .= 'VALUES (:id,:fk_user_id,:fk_document_type,:file_path,:file_name, date_uploaded)';
            $params = array(
                ':id' => $evaluationUpload->getId(),
                ':fk_user_id' => $evaluationUpload->getFkUserId(),
                ':fk_document_type' => $evaluationUpload->getFkDocumentType(),
                ':file_path' => $evaluationUpload->getFilePath(),
                ':file_name' => $evaluationUpload->getFileName(),
                ':date_uploaded' => $evaluationUpload->getDateUploaded()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new evaluation upload object: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Evaluation Upload object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing upload information
     * @return \Model\EvaluationUpload
     */
    public static function ExtractEvaluationUploadFromRow($row) {
		$evaluationUpload = new EvaluationUpload($row['id']);
        $evaluationUpload->setFkUserId($row['fk_user_id'])
            ->setFkDocumentType($row['fk_document_type'])
            ->setFilePath($row['file_path'])
            ->setFileName($row['file_name'])
            ->setDateUploaded($row['date_uploaded']);
        
        return $evaluationUpload;
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
