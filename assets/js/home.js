

function onChangeUserType() {
    let userType = $("#changeUserType").val();
    let body = {
        action: 'changeUserType',
        userType: userType
    };

    api.post('/users.php', body)
        .then(res => {
            snackbar(res.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });

    return false;
}