
let changesDetected = false;
function onEditProfileFormInputChange() {
    if (!changesDetected) {
        $('#btnEditProfileSubmit').attr('disabled', false);
        changesDetected = true;
    }
}
$('#formEditProfile input[type=file]').change(onEditProfileFormInputChange);

function onUploadDocumentFormSubmit() {
    // Capture the form
    let form = new FormData(document.getElementById('formUploadDocument'));

    let bodyDocumentUpload = new FormData();

    previousUpload = false;
    newUpload = false;

    for (const [key, value] of form.entries()) {
        if (key == 'userUpload' && value.size > 0) {
            bodyDocumentUpload.append(key, value);
            newUpload = true;
        } else if (key == 'previousUploadId') {
            bodyDocumentUpload.append(key, value);
            previousUpload = true;
        } else {
            bodyDocumentUpload.append(key, value);
        }
    }

    const documentType = document.getElementById("documentType");
    const userId = document.getElementById("userId");

    bodyDocumentUpload.append('userId', userId.value);
    bodyDocumentUpload.append('documentType', documentType.value);

    // Request to upload the profile image if there is one
    if (previousUpload) {
        bodyDocumentUpload.append('action', 'updateDocument');
        api.post('/user-uploads.php', bodyDocumentUpload, true)
            .then(res => {
                snackbar('Successfully updated', 'success');
                $('#btnUploadLoader').hide();
                setTimeout(function() { location.reload(); }, 1000);
            })
            .catch(err => {
                snackbar(err.message, 'error');
                $('#btnUploadLoader').hide();
            });
    } else if (newUpload) {
        bodyDocumentUpload.append('action', 'uploadDocument');
        api.post('/user-uploads.php', bodyDocumentUpload, true)
            .then(res => {
                snackbar('Successfully uploaded', 'success');
                $('#btnUploadLoader').hide();
                setTimeout(function() { location.reload(); }, 1000);
            })
            .catch(err => {
                snackbar(err.message, 'error');
                $('#btnUploadLoader').hide();
            });
    }

    $('#btnUploadSubmit').attr('disabled', true);
    changesDetected = false;
    $('#btnUploadLoader').show();
    return false;
}

$('#formUploadDocument').on('submit', onUploadDocumentFormSubmit);
document.getElementById('formUploadDocument').addEventListener('submit', function(event) {
    event.preventDefault();
});
