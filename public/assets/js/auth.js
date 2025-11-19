// Validation du formulaire d'inscription
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="/register"]');
    if (!form) return;

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password-confirm');

    // Validation à la soumission
    form.addEventListener('submit', function (e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas');
            confirmPassword.focus();
            return false;
        }
    });

    // Validation en temps réel du mot de passe
    if (password) {
        password.addEventListener('input', function () {
            const value = password.value;

            if (value.length >= 8 &&
                /[A-Z]/.test(value) &&
                /[a-z]/.test(value) &&
                /[0-9]/.test(value)) {
                password.classList.add('is-valid');
                password.classList.remove('is-invalid');
            } else {
                password.classList.add('is-invalid');
                password.classList.remove('is-valid');
            }
        });
    }
});