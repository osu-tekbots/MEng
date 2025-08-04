<?php
include_once '../../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EvaluationUploadsDao;

$usersDao = new UsersDao($dbConn, $logger);
$uploadsDao = new EvaluationUploadsDao($dbConn, $logger);

$uploads = $uploadsDao->getAllUnassignedUploads();

include_once PUBLIC_FILES . '/modules/header.php';


?>

<table class="table table-striped table-hover">
  <thead class="thead-light">
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>Larry</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
