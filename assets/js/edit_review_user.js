document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll('.star');
    const selectedStarsInput = document.getElementById('selectedStars');

    if (!stars.length || !selectedStarsInput) return;

    // Highlight based on initial value
    highlightStars(parseInt(selectedStarsInput.value));

    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            highlightStars(parseInt(this.dataset.value));
        });

        star.addEventListener('mouseout', function () {
            highlightStars(parseInt(selectedStarsInput.value));
        });

        star.addEventListener('click', function () {
            const rating = parseInt(this.dataset.value);
            selectedStarsInput.value = rating;
            highlightStars(rating);
        });
    });

    function highlightStars(rating) {
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            star.style.color = starValue <= rating ? '#FFD700' : '#ccc';
        });
    }
});
