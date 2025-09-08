<?php
namespace DataAccess;

use Model\RubricTemplate;
use Model\RubricItemTemplate;

/**
 * Contains logic for database interactions with evaluations data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class RubricsDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a Rubrics Data Access Object.
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
     * Gets all rubric templates.
     * @return RubricTemplate[]
     * 
     */
    public function getAllRubricTemplates() {
        try {
            $sql = 'SELECT * FROM Rubric_templates';
            $this->logger && $this->logger->info('[DAO] Running SQL: ' . $sql);
            $result = $this->conn->query($sql);
            $this->logger && $this->logger->info('[DAO] Raw result: ' . var_export($result, true));
            $templates = array();
            foreach ($result as $row) {
                $this->logger && $this->logger->info('[DAO] Row: ' . var_export($row, true));
                $template = self::ExtractRubricTemplateFromRow($row);
                // Populate items for each template
                $template->items = $this->getRubricTemplateItems($template->getId());
                $templates[] = $template;
            }
            $this->logger && $this->logger->info('[DAO] Template count: ' . count($templates));
            return $templates;
        } catch (\Exception $e) {
            $this->logError('Failed to get all rubric templates: ' . $e->getMessage());
            return array();
        }
    }
    /**
     * Gets a RubricTemplate by id.
     * @param int $id
     * @return RubricTemplate|false
     */
    public function getRubricTemplateById($id) {
        try {
            $sql = 'SELECT * FROM Rubric_templates WHERE id = :id';
            $params = [':id' => $id];
            $result = $this->conn->query($sql, $params);
            if (!$result || count($result) === 0) return false;
            $template = self::ExtractRubricTemplateFromRow($result[0]);
            $template->items = $this->getRubricTemplateItems($id);
            return $template;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric template: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Adds a new RubricTemplate to the database.
     * @param RubricTemplate $template
     * @return bool
     */
    public function addNewRubricTemplate($template) {
        try {
            $sql = 'INSERT INTO Rubric_templates (id, name, last_used, last_modified) VALUES (:id, :name, :last_used, :last_modified)';
            $params = [
                ':id' => $template->getId(),
                ':name' => $template->getName(),
                ':last_used' => $template->getLastUsed(),
                ':last_modified' => $template->getLastModified()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new rubric template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a RubricTemplate in the database.
     * @param RubricTemplate $template
     * @return bool
     */
    public function updateRubricTemplate($template) {
        try {
            $sql = 'UPDATE Rubric_templates SET name = :name, last_used = :last_used, last_modified = :last_modified WHERE id = :id';
            $params = [
                ':id' => $template->getId(),
                ':name' => $template->getName(),
                ':last_used' => $template->getLastUsed(),
                ':last_modified' => $template->getLastModified()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update rubric template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a RubricTemplate by id.
     * @param int $id
     * @return bool
     */
    public function deleteRubricTemplate($id) {
        try {
            $sql = 'DELETE FROM Rubric_templates WHERE id = :id';
            $params = [':id' => $id];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to delete rubric template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all RubricTemplateItems for a rubric template.
     * @param int $templateId
     * @return RubricTemplateItem[]
     */

    /**
     * Adds a new RubricTemplateItem to the database.
     * @param RubricTemplateItem $item
     * @return bool
     */
    public function addRubricTemplateItem($item) {
        try {
            $sql = 'INSERT INTO Rubric_item_templates (id, fk_rubric_template_id, name, description, answer_type) VALUES (:id, :fk_rubric_template_id, :name, :description, :answer_type)';
            $params = [
                ':id' => $item->getId(),
                ':fk_rubric_template_id' => $item->getFkRubricTemplateId(),
                ':name' => $item->getName(),
                ':description' => $item->getDescription(),
                ':answer_type' => $item->getAnswerType()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add rubric template item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a RubricTemplateItem in the database.
     * @param RubricTemplateItem $item
     * @return bool
     */
    public function updateRubricTemplateItem($item) {
        try {
            $sql = 'UPDATE Rubric_item_templates SET name = :name, description = :description, answer_type = :answer_type WHERE id = :id';
            $params = [
                ':id' => $item->getId(),
                ':name' => $item->getName(),
                ':description' => $item->getDescription(),
                ':answer_type' => $item->getAnswerType()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update rubric template item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a RubricTemplateItem by id.
     * @param int $id
     * @return bool
     */
    public function deleteRubricTemplateItem($id) {
        try {
            $sql = 'DELETE FROM Rubric_item_templates WHERE id = :id';
            $params = [':id' => $id];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to delete rubric template item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a rubric template item given description, name, and answer type.
     * @param int $templateId
     * @param string $name
     * @param string $description
     * @param string $answerType
     * @return RubricTemplateItem|false
     */
    public function createRubricTemplateItem($templateId, $name, $description, $answerType) {
        $item = new \Model\RubricItemTemplate();
        if (func_num_args() === 4 && $templateId !== null) {
            $item->setFkRubricTemplateId($templateId);
        }
        $item->setFkRubricTemplateId($templateId)
            ->setName($name)
            ->setDescription($description)
            ->setAnswerType($answerType);
        if ($this->addRubricTemplateItem($item)) {
            return $item;
        }
        return false;
    }

    // Extraction helpers
    public static function ExtractRubricTemplateFromRow($row) {
        $template = new RubricTemplate($row['id']);
        $template->setName($row['name'])
            ->setLastUsed($row['last_used'])
            ->setLastModified($row['last_modified']);
        return $template;
    }
    public static function ExtractRubricTemplateItemFromRow($row) {
        $item = new RubricItemTemplate($row['id']);
        $item->setFkRubricTemplateId($row['fk_rubric_template_id'])
            ->setName($row['name'])
            ->setDescription($row['description'])
            ->setAnswerType($row['answer_type']);
        return $item;
    }

    /**
     * Gets all rubric items for a rubric template (as objects).
     * @param int $templateId
     * @return RubricTemplateItem[]
     */
    public function getRubricTemplateItems($templateId) {
        try {
            $sql = 'SELECT * FROM Rubric_item_templates WHERE fk_rubric_template_id = :tid';
            $params = [':tid' => $templateId];
            $result = $this->conn->query($sql, $params);
            return array_map('self::ExtractRubricTemplateItemFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric template items: ' . $e->getMessage());
            return [];
        }
    }

    // Extraction helpers
    public static function ExtractRubricFromRow($row) {
        $rubric = new Rubric($row['id']);
        $rubric->setFkRubricTemplateId($row['fk_rubric_template_id'])
            ->setName($row['name'])
            ->setDateCreated($row['date_created'])
            ->setLastModified($row['last_modified']);
        return $rubric;
    }
    public static function ExtractRubricItemFromRow($row) {
        $item = new RubricItem($row['id']);
        $item->setFkRubricId($row['fk_rubric_id'])
            ->setName($row['name'])
            ->setDescription($row['description'])
            ->setAnswerType($row['answer_type']);
        return $item;
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
                ':fk_evaluation_upload' => $evaluation->getFkUploadId(),
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
            ->setFkUploadId($row['fk_evaluation_upload'])
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

    /**
     * Gets the last inserted rubric template id.
     * @return int|null
     */
    public function getLastInsertedRubricTemplateId() {
        try {
            $sql = 'SELECT id FROM Rubric_templates ORDER BY id DESC LIMIT 1';
            $result = $this->conn->query($sql);
            if ($result && count($result) > 0) {
                return $result[0]['id'];
            }
        } catch (\Exception $e) {
            $this->logError('Failed to get last inserted rubric template id: ' . $e->getMessage());
        }
        return null;
    }
}
