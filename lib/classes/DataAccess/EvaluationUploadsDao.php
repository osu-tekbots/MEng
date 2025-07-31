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
     * Adds a new eveluation object to the database.
     *
     * @param \Model\EvaluationUpload $evaluationUpload the upload to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewEvaluationUpload($evaluationUpload) {
        try {

            $sql = 'INSERT INTO Evaluation_uploads ';
            $sql .= '(id, fk_user_id, fk_document_type, file_path, file_name ';
            $sql .= 'VALUES (:id,:fk_user_id,:fk_document_type,:file_path,:file_name)';
            $params = array(
                ':id' => $user->getId(),
                ':fk_user_id' => $user->getFkUserId(),
                ':fk_document_type' => $user->getFkDocumentType(),
                ':file_path' => $user->getFilePath(),
                ':file_name' => $user->getFileName()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new upload evaluation object: ' . $e->getMessage());

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
            ->setFileName($row['file_name']);
        
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
