let changesDetected = false;

function onEditProfileFormInputChange() {
    if (!changesDetected) {
        $('#btnEditProfileSubmit').attr('disabled', false);
        changesDetected = true;
    }
}
$('#formEditProfile input[type=file]').change(onEditProfileFormInputChange);

function onUploadDocumentFormSubmit(event) {
    if (event) event.preventDefault(); // Added preventDefault

    // Capture the form
    let form = new FormData(document.getElementById('formUploadDocument'));

    let bodyDocumentUpload = new FormData();

    let previousUpload = false;
    let newUpload = false;

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
        api.post('/uploads.php', bodyDocumentUpload, true)
            .then(res => {
                snackbar('Successfully updated', 'success');
                $('#btnUploadLoader').hide();
                setTimeout(function() {
                    location.reload();
                }, 1000);
            })
            .catch(err => {
                snackbar(err.message, 'error');
                $('#btnUploadLoader').hide();
            });
    } else if (newUpload) {
        bodyDocumentUpload.append('action', 'uploadDocument');
        api.post('/uploads.php', bodyDocumentUpload, true)
            .then(res => {
                snackbar('Successfully uploaded', 'success');
                $('#btnUploadLoader').hide();
                setTimeout(function() {
                    location.reload();
                }, 1000);
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

// Updated to pass the event implicitly
$('#formUploadDocument').on('submit', onUploadDocumentFormSubmit);


function onUploadDelete(event) {
    if (event) event.preventDefault(); // Added preventDefault

    let bodyDocumentUpload = new FormData();
    const documentType = document.getElementById("documentType");
    const userId = document.getElementById("userId");
    const previousUploadId = document.getElementById("previousUploadId");

    bodyDocumentUpload.append('userId', userId.value);
    bodyDocumentUpload.append('documentType', documentType.value);
    bodyDocumentUpload.append('previousUploadId', previousUploadId.value);

    bodyDocumentUpload.append('action', 'deleteDocument');
    api.post('/uploads.php', bodyDocumentUpload, true)
        .then(res => {
            snackbar('Successfully deleted', 'success');
            $('#btnUploadLoader').hide();
            setTimeout(function() {
                location.reload();
            }, 1000);
        })
        .catch(err => {
            snackbar(err.message, 'error');
            $('#btnUploadLoader').hide();
        });
}

$('#aUploadDelete').on('click', onUploadDelete);

function onUploadDownload(event) {
    if (event) event.preventDefault(); // Added preventDefault

    let bodyDocumentUpload = new FormData();
    const uploadId = document.getElementById("previousUploadId");

    bodyDocumentUpload.append('uploadId', uploadId.value);

    bodyDocumentUpload.append('action', 'downloadDocument');

    api.post('/uploads.php', bodyDocumentUpload, true)
    .then(data => {
        // 1. Parse the double-encoded JSON
        let payload;
        try {
            payload = typeof data.message === 'string' ? JSON.parse(data.message) : data;
        } catch (e) {
            payload = data;
        }

        if (!payload.fileData) {
            alert("Server returned no file data");
            return;
        }

        // 2. Clean & Decode Base64
        const cleanBase64 = payload.fileData.replace(/\s/g, '');
        const binaryString = window.atob(cleanBase64);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }

        // 3. Create Blob (FORCE octet-stream to bypass PDF viewers)
        const blob = new Blob([bytes], { type: 'application/octet-stream' });
        const url = window.URL.createObjectURL(blob);

        // 4. Create the link element
        const a = document.createElement('a');
        a.href = url;
        a.download = payload.filename || "download.pdf";
        
        // TRICK 1: Make it part of the DOM, but invisible
        a.style.display = 'block';
        a.style.position = 'absolute';
        a.style.left = '-9999px';
        document.body.appendChild(a);

        // TRICK 2: Create a genuine mouse event
        // (Zen/Firefox sometimes ignores .click() in promises)
        const clickEvent = new MouseEvent('click', {
            view: window,
            bubbles: true,
            cancelable: true
        });
        a.dispatchEvent(clickEvent);

        // TRICK 3: Long timeout cleanup
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }, 5000); // 5 second wait to ensure Zen's download manager has grabbed it
    })
    .catch(err => {
        console.error("Download Critical Failure:", err);
        alert("Download failed. Check console for details.");
    });
}

$('#aUploadDownload').on('click', onUploadDownload);

/**
 * Handles the Profile Edit Form Submission
 */
function onEditProfileFormSubmit(event) {
    // 1. Prevent default HTML form submission
    if (event) event.preventDefault(); // Already existed, kept consistent

    // 2. Gather data
    // Since we aren't sending files, we create a simple JSON object
    // Users.php expects JSON body, unlike Uploads.php which handles FormData
    let body = {
        action: 'updateUserProfile',
        userId: document.getElementById('userId').value, // Taken from the hidden input in the Upload form
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value
    };

    // 3. Disable button to prevent double-click
    $('#btnEditProfileSubmit').attr('disabled', true);
    
    // 4. Send API Request
    // Note: We send to '/users.php', not '/uploads.php'
    // We do NOT pass 'true' as the 3rd argument because this is JSON, not FormData
    api.post('/users.php', body)
        .then(res => {
            snackbar('Profile updated successfully', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(err => {
            snackbar(err.message, 'error');
            // Re-enable button on error so they can try again
            $('#btnEditProfileSubmit').attr('disabled', false);
        });
}

// 5. Attach the listener
$('#formEditProfile').on('submit', onEditProfileFormSubmit);

/**
 * Handles Permission Toggle Buttons
 */
$('.btn-flag-toggle').on('click', function(event) {
    if (event) event.preventDefault(); // Added preventDefault

    const btn = $(this);
    
    // Prevent double-clicking
    if(btn.prop('disabled')) return;
    btn.prop('disabled', true);

    const flagId = btn.data('flag-id');
    const userId = btn.data('user-id');
    const currentAction = btn.data('action'); // 'add' or 'remove'
    const btnType = btn.data('type'); // 'info' (departments) or 'secondary' (roles)

    // Prepare API Body
    let body = {
        action: 'toggleUserFlag',
        userId: userId,
        flagId: flagId,
        operation: currentAction
    };

    api.post('/users.php', body)
        .then(res => {
            // Success: Flip the styles and action
            if (currentAction === 'add') {
                // Changed from Outline to Solid
                btn.removeClass(`btn-outline-${btnType}`).addClass(`btn-${btnType}`);
                btn.data('action', 'remove');
            } else {
                // Changed from Solid to Outline
                btn.removeClass(`btn-${btnType}`).addClass(`btn-outline-${btnType}`);
                btn.data('action', 'add');
            }
            snackbar('Permissions updated', 'success');
            btn.prop('disabled', false);
        })
        .catch(err => {
            snackbar(err.message, 'error');
            btn.prop('disabled', false);
        });
});