<?php
namespace DataAccess;

use Model\Faq;

/**
 * Contains logic for database interactions with FAQ data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class FaqDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a FAQ Data Access Object.
     *
     * @param DatabaseConnection $connection the connection used to perform FAQ-related queries on the database
     * @param \Util\Logger $logger the logger to use for logging messages and errors associated with fetching FAQ data
     * @param boolean $echoOnError determines whether to echo an error whether or not a logger is present
     */
    public function __construct($connection, $logger = null, $echoOnError = false) {
        $this->logger = $logger;
        $this->conn = $connection;
        $this->echoOnError = $echoOnError;
    }

    /**
     * Fetches all FAQs from the database.
     * 
     * If an error occurs during the fetch, the function will return `false`.
     *
     * @return Faq[]|boolean an array of Faq objects if the fetch succeeds, false otherwise
     */
    public function getAllFaqs() {
        try {
            $sql = 'SELECT * FROM faqs';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractFaqFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch FAQs: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches all FAQs for a given category (page) from the database.
     *
     * @param string $category the category (page filename) to fetch FAQs for
     * @return Faq[]|boolean an array of Faq objects if the fetch succeeds, false otherwise
     */
    public function getFaqsByCategory($category) {
        try {
            $sql = 'SELECT * FROM faqs ';
            $sql .= 'WHERE category = :category';
            $params = array(':category' => $category);
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractFaqFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch FAQs by category: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single FAQ with the given ID from the database.
     *
     * @param int $id the ID of the FAQ to fetch
     * @return Faq|boolean the corresponding Faq from the database if the fetch succeeds and the
     * FAQ exists, false otherwise
     */
    public function getFaqById($id) {
        try {
            $sql = 'SELECT * FROM faqs ';
            $sql .= 'WHERE id = :id';
            $params = array(':id' => $id);
            $result = $this->conn->query($sql, $params);
            if (\count($result) == 0) {
                return false;
            }

            return self::ExtractFaqFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single FAQ by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new FAQ to the database.
     *
     * @param \Model\Faq $faq the FAQ to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewFaq($faq) {
        try {
            $sql = 'INSERT INTO faqs ';
            $sql .= '(category, question, answer) ';
            $sql .= 'VALUES (:category, :question, :answer)';
            $params = array(
                ':category' => $faq->getCategory(),
                ':question' => $faq->getQuestion(),
                ':answer' => $faq->getAnswer()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new FAQ: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Updates an existing FAQ in the database.
     *
     * @param \Model\Faq $faq the FAQ to update
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function updateFaq($faq) {
        try {
            $sql = 'UPDATE faqs SET ';
            $sql .= 'category = :category, ';
            $sql .= 'question = :question, ';
            $sql .= 'answer = :answer ';
            $sql .= 'WHERE id = :id';
            $params = array(
                ':category' => $faq->getCategory(),
                ':question' => $faq->getQuestion(),
                ':answer' => $faq->getAnswer(),
                ':id' => $faq->getId()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update FAQ: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Deletes a FAQ from the database by its ID.
     *
     * @param int $id the ID of the FAQ to delete
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function deleteFaq($id) {
        try {
            $sql = 'DELETE FROM faqs WHERE id = :id';
            $params = array(':id' => $id);
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to delete FAQ: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Faq object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing FAQ information
     * @return \Model\Faq
     */
    public static function ExtractFaqFromRow($row) {
        $faq = new Faq($row['id']);
        $faq->setCategory($row['category']);
        $faq->setQuestion($row['question']);
        $faq->setAnswer($row['answer']);

        return $faq;
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
