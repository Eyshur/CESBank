/**
 * loan-form.js
 * Used by modules/loans/create.php. Submits the application via AJAX
 * to store.php, then redirects back to the loans list on success.
 */

$(function () {
    const $form = $('#loanForm');
    if (!$form.length) return;

    const action = $form.data('action');

    $form.on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#loanFormSubmit').prop('disabled', true);

        $.post(action, $form.serialize())
            .done(function (res) {
                if (res.success) {
                    cbAlert('success', 'Application submitted', res.message).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    cbAlert('error', 'Could not submit application', res.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                cbAlert('error', 'Something went wrong', 'Please try again.');
                $btn.prop('disabled', false);
            });
    });
});