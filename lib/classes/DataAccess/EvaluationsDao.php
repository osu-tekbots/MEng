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
     * Builds a complete Evaluation object from a database row, including associated evaluation flags.
     *
     * @param string[] $row a row from the database containing evaluation information
     * @return \Model\Evaluation
     */
    public function buildEvaluationObjectFromRow($row) {
        $evaluation = self::ExtractEvaluationFromRow($row);
        $evaluation -> setEvaluationFlags($this->getEvaluationFlagsForEvaluation($evaluation->getId()));
        return $evaluation;
    }

    /**
     * Gets every evaluation flag an evaluation is associated with.
     *
     * @param ID $evaluationId the id of the evaluation
     * @return EvaluationFlag[]|boolean an array of Evaluation Flag objects if the query execution succeeds, false otherwise.
     */
    public function getEvaluationFlagsForEvaluation($evaluationId) {
        try {
            //Gets all evaluation flags assigned to a specific evaluation from eval flags assignments table
            $sql = 'SELECT *  FROM Evaluation_flags 
                    JOIN Evaluation_flag_assignments on Evaluation_flags.id = Evaluation_flag_assignments.fk_evaluation_flag_id
                    WHERE Evaluation_flag_assignments.fk_evaluation_id = :evaluationId';
            $params = array(
                ':evaluationId' => $evaluationId
            );
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractEvaluationFlagFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation flags for evaluation: ' . $e->getMessage());

            return false;
        }
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

            return self::buildEvaluationObjectFromRow($result[0]);
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

            return \array_map('self::buildEvaluationObjectFromRow', $result);
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

            return \array_map('self::buildEvaluationObjectFromRow', $result);
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

    /**
     * Adds an evaluation flag to an evaluation.
     *
     * @param string $evaluationId the id of the evaluation
     * @param int $evaluationFlagId the id of the evaluation flag
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addEvaluationFlagToEvaluation($evaluationId, $evaluationFlagId) {
        try {
            $sql = 'INSERT INTO Evaluation_flag_assignments 
                (fk_evaluation_id, fk_evaluation_flag_id) 
                VALUES (:evaluationId, :evaluationFlagId)';

            $params = array(
                ':evaluationId' => $evaluationId,
                ':evaluationFlagId' => $evaluationFlagId
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add evaluation flag to evaluation: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Gets a status evaluation flag by its arrangement value.
     *
     * @param int $arrangement the arrangement value of the status evaluation flag
     * @return EvaluationFlag|boolean an EvaluationFlag object if the query execution succeeds, false otherwise.
     * 
     * Will crash if multiple flags with same arrangement exist.
     */
    public function getStatusEvaluationFlagByArrangement($arrangement) {
        try {
            $sql = 'SELECT * FROM Evaluation_flags ';
            $sql .= 'WHERE type = :type AND arrangement = :arrangement';
            $params = array(
                ':type' => 'status',
                ':arrangement' => $arrangement
            );
            $result = $this->conn->query($sql, $params);

            return self::ExtractEvaluationFlagFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to get status evaluation flag by arrangement: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Sets the status flag for an evaluation based on arrangement.
     *
     * @param string $evaluationId the id of the evaluation
     * @param int $arrangement the arrangement value of the status evaluation flag to set
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function setStatusFlagForEvaluation($evaluationId, $arrangement) {
        $flag = $this->getStatusEvaluationFlagByArrangement($arrangement);
        if (!$flag) {
            $this->logError('No status flag found for arrangement: ' . $arrangement);
            return false;
        }

        return $this->addEvaluationFlagToEvaluation($evaluationId, $flag->getId());
    }

    
    /**
     * Creates a new Evaluation object from foreign keys.
     * Assigns pending flag to evaluation upon creation.
     * 
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

        $evaluationFlag = $this->getStatusEvaluationFlagByArrangement(1); // 1 corresponds to "Pending" status flag
        if ($evaluationFlag) {
            $evaluation->setEvaluationFlags([$evaluationFlag]);
        } else {
            $this->logError('No status flag found for arrangement: 1');
        }

        // 4. Persist to Database
        if ($this->addNewEvaluation($evaluation)) {
            if($this->addEvaluationFlagToEvaluation($id, $evaluationFlag->getId())) {
                return $evaluation;
            }
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

    /*
        Updates the value and comments of an evaluation rubric item. 
        Expected that the rubric item is already in the database.
        @param EvaluationRubricItem $evaluationRubricItem the evaluation rubric item to update
        @return boolean true if the query execution succeeds, false otherwise.
    */
    public function setEvaluationRubricItem($evaluationRubricItem) {
        try {
            $sql = 'UPDATE Evaluation_rubric_items SET answer_value = :value, comments = :comments WHERE id = :id';
            $params = array(
                ':value' => $evaluationRubricItem->getValue(),
                ':id' => $evaluationRubricItem->getId(),
                ':comments' => $evaluationRubricItem->getComments()
            );
        
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to set evaluation rubric item: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function ExtractEvaluationRubricItemFromRow($row) {
        $item = new EvaluationRubricItem($row['id']);
        //$this->logError(json_encode($row));
        $item->setFkEvaluationRubricId($row['fk_evaluation_rubric_id'])
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setAnswerType($row['answer_type'])
                ->setValue($row['answer_value'])
                ->setComments($row['comments']);

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
        $evaluationflag->setName($row['name'])
            ->setType($row['type'])
            ->setArrangement($row['arrangement'])
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