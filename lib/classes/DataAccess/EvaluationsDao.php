<?php
namespace DataAccess;

use Model\Evaluation;
use Model\EvaluationRubricItem;
use Model\EvaluationRubric;
use Model\EvaluationFlag;

/**
 * Contains logic for database interactions with evaluations data in the database. 
 * * DAO stands for 'Data Access Object'
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
            $sql = 'SELECT * FROM Evaluations ';
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
            $sql = 'SELECT * FROM Evaluations ';
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
     * Gets evaluation objects by reviewer's userId the database.
     *
     * @param int $userId the user id of the reviewer
     * @return Evaluation[]|boolean an Evaluation object if the query execution succeeds, false otherwise.
     */
    public function getEvaluationsByReviewerUserId($userId) {
        try {
            $sql = 'SELECT * FROM Evaluations ';
            $sql .= 'WHERE fk_reviewer_id = :userId';
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

    public function getEvaluationRubricsByReviewerUserId($userId) {
        try {
            $evaluations = $this->getEvaluationsByReviewerUserId($userId);
            $rubrics = [];
            foreach ($evaluations as $evaluation) {
                $rubric = $this->getEvaluationRubricFromEvaluationId($evaluation->getId());
                if ($rubric) {
                    $rubrics[] = $rubric;
                }
            }
            return $rubrics;
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation rubrics from reviewer id: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new evaluation object to the database.
     * REMOVED try/catch so errors bubble up to handler.
     * FIXED table name typo (Evaluations).
     *
     * @param \Model\Evaluation $evaluation the evaluation to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewEvaluation($evaluation) {
        $sql = 'INSERT INTO Evaluations ';
        $sql .= '(id, fk_student_id, fk_reviewer_id, fk_upload_id, date_created) ';
        $sql .= 'VALUES (:id, :fk_student_id, :fk_reviewer_id, :fk_upload_id, :date_created)';
        $params = array(
            ':id' => $evaluation->getId(),
            ':fk_student_id' => $evaluation->getFkStudentId(),
            ':fk_reviewer_id' => $evaluation->getFkReviewerId(),
            ':fk_upload_id' => $evaluation->getFkUploadId(),
            ':date_created' => $evaluation->getDateCreated()
        );
        $this->conn->execute($sql, $params);

        return true;
    }

    public function getEvaluationRubricFromEvaluationId($evaluationId) {
        try {
            $sql = 'SELECT * FROM Evaluation_rubrics ';
            $sql .= 'WHERE fk_evaluation_id = :evaluationId';
            $params = array(
                ':evaluationId' => $evaluationId
            );
            $result = $this->conn->query($sql, $params);
            //Will fail if multiple rubrics per evaluation
            // $this -> logError(json_encode($result[0]));
            if (!$result) return false;
            
            $template = $this -> ExtractEvaluationRubricFromRow($result[0]);
            $template -> items = $this->getEvaluationRubricTemplateItems($template->getId());
            return $template;
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation rubric from evaluation id: ' . $e->getMessage());

            return false;
        }
    }

    public function getEvaluationRubricTemplateItems($evaluationRubricId) {
        try {
            $sql = 'SELECT * FROM Evaluation_rubric_items ';
            $sql .= 'WHERE fk_evaluation_rubric_id = :evaluationRubricId';
            $params = array(
                ':evaluationRubricId' => $evaluationRubricId
            );
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractEvaluationRubricItemFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation rubric template items: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Evaluation object from foreign keys.
     * REMOVED try/catch so errors bubble up.
     */
    public function createEvaluation($studentId, $reviewerId, $uploadId) {
        // 1. Generate a random 8-character ID
        $id = bin2hex(random_bytes(4));

        // 2. Get current date and time
        $dateCreated = date('Y-m-d H:i:s');

        // 3. Instantiate the Model
        $evaluation = new Evaluation($id);
        $evaluation->setFkStudentId($studentId)
                    ->setFkReviewerId($reviewerId)
                    ->setFkUploadId($uploadId)
                    ->setDateCreated($dateCreated);

        // 4. Persist to Database
        if ($this->addNewEvaluation($evaluation)) {
            return $evaluation;
        }

        return false;
    }

    /**
     * Assigns a specific rubric template to an evaluation.
     * Note: In a full system, this might involve copying rubric items. 
     * Here we assume a direct link or simple creation for the sake of the assignment flow.
     */
    public function assignRubricToEvaluation($evaluationId, $rubricTemplateId) {
        try {
            // We need to fetch the template name to insert into Evaluation_rubrics
            // or simply link them. This SQL assumes we are creating a new rubric instance
            // linked to the evaluation based on the selected ID.
            $sql = 'INSERT INTO Evaluation_rubrics (id, fk_evaluation_id, name, date_created) 
                    SELECT :id, :evalId, name, NOW() 
                    FROM Rubrics WHERE id = :rubricId';
            
            $id = bin2hex(random_bytes(4));
            
            $params = array(
                ':id' => $id,
                ':evalId' => $evaluationId,
                ':rubricId' => $rubricTemplateId
            );

            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to assign rubric: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function ExtractEvaluationRubricItemFromRow($row) {
        $item = new EvaluationRubricItem($row['id']);
        $item->setFkEvaluationRubricId($row['fk_evaluation_rubric_id'])
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setAnswerType($row['answer_type'])
                ->setValue($row['value']);

        return $item;
    }

    public static function ExtractEvaluationRubricFromRow($row) {
        $evaluationrubric = new EvaluationRubric($row['id']);
        $evaluationrubric->setFkEvaluationId($row['fk_evaluation_id'])
            ->setName($row['name'])
            ->setDateCreated($row['date_created']);

        return $evaluationrubric;
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
            ->setFkUploadId($row['fk_upload_id'])
            ->setDateCreated($row['date_created']);
        
        return $evaluation;
    }

    /**
     * Creates a new Evaluation Flag object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing evaluation flag information
     * @return \Model\EvaluationFlag
     */
    public static function ExtractEvaluationFlagFromRow($row) {
        $evaluationflag = new EvaluationFlag($row['id']);
        $evaluationflag->setFlagName($row['flag_name'])
            ->setFlagType($row['flag_type'])
            ->setIsActive($row['is_active']);
        
        return $evaluationflag;
    }

    /**
     * Logs an error if a logger was provided to the class when it was constructed.
     * * Essentially a wrapper around the error logging so we don't cause the equivalent of a null pointer exception.
     *
     * @param string $message the message to log.
     * @return void
     */
    private function logError($message) {
        if ($this->logger != null) {
            $this->logger->error($message);
        }
        if ($this->echoOnError) {
            // Echo commented out to prevent breaking JSON API responses
            // echo "$message\n";
        }
    }
}