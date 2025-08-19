<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;

$usersDao = new UsersDao($dbConn, $logger);

include_once PUBLIC_FILES . '/modules/header.php';


?>



<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
