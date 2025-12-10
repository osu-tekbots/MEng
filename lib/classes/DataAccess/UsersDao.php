<?php
namespace DataAccess;

use Model\User;
use Model\UserFlag;

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
     * Fetches user developer status with the user's UUID.
     *
     * @param string $uuid the UUID of the user, provided by OSU
     * @return boolean true if the user is a developer, 
     * false otherwise
     */
    public function userIsDeveloper($uuid) {
        try {

            $user = $this->getUserByUuid($uuid);

            $sql = 'SELECT * FROM User_flag_assignments ';
            $sql .= 'WHERE fk_user_flag_id = 1 ';
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
     * Fetches all users with the student flag
     *
     * @return User[]|boolean an array of User objects if the fetch succeeds, false otherwise
     */
    public function getAllStudents() {
        try {
            $sql = 'SELECT Users.* FROM Users ';
            $sql .= 'LEFT JOIN User_flag_assignments ';
            $sql .= 'ON Users.id = User_flag_assignments.fk_user_id ';
            $sql .= 'WHERE User_flag_assignments.fk_user_flag_id = 2';

            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch students: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches all users who have the Student flag AND the specific department flag.
     *
     * @param int $departmentFlagId The ID of the department flag
     * @return User[]|boolean An array of User objects or false on failure
     */
    public function getStudentsByDepartment($departmentFlagId) {
        try {
            $sql = 'SELECT Users.* FROM Users ';
            // Join for the Student Flag (ID 2)
            $sql .= 'JOIN User_flag_assignments as ufa_student ON Users.id = ufa_student.fk_user_id ';
            // Join for the Department Flag (Selected ID)
            $sql .= 'JOIN User_flag_assignments as ufa_dept ON Users.id = ufa_dept.fk_user_id ';
            
            $sql .= 'WHERE ufa_student.fk_user_flag_id = 2 ';
            $sql .= 'AND ufa_dept.fk_user_flag_id = :dept_id';

            $params = array(':dept_id' => $departmentFlagId);
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch students by department: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all users with the admin flag
     *
     * @return User[]|boolean an array of User objects if the fetch succeeds, false otherwise
     */
    public function getAllAdmins() {
        try {
            $sql = 'SELECT Users.* FROM Users ';
            $sql .= 'LEFT JOIN User_flag_assignments ';
            $sql .= 'ON Users.id = User_flag_assignments.fk_user_id ';
            $sql .= 'WHERE User_flag_assignments.fk_user_flag_id = 3';

            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch admins: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches all users with the reviewer flag
     *
     * @return User[]|boolean an array of User objects if the fetch succeeds, false otherwise
     */
    public function getAllReviewers() {
        try {
            $sql = 'SELECT Users.* FROM Users ';
            $sql .= 'LEFT JOIN User_flag_assignments ';
            $sql .= 'ON Users.id = User_flag_assignments.fk_user_id ';
            $sql .= 'WHERE User_flag_assignments.fk_user_flag_id = 4';

            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch reviewers: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches all users who have the Reviewer flag AND the specific department flag.
     *
     * @param int $departmentFlagId The ID of the department flag
     * @return User[]|boolean An array of User objects or false on failure
     */
    public function getReviewersByDepartment($departmentFlagId) {
        try {
            $sql = 'SELECT Users.* FROM Users ';
            // Join for the Reviewer Flag (ID 4)
            $sql .= 'JOIN User_flag_assignments as ufa_reviewer ON Users.id = ufa_reviewer.fk_user_id ';
            // Join for the Department Flag (Selected ID)
            $sql .= 'JOIN User_flag_assignments as ufa_dept ON Users.id = ufa_dept.fk_user_id ';
            
            $sql .= 'WHERE ufa_reviewer.fk_user_flag_id = 4 ';
            $sql .= 'AND ufa_dept.fk_user_flag_id = :dept_id';

            $params = array(':dept_id' => $departmentFlagId);
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractUserFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch reviewers by department: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all department user flags
     *
     * @return UserFlag[]|boolean an array of UserFlag objects if the fetch succeeds, false otherwise
     */
    public function getAllDepartmentFlags() {
        try {
            $sql = 'SELECT User_flags.* FROM User_flags ';
            $sql .= 'WHERE User_flags.flag_type = "Department"';

            $result = $this->conn->query($sql);

            return \array_map('self::ExtractUserFlagFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch department flags: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetches all department user flags
     *
     * @param string $id the id of the user
     * @return UserFlag[]|boolean an array of UserFlag objects if the fetch succeeds, false otherwise
     */
    public function getUserFlags($id) {
        try {
            $sql = 'SELECT User_flags.* FROM User_flags ';
            $sql .= 'LEFT JOIN User_flag_assignments ';
            $sql .= 'ON User_flags.id = User_flag_assignments.fk_flag_id ';
            $sql .= 'WHERE User_flag_assignments.fk_user_id = :id';

            $params = array(':id' => $id);
            $result = $this->conn->query($sql, $params);

            return \array_map('self::ExtractUserFlagFromRow', $result);
        } catch (\Exception $e) {
            $this->logError('Failed to fetch flags: ' . $e->getMessage());

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
     * Creates a new User Flag object by extracting the information from a row in the database.
     *
     * @param string[] $row a row from the database containing user flag information
     * @return \Model\UserFlag
     */
    public static function ExtractUserFlagFromRow($row) {
		$userflag = new UserFlag($row['id']);
        $userflag->setFlagName($row['flag_name'])
            ->setFlagType($row['flag_type'])
            ->setIsActive($row['is_active']);
        
        return $userflag;
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
