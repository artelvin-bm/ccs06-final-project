document.getElementById('categoryFilter').addEventListener('change', function () {
    const categoryId = this.value;
    window.location.href = categoryId ? `manage_products.php?category_id=${categoryId}` : 'manage_products.php';
});