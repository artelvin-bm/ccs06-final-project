document.addEventListener("DOMContentLoaded", () => {
    const stars = document.querySelectorAll('.star');
    const selectedStarsInput = document.getElementById('selectedStars');
    if (!stars.length || !selectedStarsInput) return;

    function highlightStars(rating) {
        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-value'));
            star.style.color = (starRating <= rating) ? '#FFD700' : '#ccc';
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            highlightStars(parseInt(this.getAttribute('data-value')));
        });

        star.addEventListener('mouseout', function () {
            highlightStars(parseInt(selectedStarsInput.value));
        });

        star.addEventListener('click', function () {
            selectedStarsInput.value = this.getAttribute('data-value');
            highlightStars(parseInt(selectedStarsInput.value));
        });
    });
});
