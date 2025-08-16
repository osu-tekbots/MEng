<?php
namespace Api;

use Model\Upload;

/**
 * Defines the logic for how to handle AJAX requests made to modify upload information.
 */
class UploadActionHandler extends ActionHandler {

    /** @var \DataAccess\UploadsDao */
    private $uploadsDao;

    /** @var \DataAccess\UsersDao */
    private $usersDao;

    /** @var \Util\ConfigManager */
    private $configManager;

    /**
     * Constructs a new instance of the action handler for requests on upload resources.
     *
     * @param \DataAccess\UploadsDao $dao the data access object for uploads
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($uploadsDao, $usersDao, $configManager, $logger) {
        parent::__construct($logger);
        $this->uploadsDao = $uploadsDao;
        $this->usersDao = $usersDao;
        $this->configManager = $configManager;
    }

    /**
     * Uploads user document to site
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleUploadDocument() {
        // Make sure we have the user ID
        $userId = isset($_POST['userId']) && !empty($_POST['userId']) ? $_POST['userId'] : null;
        if (empty($userId)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include ID of user in request'));
        }

        // Make sure we have the document type
        $documentType = isset($_POST['documentType']) && !empty($_POST['documentType']) ? $_POST['documentType'] : null;
        if (empty($documentType)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include document type in request'));
        }

        // Construct the path
        mkdir($this->configManager->get('server.upload_file_path') . "/$userId" . "/$documentType", 0777, true); 

        $filepath = 
            $this->configManager->get('server.upload_file_path') . 
            "/$userId" .
            "/$documentType" .
            "/";
        
        // Make sure we have a file
        if (!isset($_FILES['userUpload'])) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include file in upload request'));
        }

        // Get the information we need
        $fileName = $_FILES['userUpload']['name'];
        $fileSize = $_FILES['userUpload']['size'];
        $fileTmpName  = $_FILES['userUpload']['tmp_name'];

        // Check the file size
        $tenMb = 10485760;
        if ($fileSize > $tenMb) {
            $this->respond(new Response(Response::BAD_REQUEST, 'File size must be smaller than 10MB'));
        }

        // Check the mime type
        $mime = mime_content_type($fileTmpName);
        if ($mime != 'application/pdf') {
            $this->respond(new Response(Response::BAD_REQUEST, 'File must be a pdf'));
        }

        //
        // We've passed all the checks, now we can upload the image
        //

        $upload = new Upload();
        $upload->setFkUserId($userId)
            ->setFilePath("/" . $userId . "/" . $documentType . "/")
            ->setFileName($fileName)
            ->setDateUploaded(date('Y-m-d H:i:s'));

        $ok = $this->uploadsDao->addNewUpload($upload);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create document database object'));
        }

        $ok = $this->uploadsDao->assignUploadFlag($upload->getId(), $documentType);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to assign flag to document database object'));
        }

        $ok = move_uploaded_file($fileTmpName, $filepath . $upload->getId() . ".pdf");
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to upload document'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully uploaded document'
        ));
    }

    /**
     * Updates a user document on the site
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleUpdateDocument() {
        // Make sure we have the user ID
        $userId = isset($_POST['userId']) && !empty($_POST['userId']) ? $_POST['userId'] : null;
        if (empty($userId)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include ID of user in request'));
        }

        // Make sure we have the document type
        $documentType = isset($_POST['documentType']) && !empty($_POST['documentType']) ? $_POST['documentType'] : null;
        if (empty($documentType)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include document type in request'));
        }

        // Make sure we have the previous upload ID
        $previousUploadId = isset($_POST['previousUploadId']) && !empty($_POST['previousUploadId']) ? $_POST['previousUploadId'] : null;
        if (empty($previousUploadId)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include previous upload id in request'));
        }

        $filepath = 
            $this->configManager->get('server.upload_file_path') . 
            "/$userId" .
            "/$documentType" .
            "/";
        
        // Make sure we have a file
        if (!isset($_FILES['userUpload'])) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include file in upload request'));
        }

        // Get the information we need
        $fileName = $_FILES['userUpload']['name'];
        $fileSize = $_FILES['userUpload']['size'];
        $fileTmpName  = $_FILES['userUpload']['tmp_name'];

        // Check the file size
        $tenMb = 10485760;
        if ($fileSize > $tenMb) {
            $this->respond(new Response(Response::BAD_REQUEST, 'File size must be smaller than 10MB'));
        }

        // Check the mime type
        $mime = mime_content_type($fileTmpName);
        if ($mime != 'application/pdf') {
            $this->respond(new Response(Response::BAD_REQUEST, 'File must be a pdf'));
        }

        $previousUpload = $this->uploadsDao->getUpload($previousUploadId);
        $previousUpload->setFileName($fileName);

        $ok = $this->uploadsDao->updateUpload($previousUpload);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update document database object'));
        }

        $ok = move_uploaded_file($fileTmpName, $filepath . $previousUploadId . ".pdf");
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update document'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully updated document'
        ));
    }

    /**
     * Deletes a user document on the site
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleDeleteDocument() {
        // Make sure we have the user ID
        $userId = isset($_POST['userId']) && !empty($_POST['userId']) ? $_POST['userId'] : null;
        if (empty($userId)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include ID of user in request'));
        }

        // Make sure we have the document type
        $documentType = isset($_POST['documentType']) && !empty($_POST['documentType']) ? $_POST['documentType'] : null;
        if (empty($documentType)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include document type in request'));
        }

        // Make sure we have the previous upload ID
        $previousUploadId = isset($_POST['previousUploadId']) && !empty($_POST['previousUploadId']) ? $_POST['previousUploadId'] : null;
        if (empty($previousUploadId)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Must include previous upload id in request'));
        }

        $filepath = 
            $this->configManager->get('server.upload_file_path') . 
            "/$userId" .
            "/$documentType" .
            "/";
        
        $ok = unlink($filepath . $previousUploadId . ".pdf");
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete document'));
        }

        $ok = $this->uploadsDao->deleteUpload($previousUploadId);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete document properly'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully deleted document'
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
        if (!isset($_POST['action'])) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Missing action in request body'));
        }

        // Call the correct handler based on the action
        switch ($_POST['action']) {

            case 'uploadDocument':
                $this->handleUploadDocument();
            
            case 'updateDocument':
                $this->handleUpdateDocument();
                
            case 'deleteDocument':
                $this->handleDeleteDocument();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on upload resource'));
        }
    }
}
