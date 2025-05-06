document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".vote-buttons").forEach(buttonGroup => {
        const reviewId = buttonGroup.dataset.reviewId;

        buttonGroup.querySelector(".like-btn").addEventListener("click", () => {
            sendVote(reviewId, "like", buttonGroup);
        });

        buttonGroup.querySelector(".dislike-btn").addEventListener("click", () => {
            sendVote(reviewId, "dislike", buttonGroup);
        });
    });

    function sendVote(reviewId, voteType, container) {
        fetch("api/vote_review.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `review_id=${reviewId}&vote_type=${voteType}`
        })
        .then(res => res.json())
        .then(data => {
            if (!data.error) {
                container.querySelector(".like-count").textContent = data.likes;
                container.querySelector(".dislike-count").textContent = data.dislikes;
            } else {
                alert(data.error);
            }
        });
    }
});