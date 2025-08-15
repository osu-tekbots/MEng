<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\DocumentTypesDao;

$uploadId = isset($_GET['uploadId']) && !empty($_GET['uploadId']) ? $_GET['uploadId'] : false;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$documentTypesDao = new DocumentTypesDao($dbConn, $logger);

$upload = $uploadsDao->getUpload($uploadId);
$uploadUser = $usersDao->getUser($upload->getFkUserId());
$uploadDocumentType = $documentTypesDao->getDocumentType($upload->getFkDocumentType());

include_once PUBLIC_FILES . '/modules/header.php';


?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h2>
                <?php  
                    echo $uploadUser->getFullName() . "'s " . $uploadDocumentType->getTypeName();
                ?>
            </h2>
        </div>
    </div>
    <div class="row">
        <div class="col">

        </div>
    </div>
</div>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
