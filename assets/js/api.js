// Simple object that provides nice, clean functions for making asynchronous HTTP requests to our backend
const api = {
    get: url => request('GET', url),

    post: (url, data = {}, encoded = false) => request('POST', url, data, encoded),

    patch: (url, data = {}, encoded = false) => request('PATCH', url, data, encoded),

    delete: url => request('DELETE', url)
};

function requestBlob(method, url, data) {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
        xhr.open(method, 'api' + url, true);
        
        // CRITICAL: Tell the browser not to parse this as text, but as a binary blob
        xhr.responseType = 'blob'; 

        xhr.onload = function () {
            // Check if the server returned an error (JSON) instead of the file
            // Sometimes we ask for a file, but get a 404 or 403 JSON error back.
            if (this.response.type === 'application/json') {
                const reader = new FileReader();
                reader.onload = function() {
                    try {
                        const errorResponse = JSON.parse(this.result);
                        alert(errorResponse.message || 'Error downloading file');
                        reject(errorResponse);
                    } catch (e) {
                        reject(new Error('Unknown server error'));
                    }
                };
                reader.readAsText(this.response);
                return;
            }

            if (this.status >= 200 && this.status < 300) {
                // It is a success! Create a virtual link to trigger the browser download
                const blob = this.response;
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                
                // Try to get filename from headers, otherwise default to file.pdf
                const disposition = xhr.getResponseHeader('Content-Disposition');
                let fileName = 'file.pdf';
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) { 
                        fileName = matches[1].replace(/['"]/g, '');
                    }
                }
                
                a.download = fileName;
                document.body.appendChild(a); // Required for Firefox
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);
                
                resolve({ success: true });
            } else {
                reject(new Error('Download failed with status: ' + this.status));
            }
        };

        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(data));
    });
}

/**
 * Makes an AJAX request to the API endpoint for the website.
 * 
 * @param {string} method the HTTP method to use in the request (GET, POST, PUT, DELETE)
 * @param {string} url the resource URL location. A base prefix of `api` will be applied to the url.
 * @param {object|FormData|undefined} data the body to send in the request
 * @param {boolean} encoded whether the request body is being sent as `multipart/form-data` or 
 * `application/x-www-form-urlencoded`
 */
function request(method, url, data, encoded) {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
        xhr.onload = function () {
            let data;
            try {        
                data = JSON.parse(this.response);
            } catch(err) {
                alert(err);
				alert(this.response);
				console.log(err);
                reject(new Error('Failed to parse response from server'));
            }
            if (this.status >= 200 && this.status < 300) {
                return resolve(data);
            } else {
                return reject(data);
            }
        };
        xhr.open(method, 'api' + url, true);
        if (data) {
            if (encoded) {
                xhr.send(data);
            } else {
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify(data));
            }
        } else {
            xhr.send();
        }
    });
}

/**
 * Serializes an HTML form as a JSON object.
 * 
 * The `name` attribute values of the `<input>` elements in the form will be the properties of the resulting object.
 * 
 * @param {string} formId the ID of the form element to serialize as JSON
 * @returns {object} the JSON representation of the form
 */
function serializeFormAsJson(formId) {
    let data = {};
    let form = new FormData(document.getElementById(formId));
    for(const [name, value] of form.entries()) {
        data[name] = value;
    }
    return data;
}