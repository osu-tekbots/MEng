<?php
include_once '../../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EvaluationUploadsDao;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new EvaluationUploadsDao($dbConn, $logger);

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
                <th scope="col">Reviewer(s)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>Mark</td>
                    <td>Otto</td>
                    <td>@mdo</td>
                    <td>
                        <select class="form-select">
    
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
