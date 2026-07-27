/**
 * transaction-form.js
 * Used by modules/transactions/create.php. Toggles the destination
 * account field for Transfers, and submits the form via AJAX to
 * store.php (unchanged — same JSON contract as before).
 */

$(function () {
    const $form = $('#transactionForm');
    if (!$form.length) return;

    const action = $form.data('action');

    $('#transactionTypeSelect').on('change', function () {
        const transfer = $(this).val() === 'Transfer';
        $('#receiverAccountGroup').toggleClass('d-none', !transfer);
        $('#receiverId').prop('required', transfer);
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#transactionSubmit').prop('disabled', true);

        $.post(action, $form.serialize())
            .done(function (res) {
                if (res.success) {
                    cbAlert('success', 'Transaction processed', res.message).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    cbAlert('error', 'Transaction not processed', res.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                cbAlert('error', 'Something went wrong', 'Please try again.');
                $btn.prop('disabled', false);
            });
    });
});