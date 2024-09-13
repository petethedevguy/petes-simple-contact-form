jQuery(document).ready(function($) {
    grecaptcha.ready(function() {
        grecaptcha.execute(recaptchaData.siteKey, {action: 'submit'}).then(function(token) {
            var form = document.getElementById('scf_contact_form');
            var input = document.createElement('input');
            input.setAttribute('type', 'hidden');
            input.setAttribute('name', 'g-recaptcha-response');
            input.setAttribute('value', token);
            form.appendChild(input);
        });
    });
});
