<?php
namespace Api;

use Model\User;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class UserActionHandler extends ActionHandler {

    /** @var \DataAccess\UsersDao */
    private $usersDao;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\UsersDao $dao the data access object for users
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($usersDao, $logger) {
        parent::__construct($logger);
        $this->usersDao = $usersDao;
    }

    /**
     * Updates the user profile information (First Name, Last Name, Email).
     *
     * @return void
     */
    public function handleUpdateUserProfile() {
        // 1. Get parameters from the request body
        $userId = $this->getFromBody('userId');
        $firstName = $this->getFromBody('firstName');
        $lastName = $this->getFromBody('lastName');
        $email = $this->getFromBody('email');

        // 2. Security Check: Ensure the user is updating themselves or is an admin
        // (Assuming you have access to $_SESSION['userID'] and $_SESSION['userIsAdmin'] here)
        if ($userId != $_SESSION['userID'] && !$_SESSION['userIsAdmin']) {
             $this->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to update this user.'));
             return;
        }

        // 3. Fetch the existing user
        $user = $this->usersDao->getUser($userId);
        if (!$user) {
            $this->respond(new Response(Response::NOT_FOUND, 'User not found.'));
            return;
        }

        // 4. Update the object
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);

        // 5. Persist to Database
        if ($this->usersDao->updateUser($user)) {
            $this->respond(new Response(
                Response::OK,
                'Profile updated successfully'
            ));
        } else {
            $this->respond(new Response(
                Response::INTERNAL_SERVER_ERROR,
                'Failed to update profile in database.'
            ));
        }
    }

    /**
     * Changes the $_SESSION variable 'userType'
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleChangeUserType() {
        // Make sure the required parameters exit
        $userType = $this->getFromBody('userType');

        $_SESSION['userType'] = $userType;

        $this->respond(new Response(
            Response::OK,
            'Successfully swapped user types'
        ));
    }

    public function handleToggleFlag() {
        $userId = $this->getFromBody('userId');
        $flagId = $this->getFromBody('flagId');
        $operation = $this->getFromBody('operation'); // 'add' or 'remove'

        $success = false;
        if ($operation === 'add') {
            $success = $this->usersDao->addUserFlag($userId, $flagId);
        } else {
            $success = $this->usersDao->removeUserFlag($userId, $flagId);
        }

        if ($success) {
            $this->respond(new Response(Response::OK, 'Permission updated'));
        } else {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Database update failed'));
        }
    }

    public function handleAdminToggleFlag() {
        $userId = $this->getFromBody('userId');
        $flagId = $this->getFromBody('flagId');
        $operation = $this->getFromBody('operation'); // 'add' or 'remove'

        // SECURITY: Only Admins can change flags
        if (!isset($_SESSION['userIsAdmin']) || !$_SESSION['userIsAdmin']) {
            $this->respond(new Response(Response::UNAUTHORIZED, 'Only admins can modify permissions.'));
            return;
        }

        $success = false;
        if ($operation === 'add') {
            $success = $this->usersDao->addUserFlag($userId, $flagId);
        } else {
            $success = $this->usersDao->removeUserFlag($userId, $flagId);
        }

        if ($success) {
            $this->respond(new Response(Response::OK, 'Permission updated'));
        } else {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Database update failed'));
        }
    }

    /**
     * Handles the HTTP request on the API resource. 
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body. If
     * the `action` parameter is not in the body, the request will be rejected. The assumption is that the request
     * has already been authorized before this function is called.
     *
     * @return void
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $this->requireParam('action');

        // Call the correct handler based on the action
        switch ($this->requestBody['action']) {

            case 'updateUserProfile':
                $this->handleUpdateUserProfile();

            case 'changeUserType':
                $this->handleChangeUserType();

            case 'toggleUserFlag':
                $this->handleToggleFlag();

            case 'toggleAdminUserFlag':
                $this->handleAdminToggleFlag();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on user resource'));
        }
    }
}
