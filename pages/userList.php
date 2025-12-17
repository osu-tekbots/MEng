<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;

// 2. Setup DAO
$usersDao = new UsersDao($dbConn, $logger);

// 3. Fetch All Users
$users = $usersDao->getAllUsers();

include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">
        
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2>User Management</h2>
                <p class="text-muted">View all registered users, check their roles, and access their profiles.</p>
            </div>
            <div class="col-md-4">
                 <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="userSearch" class="form-control" placeholder="Search by name or email...">
                 </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0" id="usersTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Assigned Roles</th>
                                <th>Last Login</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($users):
                                foreach ($users as $u): 
                                    // Fetch specific permissions for this user
                                    $flags = $usersDao->getUserFlags($u->getId());
                                    $roles = [];
                                    
                                    // Filter just the "Role" type flags (ignoring Departments for this column)
                                    if ($flags) {
                                        foreach ($flags as $flag) {
                                            if ($flag->getType() == 'Role') {
                                                $roles[] = $flag->getName();
                                            }
                                        }
                                    }
                            ?>
                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <?php echo $u->getLastName() . ', ' . $u->getFirstName(); ?>
                                </td>
                                <td class="align-middle">
                                    <a href="mailto:<?php echo $u->getEmail(); ?>" class="text-dark">
                                        <?php echo $u->getEmail(); ?>
                                    </a>
                                </td>
                                <td class="align-middle">
                                    <?php if (count($roles) > 0): ?>
                                        <?php foreach ($roles as $role): ?>
                                            <span class="badge bg-secondary"><?php echo $role; ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small font-italic">No Roles</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php 
                                        $lastLogin = $u->getLastLogin();
                                        if ($lastLogin instanceof DateTime) {
                                            echo $lastLogin->format('M d, Y');
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                    ?>
                                </td>
                                <td class="align-middle text-right">
                                    <a href="profile.php?userId=<?php echo $u->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-user-edit mr-1"></i> Manage Profile
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endforeach; 
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center p-4">No users found in the database.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Simple client-side search to filter table rows
 */
document.getElementById('userSearch').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let tableRows = document.querySelectorAll('#usersTable tbody tr');

    tableRows.forEach(row => {
        // We get the text content of the row (Name, Email, Roles)
        let text = row.innerText.toLowerCase();
        if(text.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>