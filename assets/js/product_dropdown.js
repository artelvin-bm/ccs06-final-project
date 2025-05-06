document.addEventListener("DOMContentLoaded", () => {
    const productSelect = document.querySelector("select[name='product_id']");
    if (!productSelect) return;

    productSelect.addEventListener("change", function () {
        const productId = this.value;
        if (!productId) {
            document.getElementById("productDetails").classList.add("d-none");
            return;
        }

        fetch("../api/get_product_details.php?id=" + productId)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    document.getElementById("productDetails").classList.add("d-none");
                    return;
                }

                document.getElementById("productImage").src = "../assets/img/" + data.image;
                document.getElementById("productName").textContent = data.name;
                document.getElementById("productDescription").textContent = data.description;
                document.getElementById("productDetails").classList.remove("d-none");
            })
            .catch(() => {
                document.getElementById("productDetails").classList.add("d-none");
            });
    });

    // Auto-select if product_id in URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product_id');
    if (productId) {
        productSelect.value = productId;
        productSelect.dispatchEvent(new Event('change'));
    }
});
