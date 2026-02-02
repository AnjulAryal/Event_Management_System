// assets/js/main.js
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('live-search');
    const container = document.getElementById('event-container');

    if (searchInput && container) {
        let timeout = null;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            const query = e.target.value;

            timeout = setTimeout(() => {
                fetch(`search.php?q=${encodeURIComponent(query)}&ajax=1`)
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                    })
                    .catch(err => console.error('Error fetching search results:', err));
            }, 300); // Debounce search
        });
    }
});
