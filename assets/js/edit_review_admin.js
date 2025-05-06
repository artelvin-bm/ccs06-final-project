const stars = document.querySelectorAll('.star');
const selectedStarsInput = document.getElementById('selectedStars');
highlightStars(parseInt(selectedStarsInput.value));

stars.forEach(star => {
    star.addEventListener('mouseover', () => {
        highlightStars(parseInt(star.dataset.value));
    });

    star.addEventListener('mouseout', () => {
        highlightStars(parseInt(selectedStarsInput.value));
    });

    star.addEventListener('click', () => {
        const rating = parseInt(star.dataset.value);
        selectedStarsInput.value = rating;
        highlightStars(rating);
    });
});

function highlightStars(rating) {
    stars.forEach(star => {
        star.style.color = parseInt(star.dataset.value) <= rating ? '#FFD700' : '#ccc';
    });
}