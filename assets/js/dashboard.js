document.addEventListener("DOMContentLoaded", function() {
    fetch("../api/get_sentiment_chart.php?product_id=" + PRODUCT_FILTER_ID)
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('sentimentChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Positive', 'Negative', 'Neutral'],
                    datasets: [{
                        data: [data.positive, data.negative, data.neutral],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
});
