let tricks = document.querySelectorAll('.trick');
let offset = tricks.length;
document.getElementById('loadingSpinner').classList.remove('d-block');
document.getElementById('loadingSpinner').classList.add('d-none');


function loadMoreTricks() { // Show the loading spinner
    document.getElementById('loadingSpinner').classList.remove('d-none');
    document.getElementById('loadingSpinner').classList.add('d-block');
    tricks = document.querySelectorAll('.trick');

    var offset = tricks.length

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/' + offset, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onload = function () {
        if (xhr.status === 200) {
            // Handle the response here
            console.log(xhr.responseText);
            document.getElementById('loadingSpinner').classList.add('d-none');
            document.getElementById('loadingSpinner').classList.remove('d-block');
            const tricksContainer = document.getElementById('tricksContainer');
            tricksContainer.innerHTML += xhr.responseText;

            if (xhr.responseText === "") {
                document.getElementById('loadMoreButton').disabled = true;
            }
        } else { // Handle HTTP error
            console.log(xhr.status);
            document.getElementById('loadingSpinner').classList.add('d-none');
            document.getElementById('loadingSpinner').classList.remove('d-block');
        }
    };

    xhr.send();
}

document.getElementById('loadMoreButton').addEventListener('click', loadMoreTricks);




// Delete trick via AJAX
document.querySelectorAll('.delete-trick').forEach(function (button) {
    button.addEventListener('click', function () {
        var trickSlug = this.dataset.trickslug;
        var trickId = this.dataset.trickid;
        var csrfToken = this.dataset.csrftoken;
        var targetElementId = this.dataset.target;
        var modalDelete = document.querySelector('#exampleModal' + trickId)
        var modalBackdropDelete = document.querySelector('.modal-backdrop')
        var spinner = document.querySelector('#spinner-' + trickSlug);

        var xhr = new XMLHttpRequest();
        xhr.open('DELETE', '/tricks/delete/' + trickSlug, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken); // Set the CSRF token as a header

        spinner.classList.add('d-inline-block');
        spinner.classList.remove('d-none');

        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.message === 'Trick deleted successfully') { // Handle success, e.g., remove the image element from the page
                    var trickElement = document.querySelector(targetElementId);
                    if (trickElement) {
                        trickElement.parentNode.removeChild(trickElement);
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