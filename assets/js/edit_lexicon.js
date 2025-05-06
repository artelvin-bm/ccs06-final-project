document.addEventListener('DOMContentLoaded', function () {
    const searchBox = document.getElementById('searchBox');
    const wordItems = document.querySelectorAll('.word-item');
    const actionForm = document.getElementById('actionForm');
    const originalWordInput = document.getElementById('originalWordInput');
    const editWordInput = document.getElementById('editWordInput');
    const actionTypeInput = document.getElementById('actionTypeInput');
    const deleteBtn = document.getElementById('deleteBtn');
    const editBtn = document.getElementById('editBtn');
    const saveEditBtn = document.getElementById('saveEditBtn');

    let currentlySelected = null;

    function unselectCurrent() {
        if (currentlySelected) {
            currentlySelected.classList.remove('active');
            currentlySelected = null;
        }
        originalWordInput.value = '';
        editWordInput.value = '';
        editWordInput.classList.add('d-none');
        saveEditBtn.classList.add('d-none');
        editBtn.classList.remove('d-none');
        actionForm.classList.add('d-none');
    }

    if (searchBox) {
        searchBox.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            wordItems.forEach(item => {
                const word = item.querySelector('.word-text').textContent.toLowerCase();
                item.style.display = word.includes(query) ? '' : 'none';
            });
            unselectCurrent(); // unselect during search
        });
    }

    wordItems.forEach(item => {
        item.addEventListener('click', function () {
            if (currentlySelected === this) {
                unselectCurrent();
            } else {
                unselectCurrent();
                this.classList.add('active');
                currentlySelected = this;

                const selectedWord = this.dataset.word;
                originalWordInput.value = selectedWord;
                editWordInput.value = selectedWord;

                actionForm.classList.remove('d-none');
            }
        });
    });

    deleteBtn.addEventListener('click', function (e) {
        if (!confirm("Are you sure you want to delete this word?")) {
            e.preventDefault();
        } else {
            actionTypeInput.value = 'delete';
        }
    });

    editBtn.addEventListener('click', function () {
        editWordInput.classList.remove('d-none');
        saveEditBtn.classList.remove('d-none');
        editBtn.classList.add('d-none');
        actionTypeInput.value = 'edit';
    });
});
