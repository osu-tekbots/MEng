<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\DocumentTypesDao;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$documentTypesDao = new DocumentTypesDao($dbConn, $logger);

$uploads = $uploadsDao->getAllUnassignedUploads();

include_once PUBLIC_FILES . '/modules/header.php';

?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h2>Unassigned Uploads</h2>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-striped table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Uploader</th>
                    <th scope="col">Document Type</th>
                    <th scope="col">Date Uploaded</th>
                    <!-- <th scope="col">Reviewer(s)</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        foreach ($uploads as $upload) {
                            echo '<tr onclick="goToUploadPage(\'' . $upload->getId() . '\')">';
                            echo '<th scope="row">' . $upload->getId() . '</th>';
                            $uploader = $usersDao->getUser($upload->getFkUserId());
                            echo '<td>' . $uploader->getFullName() . '</td>';
                            $documentType = $documentTypesDao->getDocumentType($upload->getFkDocumentType());
                            echo '<td>' . $documentType->getTypeName() . '</td>';
                            echo '<td>' . $upload->getDateUploaded() . '</td>';
                            // <!-- <td>
                            //     <select class="form-select">
            
                            //     </select>
                            // </td> -->
                            echo '</tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function goToUploadPage(uploadId) {
        window.location.replace("/upload.php?uploadId=" + uploadId);
    }
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
