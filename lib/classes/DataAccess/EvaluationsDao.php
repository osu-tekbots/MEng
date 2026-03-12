<?php
namespace DataAccess;

use Model\Evaluation;
use Model\Rubric;
use Model\RubricItem;
use Model\RubricItemOption;
use Model\EvaluationRubricItem;
use Model\EvaluationFlag;
use Model\EvaluationFlagAssignment;

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
     * Gets an evaluation flag by its id.
     *
     * @param int $id the id of the evaluation flag
     * @return \Model\EvaluationFlag|boolean an EvaluationFlag object if the query execution succeeds, false otherwise.
     */
    public function getEvaluationFlag($id) {
        try {
            $sql = 'SELECT * FROM Evaluation_flags WHERE id = :id';
            $params = array(':id' => $id);
            $result = $this->conn->query($sql, $params);

            if (empty($result)) {
                return false;
            }

            return self::ExtractEvaluationFlagFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation flag by id: ' . $e->getMessage());

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

    //Kind of a rubric dao function 
    public function getRubricsByReviewerUserId($userId) {
        try {
            $evaluations = $this->getEvaluationsByReviewerUserId($userId);
            $rubrics = [];
            foreach ($evaluations as $evaluation) {
                $rubric = $this->getRubricFromEvaluationId($evaluation->getId());
                if ($rubric) {
                    $rubrics[] = $rubric;
                }
            }
            return $rubric;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubrics from reviewer id: ' . $e->getMessage());

            return false;
        }
    }


    public function getRubricFromEvaluationId($evaluationId) {
        try {
            $sql = 'SELECT Rubrics.* FROM Rubrics 
                    JOIN Evaluations on Rubrics.id = Evaluations.fk_rubric_id
                    WHERE Evaluations.id = :evaluationId';
            $params = array(
                ':evaluationId' => $evaluationId
            );
            $result = $this->conn->query($sql, $params);
            //Will fail if multiple rubrics per evaluation
            // $this -> logError(json_encode($result[0]));
            if (!$result) return false;
            
            $rubric = $this -> ExtractRubricFromRow($result[0]);
            $rubric->items = $this->getRubricItems($rubric->getId());
            return $rubric;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric from evaluation id: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Checks if a given rubric is currently being used in any evaluation.
     *
     * @param int $rubricId the id of the rubric to check
     * @return boolean true if the rubric is in use, false otherwise
     */
    public function isRubricInUse($rubricId) {
        try {
            $sql = 'SELECT COUNT(*) as count FROM Evaluations WHERE fk_rubric_id = :rubricId';
            $params = array(
                ':rubricId' => $rubricId
            );
            $result = $this->conn->query($sql, $params);
            
            if ($result && isset($result[0]['count']) && $result[0]['count'] > 0) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logError('Failed to check if rubric is in use: ' . $e->getMessage());
            return false;
        }
    }

    public function getRubricItems($rubricId) {
        try {
            $sql = 'SELECT * FROM Rubric_items WHERE fk_rubric_id = :id';
            $params = [':id' => $rubricId];
            $result = $this->conn->query($sql, $params);
            
            $items = array();
            
            foreach ($result as $row) {
                $item = self::ExtractRubricItemFromRow($row);
                $options = $this->getRubricItemOptionsByItemId($item->getId());
                if ($options === false) {
                    $options = [];
                }
                $item->items = $options; // using ->items as per existing convention
                $items[] = $item;
            }
            return $items;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric items: ' . $e->getMessage());
            return [];
        }
    }

    //DUPLICATE FROM RubricsDao; consider refactor to avoid code duplication
    public static function ExtractRubricItemFromRow($row) {
        $item = new RubricItem($row['id']);
        $item->setFkRubricId($row['fk_rubric_id'])
            ->setName($row['name'])
            ->setDescription($row['description'])
            ->setCommentRequired($row['comment_required']);
        return $item;
    }

    /**
     * Gets all RubricItemOptions by RubricItem id.
     * @param int $id
     * @return RubricItemOption[] | false
     */
    public function getRubricItemOptionsByItemId($id) {
        try {
            $sql = 'SELECT * FROM Rubric_item_options WHERE fk_rubric_item_id = :id ORDER BY Title DESC';
            $params = [':id' => $id];
            $result = $this->conn->query($sql, $params);
            if (!$result || count($result) === 0) return false;
            $options = Array();
			foreach ($result as $row) {
                $option = self::ExtractRubricItemOptionFromRow($row);
                $options[] = $option;
            }
            return $options;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric item option by id: ' . $e->getMessage());
            return false;
        }
    }

    public static function ExtractRubricItemOptionFromRow($row) {
        $option = new RubricItemOption($row['id']);
        $option->setFkRubricItemId($row['fk_rubric_item_id'])
            ->setValue($row['value'])
            ->setTitle($row['title']);
        return $option;
    }

    

    private function buildEvaluationRubricItemObjectFromRow($row) {
        $item = new EvaluationRubricItem($row['id']);

        $item->setFkEvaluationId($row['fk_evaluation_id']);
        $item -> setComments($row['comments']);

        $rubricsDao = new RubricsDao($this->conn, $this->logger);
        $fkRubricItem = $rubricsDao->getRubricItemById($row['fk_rubric_item_id']);
        $fkRubricItemOption = $rubricsDao->getRubricItemOptionById($row['fk_rubric_item_option_id']);

        $item->setRubricItem($fkRubricItem);
        $item->setRubricItemOption($fkRubricItemOption);

        return $item;
    }

    public function getEvaluationRubricItemsForEvaluation($evaluationId) {
        try {
            $sql = 'SELECT * FROM Evaluation_rubric_items ';
            $sql .= 'WHERE fk_evaluation_id = :evaluationId';
            $params = array(
                ':evaluationId' => $evaluationId
            );
            $result = $this->conn->query($sql, $params);

            return \array_map('self::buildEvaluationRubricItemObjectFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation rubric rubric items: ' . $e->getMessage());

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
        $sql .= '(id, fk_student_id, fk_reviewer_id, fk_upload_id, fk_rubric_id, date_created) ';
        $sql .= 'VALUES (:id, :fk_student_id, :fk_reviewer_id, :fk_upload_id, :fk_rubric_id, :date_created)';
        $params = array(
            ':id' => $evaluation->getId(),
            ':fk_student_id' => $evaluation->getFkStudentId(),
            ':fk_reviewer_id' => $evaluation->getFkReviewerId(),
            ':fk_upload_id' => $evaluation->getFkUploadId(),
            ':fk_rubric_id' => $evaluation->getFkRubricId(),
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
     * Gets the status item (evaluation_flag_assignments) of the highest arrangement associated with the evaluation id.
     *
     * @param string $evaluationId the id of the evaluation
     * @return EvaluationFlagAssignment|boolean an EvaluationFlagAssignment object if the query execution succeeds, false otherwise.
     */
    public function getHighestStatusAssignmentByEvaluationId($evaluationId) {
        try {
            // Select the assignment fields, join with flags to access arrangement, filter by ID and Type 'Status'
            $sql = 'SELECT efa.* FROM Evaluation_flag_assignments efa ';
            $sql .= 'JOIN Evaluation_flags ef ON efa.fk_evaluation_flag_id = ef.id ';
            $sql .= 'WHERE efa.fk_evaluation_id = :evaluationId AND ef.type = :type ';
            $sql .= 'ORDER BY ef.arrangement DESC ';
            $sql .= 'LIMIT 1';

            $params = array(
                ':evaluationId' => $evaluationId,
                ':type' => 'Status'
            );
            $result = $this->conn->query($sql, $params);

            if (empty($result)) {
                return false;
            }

            return self::ExtractEvaluationFlagAssignmentFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to get highest status assignment by evaluation id: ' . $e->getMessage());

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

    public function getEvaluationDataForExport($evaluationId) {
        try {
            $evaluationItems = $this->getEvaluationRubricItemsForEvaluation($evaluationId);

            $jsonArray = [];

            foreach ($evaluationItems as $evaluationRubricItem) {
                $rubricItem = $evaluationRubricItem -> getRubricItem();
                $option = $evaluationRubricItem -> getRubricItemOption();
                $jsonArray[] = [
                    'Name' => $rubricItem->getName(),
                    'Description' => $rubricItem->getDescription(),
                    'Answer Title' => $option->getTitle(),
                    'Answer Value' => $option->getValue(),
                    'Comments' => $evaluationRubricItem->getComments(),
                    'Comments Required' => $rubricItem->getCommentRequired()
                ];
            }

            // Convert to JSON
            $jsonData = json_encode($jsonArray);
            $this -> logError('Exported Evaluation Data: ' . $jsonData);
            return $jsonData;

        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation data for export: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Evaluation object from foreign keys.
     * Assigns pending flag to evaluation upon creation.
     * 
     */
    public function createEvaluation($studentId, $reviewerId, $uploadId, $rubricId) {
        // 1. Generate a random 8-character ID
        $id = bin2hex(random_bytes(4));

        // 2. Get current date and time
        $dateCreated = date('Y-m-d H:i:s');

        // 3. Instantiate the Model
        $evaluation = new Evaluation($id);
        $evaluation->setFkStudentId($studentId)
                    ->setFkReviewerId($reviewerId)
                    ->setFkUploadId($uploadId)
                    ->setDateCreated($dateCreated)
                    ->setFkRubricId($rubricId);

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

    /*
        Updates the option and comments of an evaluation rubric item. 
        Expected that the rubric item is already in the database.
        @param EvaluationRubricItem $evaluationRubricItem the evaluation rubric item to update
        @return boolean true if the query execution succeeds, false otherwise.
    */
    public function setEvaluationRubricItem($evaluationRubricItem) {
        try {
            $sql = 'UPDATE Evaluation_rubric_items SET fk_rubric_item_id = :fk_rubric_item_id, fk_rubric_item_option_id = :fk_rubric_item_option_id, comments = :comments WHERE id = :id';
            $params = array(
                ':fk_rubric_item_id' => $evaluationRubricItem->getRubricItem() ? $evaluationRubricItem->getRubricItem()->getId() : null,
                ':fk_rubric_item_option_id' => $evaluationRubricItem->getRubricItemOption() ? $evaluationRubricItem->getRubricItemOption()->getId() : null,
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

    /**
     * Inserts a new evaluation rubric item response into the database.
     * @param EvaluationRubricItem $evaluationRubricItem the evaluation rubric item to insert
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function insertEvaluationRubricItem($evaluationRubricItem) {
        try {
            $sql = 'INSERT INTO Evaluation_rubric_items (fk_evaluation_id, fk_rubric_item_id, fk_rubric_item_option_id, comments) VALUES (:fk_evaluation_id, :fk_rubric_item_id, :fk_rubric_item_option_id, :comments)';
            $params = array(
                ':fk_evaluation_id' => $evaluationRubricItem->getFkEvaluationId(),
                ':fk_rubric_item_id' => $evaluationRubricItem->getRubricItem() ? $evaluationRubricItem->getRubricItem()->getId() : null,
                ':fk_rubric_item_option_id' => $evaluationRubricItem->getRubricItemOption() ? $evaluationRubricItem->getRubricItemOption()->getId() : null,
                ':comments' => $evaluationRubricItem->getComments()
            );
        
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to insert evaluation rubric item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets an EvaluationRubricItem by evaluation ID and rubric item ID.
     * @param string $evaluationId
     * @param string $rubricItemId
     * @return EvaluationRubricItem|false
     */
    public function getEvaluationRubricItemByEvalAndRubricItem($evaluationId, $rubricItemId) {
        try {
            $sql = 'SELECT * FROM Evaluation_rubric_items WHERE fk_evaluation_id = :fk_evaluation_id AND fk_rubric_item_id = :fk_rubric_item_id LIMIT 1';
            $params = array(
                ':fk_evaluation_id' => $evaluationId,
                ':fk_rubric_item_id' => $rubricItemId
            );
            $result = $this->conn->query($sql, $params);
            if (!$result || count($result) === 0) return false;
            
            return $this->buildEvaluationRubricItemObjectFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to get evaluation rubric item by eval and item: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function ExtractEvaluationRubricItemFromRow($row) {
        $item = new EvaluationRubricItem($row['id']);
        //$this->logError(json_encode($row));
        $item->setFkEvaluationRubricId($row['fk_evaluation_id'])
                ->setName($row['name'])
                ->setDescription($row['description'])
                ->setAnswerType($row['answer_type'])
                ->setValue($row['answer_value'])
                ->setComments($row['comments']);

        return $item;
    }

    //Duplicate function from Rubrics Dao; makes more sense to just return rubric id for eval dao functions
    public static function ExtractRubricFromRow($row) {
        $rubric = new Rubric($row['id']);
        $rubric->setName($row['name'])
            ->setLastUsed($row['last_used'])
            ->setLastModified($row['last_modified']);
        return $rubric;
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
            ->setFkRubricId($row['fk_rubric_id'])
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
     * Creates a new Evaluation Flag Assignment object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing evaluation flag assignment information
     * @return \Model\EvaluationFlagAssignment
     */
    public static function ExtractEvaluationFlagAssignmentFromRow($row) {
        $assignment = new EvaluationFlagAssignment($row['id']);
        $assignment->setFkEvaluationId($row['fk_evaluation_id'])
            ->setFkEvaluationFlagId($row['fk_evaluation_flag_id']);

        return $assignment;
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