
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

    // Serialize the form elements into JSON (except for the files). The files (if they exist) we will append to
    // a separate request that will use a urlformencoded request body.
    let bodyInfo = {};

    let bodyDocumentUpload = new FormData();

    for (const [key, value] of form.entries()) {
        if (key == 'userUpload' && value.size > 0) {
            bodyDocumentUpload.append(key, value);
            newUpload = true;
        } else {
            bodyInfo[key] = value;
        }
    }

    // Request to upload the profile image if there is one
    if (newUpload) {
        bodyDocumentUpload.append('action', 'uploadDocument');
        bodyDocumentUpload.append('userId', bodyInfo.userId);
        bodyDocumentUpload.append('documentType', bodyInfo.documentType);
        api.post('/user-uploads.php', bodyDocumentUpload, true)
            .then(res => {
                onApiResponse('document', true);
            })
            .catch(err => {
                onApiResponse('document', false);
                snackbar(err.message, 'error');
            });
        pendingProfileImageUpload = true;
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

/**
 * Captures common functionality when an API response is received.
 */
function onApiResponse(type, success) {
    switch (type) {
        case 'document':
            pendingDocumentUpload = false;
    }
    if (!pendingDocumentUpload) {
        $('#formUploadLoader').hide();
        if (!success) {
            $('#btnEditProfileSubmit').attr('disabled', false);
            changesDetected = true;
        } else {
            // Replace the profile image text
            // if (newProfileImageSelected) {
            //     $('#profileImageText').text(`
            //         Current Profile Image
            //     `);
            //     $('#btnProfileImageDelete').show();
            //     newProfileImageSelected = false;
            // }

            // // Replace the resume text
            // if (newResumeSelected) {
            //     $('#resumeText').html(`
            //         You have uploaded a resume.
            //     `);
            //     $('#aResumeDownload').attr('href', $('#userId').val());
            //     $('#resumeActions').show();
            //     newResumeSelected = false;
            // }

            snackbar('Successfully saved profile', 'success');
        }
    }
}