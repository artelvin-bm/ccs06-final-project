document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("reviewForm");

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch("../api/submit_review_ajax.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                // Display Sentiment
                document.getElementById("reviewResult").innerHTML = `
                    <p>Sentiment: <strong>${data.sentiment}</strong></p>
                    <p>Positive Words: ${data.details.positive}</p>
                    <p>Negative Words: ${data.details.negative}</p>
                `;

                // Show success message
                const successMessage = document.createElement("div");
                successMessage.classList.add("alert", "alert-success", "mt-3");
                successMessage.textContent = "Your review has been submitted successfully!";
                document.querySelector(".container").prepend(successMessage);

                form.reset(); // Clear the form

                // Reset stars
                document.getElementById('selectedStars').value = '';
                const starElems = document.querySelectorAll('.star');
                starElems.forEach(star => {
                    star.style.color = '#ccc';
                });

                // Hide product details
                const productDetails = document.getElementById("productDetails");
                const productImage = document.getElementById("productImage");
                const productName = document.getElementById("productName");
                const productDescription = document.getElementById("productDescription");

                productDetails.classList.add("d-none");
                productImage.src = "";
                productName.textContent = "";
                productDescription.textContent = "";
            });
        });
    }
});
