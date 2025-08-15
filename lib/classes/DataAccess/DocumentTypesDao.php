<?php
namespace DataAccess;

use Model\DocumentType;

/**
 * Contains logic for database interactions with document type data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class DocumentTypesDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a Document Type Data Access Object.
     *
     * @param DatabaseConnection $connection the connection used to perform document type-related queries on the database
     * @param \Util\Logger $logger the logger to use for logging messages and errors associated with fetching document type data
     * @param boolean $echoOnError determines whether to echo an error whether or not a logger is present
     */
    public function __construct($connection, $logger = null, $echoOnError = false) {
        $this->logger = $logger;
        $this->conn = $connection;
        $this->echoOnError = $echoOnError;
    }

    /**
     * Fetches all the document types from the database.
     * 
     * If an error occurs during the fetch, the function will return `false`.
     *
     * @return DocumentType[]|boolean an array of Document Type objects if the fetch succeeds, false otherwise
     */
    public function getAllDocumentTypes() {
        try {
            $sql = 'SELECT * FROM Document_types';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractDocumentTypeFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch document types: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single document type with the given ID from the database.
     *
     * @param string $id the ID of the document type to fetch
     * @return DocumentType|boolean the corresponding Document Type from the database if the fetch succeeds and the
     * document type exists, false otherwise
     */
    public function getDocumentType($id) {
        try {
            $sql = 'SELECT * FROM Document_types ';
            $sql .= 'WHERE id = :id';
            $params = array(':id' => $id);
            $result = $this->conn->query($sql, $params);
            if (\count($result) == 0) {
                return false;
            }

            return self::ExtractDocumentTypeFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single document type by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new document type to the database.
     *
     * @param \Model\DocumentType $documentType the document type to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewDocumentType($documentType) {
        try {
            $this->logger->info("Adding new document type");

            $sql = 'INSERT INTO Document_types ';
            $sql .= '(type_name) ';
            $sql .= 'VALUES (:type_name)';
            $params = array(
                ':type_name' => $documentType->getTypeName()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new document type: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new Document Type object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing document type information
     * @return \Model\DocumentType
     */
    public static function ExtractDocumentTypeFromRow($row) {
		$documentType = new DocumentType($row['id']);
        $documentType->setTypeName($row['type_name']);
        
        return $documentType;
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
