<?php
/**
 * This api endpoint uploads new file to the server.
 */

include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use Model\Upload;


/**
 * Simple function that allows us to respond with a response code and a message inside a JSON object.
 *
 * @param int  $code the HTTP status code of the response
 * @param string $message the message to send back to the client
 * @return void
 */
function respond($code, $message) {
    header('Content-Type: application/json');
    header("X-PHP-Response-Code: $code", true, $code);
    echo '{"message": "' . $message . '"}';
    die();
}

// Verify the action on the resource
if (!isset($_POST['action'])) {
    respond(400, 'Missing action in request body');
}

// Make sure we have the user ID
$userId = isset($_POST['userId']) && !empty($_POST['userId']) ? $_POST['userId'] : null;
if (empty($userId)) {
    respond(400, 'Must include ID of user in request');
}

// Make sure we have the document type
$documentType = isset($_POST['documentType']) && !empty($_POST['documentType']) ? $_POST['documentType'] : null;
if (empty($documentType)) {
    respond(400, 'Must include document type in request');
}

// Make sure the current user has permission to perform this action
$usersDao = new UsersDao($dbConn, $logger);
$user = $usersDao->getUser($userId);    
if (!$user || !$isLoggedIn || ($userId != $_SESSION['userID'])) {
    respond(401, 'You do not have permission to make this request');
}

$uploadsDao = new UploadsDao($dbConn, $logger);

// Construct the path
mkdir($configManager->get('server.upload_file_path') . "/$userId" . "/$documentType", 0777, true); 

$filepath = 
    $configManager->get('server.upload_file_path') . 
    "/$userId" .
    "/$documentType" .
    "/";

switch ($_POST['action']) {

    case 'uploadDocument';

        // Make sure we have a file
        if (!isset($_FILES['userUpload'])) {
            respond(400, 'Must include file in upload request');
        }

        // Get the information we need
        $fileName = $_FILES['userUpload']['name'];
        $fileSize = $_FILES['userUpload']['size'];
        $fileTmpName  = $_FILES['userUpload']['tmp_name'];

        // Check the file size
        $tenMb = 10485760;
        if ($fileSize > $tenMb) {
            respond(400, 'File size must be smaller than 10MB');
        }

        // Check the mime type
        $mime = mime_content_type($fileTmpName);
        if ($mime != 'application/pdf') {
            respond(400, 'File must be a pdf');
        }

        //
        // We've passed all the checks, now we can upload the image
        //

        $upload = new Upload();
        $upload->setFkUserId($userId)
            ->setFkDocumentType($documentType)
            ->setFilePath("/" . $userId . "/" . $documentType . "/")
            ->setFileName($fileName)
            ->setDateUploaded(date('Y-m-d H:i:s'));

        $ok = $uploadsDao->addNewUpload($upload);

        if (!$ok) {
            respond(500, 'Failed to create document database object ');
        }

        $ok = move_uploaded_file($fileTmpName, $filepath . $upload->getId() . ".pdf");

        if (!$ok) {
            respond(500, 'Failed to upload document ' . $fileTmpName . " " . $filepath);
        }

        respond(200, 'Successfully uploaded document');
    
    case 'updateDocument';

        // Make sure we have a file
        if (!isset($_FILES['userUpload'])) {
            respond(400, 'Must include file in upload request');
        }

        // Get the information we need
        $fileName = $_FILES['userUpload']['name'];
        $fileSize = $_FILES['userUpload']['size'];
        $fileTmpName  = $_FILES['userUpload']['tmp_name'];

        // Check the file size
        $tenMb = 10485760;
        if ($fileSize > $tenMb) {
            respond(400, 'File size must be smaller than 10MB');
        }

        // Check the mime type
        $mime = mime_content_type($fileTmpName);
        if ($mime != 'application/pdf') {
            respond(400, 'File must be a pdf');
        }

        //
        // We've passed all the checks, now we can upload the image
        //

        $previousUploadId = isset($_POST['previousUploadId']) && !empty($_POST['previousUploadId']) ? $_POST['previousUploadId'] : null;
        $previousUpload = $uploadsDao->getUpload($previousUploadId);
        $previousUpload->setFileName($fileName);
        $uploadsDao->updateUpload($previousUpload);

        $ok = move_uploaded_file($fileTmpName, $filepath . $previousUploadId . ".pdf");

        if (!$ok) {
            respond(500, 'Failed to upload document ' . $fileTmpName . " " . $filepath);
        }

        respond(200, 'Successfully uploaded document');
        
    case 'deleteDocument':

        $ok = unlink($filepath);
        if (!$ok) {
            respond(500, 'Failed to delete document');
        }

        $previousUploadId = isset($_POST['previousUploadId']) && !empty($_POST['previousUploadId']) ? $_POST['previousUploadId'] : null;
        $ok = $uploadsDao->deleteUpload($previousUploadId);
        if (!$ok) {
            $logger->warning('Document was deleted, but inserting metadata into the database failed');
            respond(500, 'Failed to delete document properly');
        }

        respond(200, 'Successfully deleted document');

    default:
        respond(400, 'Invalid action on document resource');
}
