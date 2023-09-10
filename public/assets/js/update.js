// Delete video via AJAX
document.querySelectorAll('.delete-video').forEach(function (button) {
    button.addEventListener('click', function () {
        var videoId = this.dataset.videoid;
        var csrfToken = this.dataset.csrftoken;
        var targetElementId = this.dataset.target;
        var modalDelete = document.querySelector('#exampleModalVideo' + videoId)
        var modalBackdropDelete = document.querySelector('.modal-backdrop')
        var spinner = document.querySelector('#spinner-' + videoId);

        var xhr = new XMLHttpRequest();
        xhr.open('DELETE', '/video/delete/' + videoId, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken); // Set the CSRF token as a header

        spinner.classList.add('d-inline-block');
        spinner.classList.remove('d-none');

        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.message === 'Video deleted successfully') { // Handle success, e.g., remove the video element from the page
                    var videoElement = document.querySelector(targetElementId);
                    if (videoElement) {
                        videoElement.parentNode.removeChild(videoElement);
                    }
                    // Remove modal
                    modalDelete.parentNode.removeChild(modalDelete);
                    modalBackdropDelete.parentNode.removeChild(modalBackdropDelete)
                    document.querySelector('body').style.overflow = "auto";
                    alert(response.message);

                } else { // Handle failure, e.g., show an error message
                    spinner.classList.add('d-none');
                spinner.classList.remove('d-inline-block');
                    alert('Error: ' + response.message);
                }
            } else { // Handle HTTP error
                spinner.classList.add('d-none');
                spinner.classList.remove('d-inline-block');
                alert('HTTP Error: ' + xhr.status);
            }
        };

        xhr.onerror = function () { // Handle network error
            spinner.classList.add('d-none');
                spinner.classList.remove('d-inline-block');
            alert('Network Error');
        };

        // Send the DELETE request
        xhr.send();
    });
});

// Delete image via AJAX
document.querySelectorAll('.delete-image').forEach(function (button) {
    button.addEventListener('click', function () {
        var imageId = this.dataset.imageid;
        var csrfToken = this.dataset.csrftoken;
        var targetElementId = this.dataset.target;
        var modalDelete = document.querySelector('#exampleModalImg' + imageId)
        var modalBackdropDelete = document.querySelector('.modal-backdrop')
        var spinner = document.querySelector('#spinner-' + imageId);

        var xhr = new XMLHttpRequest();
        xhr.open('DELETE', '/image/delete/' + imageId, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken); // Set the CSRF token as a header

        spinner.classList.add('d-inline-block');
        spinner.classList.remove('d-none');

        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.message === 'Image deleted successfully') { // Handle success, e.g., remove the image element from the page
                    var imageElement = document.querySelector(targetElementId);
                    if (imageElement) {
                        imageElement.parentNode.removeChild(imageElement);
                    }
                    // Remove modal
                    modalDelete.parentNode.removeChild(modalDelete);
                    modalBackdropDelete.parentNode.removeChild(modalBackdropDelete)
                    document.querySelector('body').style.overflow = "auto";
                    alert(response.message);

                } else { // Handle failure, e.g., show an error message
                    spinner.classList.add('d-none');
                    spinner.classList.remove('d-inline-block');
                    alert('Error: ' + response.message);
                }
            } else { // Handle HTTP error
                spinner.classList.add('d-none');
                spinner.classList.remove('d-inline-block');
                alert('HTTP Error: ' + xhr.status);
            }
        };

        xhr.onerror = function () { // Handle network error
            spinner.classList.add('d-none');
            spinner.classList.remove('d-inline-block');
            alert('Network Error');
        };

        // Send the DELETE request
        xhr.send();
    });
});