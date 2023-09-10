let comments = document.querySelectorAll('.comment');
document.getElementById('loadingSpinner').classList.remove('d-block');
document.getElementById('loadingSpinner').classList.add('d-none');

function loadMoreComments() { // Show the loading spinner
    document.getElementById('loadingSpinner').classList.remove('d-none');
    document.getElementById('loadingSpinner').classList.add('d-block');
    comments = document.querySelectorAll('.comment');
    var offset = comments.length;
    var slug = this.dataset.trickslug;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/tricks/' + slug + '/' + offset, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onload = function () {
        if (xhr.status === 200) {
            // Handle the response here
            console.log(xhr.responseText);
            document.getElementById('loadingSpinner').classList.remove('d-block');
            document.getElementById('loadingSpinner').classList.add('d-none');
            const commentsContainer = document.getElementById('commentsContainer');
            commentsContainer.innerHTML += xhr.responseText;

            if (xhr.responseText === "" || offset > 10) {
                document.getElementById('loadMoreButton').disabled = true;
            }
        } else { // Handle HTTP error
            console.log(xhr.status);
            document.getElementById('loadingSpinner').classList.remove('d-block');
            document.getElementById('loadingSpinner').classList.add('d-none');
        }
    };

    xhr.send();
}

document.getElementById('loadMoreButton').addEventListener('click', loadMoreComments);