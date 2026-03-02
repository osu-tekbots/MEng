<?php
namespace DataAccess;

use Model\Rubric;
use Model\RubricItem;
use Model\RubricItemOption;
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

    // --- RUBRIC METHODS ---
//In use and tested (2/17/2026: Don)
    public function getAllRubrics() {
        try {
            $sql = 'SELECT * FROM Rubrics';
            $result = $this->conn->query($sql);
            $rubrics = array();
            foreach ($result as $row) {
                $rubric = self::ExtractRubricFromRow($row);
                $rubric->items = $this->getRubricItems($rubric->getId());
                $rubrics[] = $rubric;
            }
            return $rubrics;
        } catch (\Exception $e) {
            $this->logError('Failed to get all rubrics: ' . $e->getMessage());
            return array();
        }
    }
//In use and tested (2/17/2026: Don)
    public function getRubricById($id) {
        try {
            $sql = 'SELECT * FROM Rubrics WHERE id = :id';
            $params = [':id' => $id];
            $result = $this->conn->query($sql, $params);
            if (!$result || count($result) == 0) return false;
            
			$rubric = self::ExtractRubricFromRow($result[0]);
			
            $rubric->items = $this->getRubricItems($id);
			
            return $rubric;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a new Rubric to the database.
     * @param Rubric $rubric
     * @return bool
     */
    public function addNewRubric($rubric) {
        try {
            $sql = 'INSERT INTO Rubrics (id, name, last_used, last_modified) VALUES (:id, :name, :last_used, :last_modified)';
            $params = [
                ':id' => $rubric->getId(),
                ':name' => $rubric->getName(),
                ':last_used' => $rubric->getLastUsed(),
                ':last_modified' => $rubric->getLastModified()
            ];
            $this->conn->execute($sql, $params);
            return true; 
        } catch (\Exception $e) {
            $this->logError('Failed to add new rubric : ' . $e->getMessage());
            return false;
        }
    }

     /**
     * Updates a Rubricin the database.
     * @param Rubric $rubric
     * @return bool
     */
    public function updateRubric($rubric) {
        try {
            $sql = 'UPDATE Rubrics SET name = :name, last_used = :last_used, last_modified = :last_modified WHERE id = :id';
            $params = [
                ':id' => $rubric->getId(),
                ':name' => $rubric->getName(),
                ':last_used' => $rubric->getLastUsed(),
                ':last_modified' => $rubric->getLastModified()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update rubric : ' . $e->getMessage());
            return false;
        }
    }

     /**
     * Gets the last inserted rubric id.
     * @return int|null
     */
    public function getLastInsertedRubricId() {
        try {
            $sql = 'SELECT id FROM Rubrics ORDER BY id DESC LIMIT 1';
            $result = $this->conn->query($sql);
            if ($result && count($result) > 0) {
                return $result[0]['id'];
            }
        } catch (\Exception $e) {
            $this->logError('Failed to get last inserted rubric id: ' . $e->getMessage());
        }
        return null;
    }

    //public function deleteRubric($id), todo

    ////////////////////////////////
    /// Rubric  item functions
    ////////////////////////////////


//In use and tested (2/17/2026: Don)
    public function getRubricItems($rubricId) {
        try {
            $sql = 'SELECT * FROM Rubric_items WHERE fk_rubric_id = :id';
            $params = [':id' => $rubricId];
            $result = $this->conn->query($sql, $params);
			
			$items = null;
			foreach ($result as $row){
				$item = self::ExtractRubricItemFromRow($row);
				$item->items = $this->getRubricItemOptionsByItemId($item->getId());
				$items[] = $item;
			}
            return $items;
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric items: ' . $e->getMessage());
            return [];
        }
    }

    public function getRubricItemById($id) {
        try {
            $sql = 'SELECT * FROM Rubric_items WHERE id = :id';
            $params = [':id' => $id];
            $result = $this->conn->query($sql, $params);
            if (!$result || count($result) === 0) return false;
            return self::ExtractRubricItemFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric item by id: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a rubric  item given description, name, and answer type.
     * @param int $rubricId
     * @param string $name
     * @param string $description
     * @param bool $commentRequired
     * @return RubricItem|false
     */
    public function createRubricItem($rubricId, $name, $description, $commentRequired) {
        $item = new RubricItem();
        if (func_num_args() === 4 && $rubricId !== null) {
            $item->setFkRubricId($rubricId);
        }
        $item->setFkRubricId($rubricId)
            ->setName($name)
            ->setDescription($description)
            ->setCommentRequired($commentRequired);
        if ($this->addRubricItem($item)) {
            return $item;
        }
        return false;
    }
    
    /**
     * Adds a new RubricItem to the database.
     * @param RubricItem $item
     * @return bool
     */
    public function addRubricItem($item) {
        try {
            $sql = 'INSERT INTO Rubric_items (id, fk_rubric_id, name, description, comment_required) VALUES (:id, :fk_rubric_id, :name, :description, :comment_required)';
            $params = [
                ':id' => $item->getId(),
                ':fk_rubric_id' => $item->getFkRubricId(),
                ':name' => $item->getName(),
                ':description' => $item->getDescription(),
                ':comment_required' => $item->getCommentRequired()
                ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add rubric item: ' . $e->getMessage());
        }
    }

    /**
     * Updates a RubricItem in the database.
     * @param RubricItem $item
     * @return bool
     */
    public function updateRubricItem($item) {
         try {
            $sql = 'UPDATE Rubric_items SET name = :name, description = :description, comment_required = :comment_required WHERE id = :id';
             $params = [
                ':id' => $item->getId(),
                ':name' => $item->getName(),
                ':description' => $item->getDescription(),
                ':comment_required' => $item->getCommentRequired()
                ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update rubric item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a RubricItem by id.
     * @param int $id
     * @return bool
     */
    public function deleteRubricItem($id) {
        try {
            $sql = 'DELETE FROM Rubric_items WHERE id = :id';
            $params = [':id' => $id];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to delete rubric  item: ' . $e->getMessage());
            return false;
        }
    }

    ////////////////////////////////
    /// Rubric Item Option CRUD
    ////////////////////////////////

    /**
     * Gets a RubricItemOption by id.
     * @param int $id
     * @return RubricItemOption|false
     */
    public function getRubricItemOptionById($id) {
        try {
            $sql = 'SELECT * FROM Rubric_item_options WHERE id = :id';
            $params = [':id' => $id];
            $result = $this->conn->query($sql, $params);
            if (!$result || count($result) === 0) return false;
            return self::ExtractRubricItemOptionFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to get rubric item option by id: ' . $e->getMessage());
            return false;
        }
    }


//In use and tested (2/17/2026: Don)	
	/**
     * Gets all RubricItemOptions by RubricItem id.
     * @param int $id
     * @return RubricItemOption[] | false
     */
    public function getRubricItemOptionsByItemId($id) {
        try {
            $sql = 'SELECT * FROM Rubric_item_options WHERE fk_rubric_item_id = :id ORDER BY Title ASC';
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
	
	/**
     * Gets all RubricItemOption by Rubric id.
     * @param int $id
     * @return RubricItemOption[] | false
     */
    public function getRubricItemOptionByRubricId($id) {
        try {
            $sql = 'SELECT * FROM Rubric_item_options WHERE fk_rubric_item_id = :id ORDER BY Title ASC';
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

    /**
     * Creates a new RubricItemOption with the given values.
     * @param int $fkRubricItemId
     * @param string $value
     * @param string $title
     * @return RubricItemOption|false
     */
    public function createRubricItemOption($fkRubricItemId, $value, $title) {
        $option = new \Model\RubricItemOption();
        $option->setFkRubricItemId($fkRubricItemId)
            ->setValue($value)
            ->setTitle($title);
        if ($this->addRubricItemOption($option)) {
            return $option;
        }
        return false;
    }

    /**
     * Adds a new RubricItemOption to the database.
     * @param RubricItemOption $option
     * @return bool
     */
    public function addRubricItemOption($option) {
        try {
            $sql = 'INSERT INTO Rubric_item_options (id, fk_rubric_item_id, value, title) VALUES (:id, :fk_rubric_item_id, :value, :title)';
            $params = [
                ':id' => $option->getId(),
                ':fk_rubric_item_id' => $option->getFkRubricItemId(),
                ':value' => $option->getValue(),
                ':title' => $option->getTitle()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add rubric item option: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a RubricItemOption in the database.
     * @param RubricItemOption $option
     * @return bool
     */
    public function updateRubricItemOption($option) {
        try {
            $sql = 'UPDATE Rubric_item_options SET fk_rubric_item_id = :fk_rubric_item_id, value = :value, title = :title WHERE id = :id';
            $params = [
                ':id' => $option->getId(),
                ':fk_rubric_item_id' => $option->getFkRubricItemId(),
                ':value' => $option->getValue(),
                ':title' => $option->getTitle()
            ];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update rubric item option: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a RubricItemOption by id.
     * @param int $id
     * @return bool
     */
    public function deleteRubricItemOption($id) {
        try {
            $sql = 'DELETE FROM Rubric_item_options WHERE id = :id';
            $params = [':id' => $id];
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to delete rubric item option: ' . $e->getMessage());
            return false;
        }
    }

    /*
    Removed after big db change
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
    */
//In use and tested (2/17/2026: Don)
    public static function ExtractRubricFromRow($row) {
        $rubric = new Rubric($row['id']);
        $rubric->setName($row['name'])
            ->setLastUsed($row['last_used'])
            ->setLastModified($row['last_modified']);
        return $rubric;
    }
//In use and tested (2/17/2026: Don)
    public static function ExtractRubricItemFromRow($row) {
        $item = new RubricItem($row['id']);
        $item->setFkRubricId($row['fk_rubric_id'])
            ->setName($row['name'])
            ->setDescription($row['description'])
            ->setCommentRequired($row['comment_required']);
        return $item;
    }
//In use and tested (2/17/2026: Don)
    public static function ExtractRubricItemOptionFromRow($row) {
        $option = new RubricItemOption($row['id']);
        $option->setFkRubricItemId($row['fk_rubric_item_id'])
            ->setValue($row['value'])
            ->setTitle($row['title']);
        return $option;
    }

    private function logError($message) {
        if ($this->logger != null) {
            $this->logger->error($message);
        }
    }
   
    /*
    // --- OLD EVALUATION RUBRIC LOGIC ---

    /**
     * Copies a Rubric  and its items into a new Evaluation Rubric instance.
     * @param string $evaluationId The ID of the evaluation to assign this rubric to.
     * @param int $rubricId The ID of the  to copy.
     * @return bool True on success, false on failure.
     
    public function createRubricForEvaluation($evaluationId, $rubricId) {
        try {
            // 1. Fetch the Source 
            $rubric = $this->getRubricById($rubricId);
            if (!$rubric) {
                $this->logError("Rubric  ID $rubricId not found.");
                return false;
            }

            // 2. Create new EvaluationRubric Model
            $evalRubric = new EvaluationRubric();
            $evalRubric->setFkEvaluationId($evaluationId);
            $evalRubric->setName($rubric->getName());
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
            $rubricItems = $rubric->items; // Access public property populated by getRubricById
            
            if (!empty($rubricItems)) {
                foreach ($rubricItems as $tmplItem) {
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

    /*
    Old versions
    public static function ExtractRubricFromRow($row) {
        $rubric = new Rubric($row['id']);
        $rubric->setFkRubricId($row['fk_rubric_id'])
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
    */
}