/**
 * open-account.js
 * Used by modules/users/view.php — submits the Open Account form and
 * reloads the page on success so the new account shows up in the list.
 */

$(function () {
    const $form = $('#openAccountForm');
    if (!$form.length) return;

    $form.on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#openAccountSubmit').prop('disabled', true);

        $.post('open-account.php', $form.serialize())
            .done(function (res) {
                if (res.success) {
                    cbAlert('success', 'Account opened', res.message).then(() => {
                        window.location.reload();
                    });
                } else {
                    cbAlert('error', 'Could not open account', res.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                cbAlert('error', 'Something went wrong', 'Please try again.');
                $btn.prop('disabled', false);
            });
    });
});