<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;

// 2. Setup DAO
$usersDao = new UsersDao($dbConn, $logger);

// 3. Fetch All Users
$users = $usersDao->getAllUsers();

// 4. Fetch All Role Flags (for toggle buttons)
$allRoles = $usersDao->getAllRoleFlags();

include_once PUBLIC_FILES . '/modules/header.php';
?>

<div class="container-fluid">
    <div class="container mt-5 mb-5">
        
        <div class="row mb-4">
            <div class="col">
                <h2>User Management</h2>
                <p class="text-muted">View all registered users, check their roles, and access their profiles.</p>
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
                                    
                                    // Build array of this user's flag IDs for comparison
                                    $userFlagIds = [];
                                    if ($flags && is_array($flags)) {
                                        foreach ($flags as $flag) {
                                            $userFlagIds[] = (string)$flag->getId();
                                        }
                                    }
                                    $minArr = 999;
                                    foreach ($allRoles as $r) { if (in_array($r->getId(), $userFlagIds) && $r->getArrangement() < $minArr) $minArr = $r->getArrangement(); }
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
                                <td class="align-middle" data-order="<?php echo $minArr; ?>">
                                    <?php foreach ($allRoles as $role): ?>
                                        <?php 
                                            $hasFlag = in_array($role->getId(), $userFlagIds);
                                            $btnStyle = $hasFlag ? 'btn-secondary' : 'btn-outline-secondary';
                                            $action = $hasFlag ? 'remove' : 'add';
                                        ?>
                                        <button type="button" 
                                                class="btn btn-sm <?php echo $btnStyle; ?> mb-1 btn-flag-toggle"
                                                data-flag-id="<?php echo $role->getId(); ?>"
                                                data-user-id="<?php echo $u->getId(); ?>"
                                                data-action="<?php echo $action; ?>">
                                            <?php echo $role->getName(); ?>
                                        </button>
                                    <?php endforeach; ?>
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
$("#usersTable").DataTable({
    'scrollX': false,
    'paging': false,
    'ordering': true,
    'info': true,
    'order': [[ 0, "asc" ]],
    "columns": [
        null,
        null,
        null,
        null,
        { "orderable": false }
    ]
});

$('.btn-flag-toggle').on('click', function () {
    const btn = $(this);
    if (btn.prop('disabled')) return;
    btn.prop('disabled', true);

    const action = btn.data('action');

    api.post('/users.php', {
        action: 'toggleAdminUserFlag',
        userId: btn.data('user-id'),
        flagId: btn.data('flag-id'),
        operation: action
    })
    .then(() => {
        btn.toggleClass('btn-secondary btn-outline-secondary');
        btn.data('action', action === 'add' ? 'remove' : 'add');
        snackbar('Permissions updated', 'success');
    })
    .catch(err => snackbar(err.message, 'error'))
    .always(() => btn.prop('disabled', false));
});
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>