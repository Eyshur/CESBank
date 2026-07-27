/**
 * loan-actions.js
 * Used by modules/loans/view.php — Accept/Decline buttons for staff,
 * only present in the DOM when the loan is still Pending.
 */

$(function () {
    const $card = $('#loanDetailCard');
    if (!$card.length) return;

    const loanId = $card.data('loan-id');

    function processStatus(status, verb) {
        cbConfirm(`${verb} this loan application?`, '').then((ok) => {
            if (!ok) return;

            $.post('status.php', { loan_id: loanId, status: status })
                .done(function (res) {
                    if (res.success) {
                        cbToast('success', res.message);
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        cbAlert('error', 'Could not update application', res.message);
                    }
                })
                .fail(function () {
                    cbAlert('error', 'Something went wrong', 'Please try again.');
                });
        });
    }

    $('#btnAcceptLoan').on('click', () => processStatus('Accepted', 'Accept'));
    $('#btnDeclineLoan').on('click', () => processStatus('Declined', 'Decline'));
});