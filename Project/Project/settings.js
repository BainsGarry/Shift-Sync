// settings.js
document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.querySelector('select[name="theme"]');
    themeSelect.addEventListener('change', function() {
        this.form.submit();
    });
});
