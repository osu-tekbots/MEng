<?php
namespace DataAccess;

use Model\RubricTemplate;
use Model\RubricItemTemplate;
use Model\EvaluationRubric;
use Model\EvaluationRubricItem;

class RubricsDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    public function __construct($connection, $logger = null, $echoOnError = false) {
        $this->logger = $logger;
        $this->conn = $connection;
        $this->echoOnError = $echoOnError;
    }

    // --- RUBRIC TEMPLATE METHODS ---

    public function getAllRubricTemplates() {
        try {
            $sql = 'SELECT * FROM Rubric_templates';
            $result = $this->conn->query($sql);
            $templates = array();
            foreach ($result as $row) {
                $template = self::ExtractRubricTemplateFromRow($row);
                $template->items = $this->getRubricTemplateItems($template->getId());
                $templates[] = $template;
            }
            return $templates;
        } catch (\Exception $e) {
            $this->logError('Failed to get all rubric templates: ' . $e->getMessage());
            return array();
        }
    }

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

    // --- EVALUATION RUBRIC COPYING LOGIC ---

    /**
     * Copies a Rubric Template and its items into a new Evaluation Rubric instance.
     * @param string $evaluationId The ID of the evaluation to assign this rubric to.
     * @param int $rubricTemplateId The ID of the template to copy.
     * @return bool True on success, false on failure.
     */
    public function createRubricForEvaluation($evaluationId, $rubricTemplateId) {
        try {
            // 1. Fetch the Source Template
            $template = $this->getRubricTemplateById($rubricTemplateId);
            if (!$template) {
                $this->logError("Rubric Template ID $rubricTemplateId not found.");
                return false;
            }

            // 2. Create new EvaluationRubric Model
            $evalRubric = new EvaluationRubric();
            $evalRubric->setFkEvaluationId($evaluationId);
            $evalRubric->setName($template->getName());
            $evalRubric->setDateCreated(date('Y-m-d H:i:s'));

            // 3. Save EvaluationRubric to DB
            if (!$this->addNewEvaluationRubric($evalRubric)) {
                return false;
            }

            // 4. Get the new ID
            $newRubricId = $this->getLastInsertedEvaluationRubricId();
            if (!$newRubricId) {
                $this->logError("Could not retrieve ID for new evaluation rubric.");
                return false;
            }

            // 5. Copy Items
            $templateItems = $template->items; // Access public property populated by getRubricTemplateById
            
            if (!empty($templateItems)) {
                foreach ($templateItems as $tmplItem) {
                    $newItem = new EvaluationRubricItem();
                    $newItem->setFkEvaluationRubricId($newRubricId);
                    $newItem->setName($tmplItem->getName());
                    $newItem->setDescription($tmplItem->getDescription());
                    $newItem->setAnswerType($tmplItem->getAnswerType());
                    // $newItem->setValue(null); // Value is null initially

                    $this->addNewEvaluationRubricItem($newItem);
                }
            }

            return true;

        } catch (\Exception $e) {
            $this->logError('Failed to create rubric for evaluation: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewEvaluationRubric($rubric) {
        try {
            $sql = 'INSERT INTO Evaluation_rubrics (fk_evaluation_id, name, date_created) 
                    VALUES (:evalId, :name, :date)';
            $params = [
                ':evalId' => $rubric->getFkEvaluationId(),
                ':name' => $rubric->getName(),
                ':date' => $rubric->getDateCreated()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add evaluation rubric: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewEvaluationRubricItem($item) {
        try {
            $sql = 'INSERT INTO Evaluation_rubric_items (fk_evaluation_rubric_id, name, description, answer_type) 
                    VALUES (:rubricId, :name, :desc, :type)';
            $params = [
                ':rubricId' => $item->getFkEvaluationRubricId(),
                ':name' => $item->getName(),
                ':desc' => $item->getDescription(),
                ':type' => $item->getAnswerType()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add evaluation rubric item: ' . $e->getMessage());
            return false;
        }
    }

    public function getLastInsertedEvaluationRubricId() {
        try {
            $sql = 'SELECT id FROM Evaluation_rubrics ORDER BY id DESC LIMIT 1';
            $result = $this->conn->query($sql);
            if ($result && count($result) > 0) {
                return $result[0]['id'];
            }
        } catch (\Exception $e) {
            $this->logError('Failed to get last inserted evaluation rubric id: ' . $e->getMessage());
        }
        return null;
    }

    // --- STATIC EXTRACTORS ---

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

    private function logError($message) {
        if ($this->logger != null) {
            $this->logger->error($message);
        }
    }
}