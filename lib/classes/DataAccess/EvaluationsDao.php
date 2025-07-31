<?php
namespace DataAccess;

use Model\Evaluation;

/**
 * Contains logic for database interactions with evaluations data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class EvaluationsDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a Evaluation Data Access Object.
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
     * Gets an evaluation object by id the database.
     *
     * @param int $id the id of the evaluation
     * @return Evaluation|boolean an Evaluation object if the query execution succeeds, false otherwise.
     */
    public function getEvaluationById($id) {
        try {
            $this->conn->execute($sql, $params);
            $sql = 'SELECT * FROM Evaluation ';
            $sql .= 'WHERE id = :id';
            $params = array(
                ':id' => $id
            );
            $result = $this->conn->query($sql, $params);

            return self::ExtractEvaluationFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to add new evaluation: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Gets evaluation objects by student's userId the database.
     *
     * @param int $userId the user id of the student
     * @return Evaluation[]|boolean an Evaluation object if the query execution succeeds, false otherwise.
     */
    public function getEvaluationsByStudentUserId($userId) {
        try {
            $this->conn->execute($sql, $params);
            $sql = 'SELECT * FROM Evaluation ';
            $sql .= 'WHERE fk_student_id = :userId';
            $params = array(
                ':userId' => $userId
            );
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractEvaluationFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to add new evaluation: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new evaluation object to the database.
     *
     * @param \Model\Evaluation $evaluation the evaluation to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewEvaluation($evaluation) {
        try {

            $sql = 'INSERT INTO Evaluation ';
            $sql .= '(id, fk_student_id, fk_reviewer_id, fk_evaluation_upload, date_created ';
            $sql .= 'VALUES (:id,:fk_student_id,:fk_reviewer_id,:fk_evaluation_upload,:date_created)';
            $params = array(
                ':id' => $evaluation->getId(),
                ':fk_student_id' => $evaluation->getFkStudentId(),
                ':fk_reviewer_id' => $evaluation->getFkReviewerId(),
                ':fk_evaluation_upload' => $evaluation->getFkEvaluationUpload(),
                ':date_created' => $evaluation->getDateCreated()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new evaluation: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Evaluation object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing upload information
     * @return \Model\Evaluation
     */
    public static function ExtractEvaluationFromRow($row) {
		$evaluation = new Evaluation($row['id']);
        $evaluation->setFkStudentId($row['fk_student_id'])
            ->setFkReviewerId($row['fk_reviewer_id'])
            ->setFkEvaluationUpload($row['fk_evaluation_upload']);
            ->setDateCreated($row['date_created']);
        
        return $evaluation;
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
