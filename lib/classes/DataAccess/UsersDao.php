<?php
namespace DataAccess;

use Model\User;

/**
 * Contains logic for database interactions with user data in the database. 
 * 
 * DAO stands for 'Data Access Object'
 */
class UsersDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var boolean */
    private $echoOnError;

    /**
     * Constructs a new instance of a User Data Access Object.
     *
     * @param DatabaseConnection $connection the connection used to perform user-related queries on the database
     * @param \Util\Logger $logger the logger to use for logging messages and errors associated with fetching user data
     * @param boolean $echoOnError determines whether to echo an error whether or not a logger is present
     */
    public function __construct($connection, $logger = null, $echoOnError = false) {
        $this->logger = $logger;
        $this->conn = $connection;
        $this->echoOnError = $echoOnError;
    }

    /**
     * Fetches all the users from the database.
     * 
     * If an error occurs during the fetch, the function will return `false`.
     *
     * @return User[]|boolean an array of User objects if the fetch succeeds, false otherwise
     */
    public function getAllUsers() {
        try {
            $sql = 'SELECT * FROM Users';
            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch users: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single user with the given ID from the database.
     *
     * @param string $id the ID of the user to fetch
     * @return User|boolean the corresponding User from the database if the fetch succeeds and the user exists, 
     * false otherwise
     */
    public function getUser($id) {
        try {
            $sql = 'SELECT * FROM Users ';
            $sql .= 'WHERE id = :id';
            $params = array(':id' => $id);
            $result = $this->conn->query($sql, $params);
            if (\count($result) == 0) {
                return false;
            }

            return self::ExtractUserFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by ID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single user with the user's ONID.
     *
     * @param string $uuid the ONID of the user, provided by OSU
     * @return User|boolean the corresponding User from the database if the fetch succeeds and the user exists, 
     * false otherwise
     */
    public function getUserByOnid($onid) {
        try {
            if($onid == '' || $onid == NULL) {
                $this->logger->warn("Called getUserByOnid() with blank or null ONID");
                return false;
            }

            $sql = 'SELECT * FROM Users ';
            $sql .= 'WHERE onid = :onid';
            $params = array(':onid' => $onid);
            $result = $this->conn->query($sql, $params);
            if (!$result || \count($result) == 0) {
                return false;
            }

            return self::ExtractUserFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by ONID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches a single user with the user's UUID.
     *
     * @param string $uuid the UUID of the user, provided by OSU
     * @return User|boolean the corresponding User from the database if the fetch succeeds and the user exists, 
     * false otherwise
     */
    public function getUserByUuid($uuid) {
        try {
            if($uuid == '' || $uuid == NULL) {
                $this->logger->warn("Called getUserByUuid() with blank or null UUID");
                return false;
            }

            $sql = 'SELECT * FROM Users ';
            $sql .= 'WHERE uuid = :uuid';
            $params = array(':uuid' => $uuid);
            $result = $this->conn->query($sql, $params);
            if (!$result || \count($result) == 0) {
                return false;
            }

            return self::ExtractUserFromRow($result[0]);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by UUID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches user student status with the user's UUID.
     *
     * @param string $uuid the UUID of the user, provided by OSU
     * @return boolean true if the user is a student, 
     * false otherwise
     */
    public function userIsStudent($uuid) {
        try {

            $user = $this->getUserByUuid($uuid);

            $sql = 'SELECT * FROM User_flag_assignments ';
            $sql .= 'WHERE fk_user_flag_id = 2 ';
            $sql .= 'AND fk_user_id = :user_id ';
            $params = array(':user_id' => $user->getId());
            $result = $this->conn->query($sql, $params);
            if (!$result || \count($result) == 0) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by UUID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches user admin status with the user's UUID.
     *
     * @param string $uuid the UUID of the user, provided by OSU
     * @return boolean true if the user is an admin, 
     * false otherwise
     */
    public function userIsAdmin($uuid) {
        try {

            $user = $this->getUserByUuid($uuid);

            $sql = 'SELECT * FROM User_flag_assignments ';
            $sql .= 'WHERE fk_user_flag_id = 3 ';
            $sql .= 'AND fk_user_id = :user_id ';
            $params = array(':user_id' => $user->getId());
            $result = $this->conn->query($sql, $params);
            if (!$result || \count($result) == 0) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by UUID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches user reviewer status with the user's UUID.
     *
     * @param string $uuid the UUID of the user, provided by OSU
     * @return boolean true if the user is a reviewer, 
     * false otherwise
     */
    public function userIsReviewer($uuid) {
        try {

            $user = $this->getUserByUuid($uuid);

            $sql = 'SELECT * FROM User_flag_assignments ';
            $sql .= 'WHERE fk_user_flag_id = 4 ';
            $sql .= 'AND fk_user_id = :user_id ';
            $params = array(':user_id' => $user->getId());
            $result = $this->conn->query($sql, $params);
            if (!$result || \count($result) == 0) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to fetch single user by UUID: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Adds a new user to the database.
     *
     * @param \Model\User $user the user to add to the database
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function addNewUser($user) {
        try {
            $this->logger->info("Adding new user");

            $sql = 'INSERT INTO Users ';
            $sql .= '(id, uuid, first_name, last_name, onid, email, last_login ';
            $sql .= 'VALUES (:id,:uuid,:first_name,:last_name,:onid,:email,:last_login)';
            $params = array(
                ':id' => $user->getId(),
                ':uuid' => $user->getUuid(),
                ':first_name' => $user->getFirstName(),
                ':last_name' => $user->getLastName(),
                ':onid' => $user->getOnid(),
                ':email' => $user->getEmail(),
                ':last_login' => QueryUtils::FormatDate($user->getLastLogin())
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to add new user: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Updates an existing user in the database. 
     * 
     * This function only updates trivial user information, such as the type, first and last names, salutation, majors, 
     * affiliations, and contact information.
     *
     * @param \Model\User $user the user to update
     * @return boolean true if the query execution succeeds, false otherwise.
     */
    public function updateUser($user) {
        try {
            $sql = 'UPDATE Users SET ';
            $sql .= 'uuid = :uuid,';
            $sql .= 'osu_id = :osu_id, ';
            $sql .= 'first_name = :first_name, ';
            $sql .= 'last_name = :last_name, ';
            $sql .= 'onid = :onid, ';
			$sql .= 'email = :email, ';
            $sql .= 'WHERE id = :id';
            $params = array(
                ':uuid' => $user->getUuid(),
                ':osu_id' => $user->getOsuId(),
                ':first_name' => $user->getFirstName(),
                ':last_name' => $user->getLastName(),
                ':onid' => $user->getOnid(),
				':email' => $user->getEmail(),
                ':id' => $user->getId()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to update user: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Creates a new User object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing user information
     * @return \Model\User
     */
    public static function ExtractUserFromRow($row) {
		$user = new User($row['id']);
        $user->setUuid($row['uuid'])
            ->setOsuId($row['osu_id'])
            ->setFirstName($row['first_name'])
            ->setLastName($row['last_name'])
            ->setOnid($row['onid'])
            ->setEmail($row['email'])
            ->setLastLogin(new \DateTime(($row['last_login'] == '' ? "now" : $row['last_login'])));
        
        return $user;
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
