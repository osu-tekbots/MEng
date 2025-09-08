<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\UploadsDao;
use DataAccess\DocumentTypesDao;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new UploadsDao($dbConn, $logger);
$documentTypesDao = new DocumentTypesDao($dbConn, $logger);

$uploads = $uploadsDao->getAllUnassignedUploads();

require_once PUBLIC_FILES . '/lib/osu-identities-api.php';

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
                    <th scope="col">Assign Reviewer(s)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        foreach ($uploads as $upload) {
                            echo '<tr>';
                            echo '<th scope="row">' . $upload->getId() . '</th>';
                            $uploader = $usersDao->getUser($upload->getFkUserId());
                            echo '<td>' . $uploader->getFullName() . '</td>';
                            $documentTypeFlag = $uploadsDao->getDocumentType($upload->getId());
                            echo '<td>' . $documentTypeFlag->getFlagName() . '</td>';
                            echo '<td>' . $upload->getDateUploaded() . '</td>';
                            echo '<td><input class="form-check-input" type="checkbox" id="flexCheckIndeterminate"></td>';
                            echo '</tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <label for="fruits">Fruits</label>
        <select id="fruits" name="fruits" data-placeholder="Select fruits" multiple data-multi-select>
            <option value="Apple">Apple</option>
            <option value="Banana">Banana</option>
            <option value="Blackberry">Blackberry</option>
            <option value="Blueberry">Blueberry</option>
            <option value="Cherry">Cherry</option>
            <option value="Cranberry">Cranberry</option>
            <option value="Grapes">Grapes</option>
            <option value="Kiwi">Kiwi</option>
            <option value="Mango">Mango</option>
            <option value="Orange">Orange</option>
            <option value="Peach">Peach</option>
            <option value="Pear">Pear</option>
            <option value="Pineapple">Pineapple</option>
            <option value="Raspberry">Raspberry</option>
            <option value="Strawberry">Strawberry</option>
            <option value="Watermelon">Watermelon</option>
        </select>
    </div>
</div>

<script>
    document.querySelectorAll('[data-multi-select]').forEach(select => new MultiSelect(select));
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
