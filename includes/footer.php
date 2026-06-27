    <footer>
        <p>&copy; <?php echo date('Y'); ?> Learn & Quiz Platform. All rights reserved.</p>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.addEventListener('click', (e) => {
            const openDropdowns = document.querySelectorAll('.custom-dropdown.active');
            openDropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        });

        const toggles = document.querySelectorAll('.custom-dropdown-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const parent = toggle.closest('.custom-dropdown');
                
                // Close other dropdowns first
                document.querySelectorAll('.custom-dropdown').forEach(d => {
                    if (d !== parent) d.classList.remove('active');
                });
                
                parent.classList.toggle('active');
            });
        });
    });
    </script>
</body>
</html>
