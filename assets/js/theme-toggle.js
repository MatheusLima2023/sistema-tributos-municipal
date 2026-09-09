document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const storedTheme = localStorage.getItem('theme') || 'light';

    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-bs-theme', theme);
        if (themeToggleBtn) {
            themeToggleBtn.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
    };

    applyTheme(storedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
    }
});