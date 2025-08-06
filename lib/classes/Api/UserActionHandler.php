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
     * Changes the $_SESSION variable 'userType'
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function changeUserType() {
        // Make sure the required parameters exit
        $userType = $this->getFromBody('userType');

        $_SESSION['userType'] = $userType;

        $this->respond(new Response(
            Response::OK,
            'Successfully swapped user types'
        ));
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

            case 'changeUserType':
                $this->changeUserType();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on user resource'));
        }
    }
}
