// maintains theme throughout different pages once dark mode or light mode is selected
document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.querySelector('select[name="theme"]');
    themeSelect.addEventListener('change', function() {
        this.form.submit();
    });
});
