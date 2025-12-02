<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;

$usersDao = new UsersDao($dbConn, $logger);

$user = $usersDao->getUser($_SESSION['userID']);
$userFlags = $usersDao->getUserFlags($_SESSION['userID']); 

$roles = [];
$departments = [];

if ($userFlags) {
    foreach ($userFlags as $flag) {
        if ($flag->getFlagType() === 'Role') {
            $roles[] = $flag->getFlagName();
        } elseif ($flag->getFlagType() === 'Department') {
            $departments[] = $flag->getFlagName();
        }
    }
}

include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">
        
        <div class="row mb-4">
            <div class="col">
                <h2>My Profile</h2>
                <p class="text-muted">Manage your personal information and view your system permissions.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="profile.php" method="POST">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="firstName">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" 
                                           value="<?php echo $user->getFirstName(); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" 
                                           value="<?php echo $user->getLastName(); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $user->getEmail(); ?>" required>
                            </div>
                            
                            <hr class="mt-4 mb-4">
                            
                            <button type="submit" name="updateProfile" class="btn btn-primary">
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">System Identifiers</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="text-muted small text-uppercase font-weight-bold">ONID</label>
                            <input type="text" class="form-control-plaintext font-weight-bold" 
                                   value="<?php echo $user->getOnid(); ?>" readonly>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-muted small text-uppercase font-weight-bold">OSU ID</label>
                            <input type="text" class="form-control-plaintext font-weight-bold" 
                                   value="<?php echo $user->getOsuId(); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Permissions & Access</h5>
                    </div>
                    <div class="card-body">
                        
                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">Departments</h6>
                        <div class="mb-3">
                            <?php if (count($departments) > 0): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <span class="badge badge-info p-2 mr-1"><?php echo $dept; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted font-italic">No departments assigned.</span>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <h6 class="text-muted small text-uppercase font-weight-bold mb-2">User Roles</h6>
                        <div>
                            <?php if (count($roles) > 0): ?>
                                <?php foreach ($roles as $role): ?>
                                    <span class="badge badge-secondary p-2 mr-1"><?php echo $role; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted font-italic">No specific roles assigned.</span>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>