/** Handles the asynchronous transaction list and transaction modal. */
$(function () {
    const $body = $('#transactionsTableBody');
    const modalEl = document.getElementById('transactionModal');
    const modal = new bootstrap.Modal(modalEl);
    let searchTimer;

    const esc = (value) => $('<div>').text(value || '').html();
    const money = (value) => `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    function loadTransactions() {
        $body.html('<tr><td colspan="6" class="text-center text-muted py-4">Loading transactions…</td></tr>');
        $.get('index.php', { type: $('#transactionType').val(), search: $('#transactionSearch').val() })
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $body.html('<tr><td colspan="6" class="text-center text-muted py-4">No transactions found.</td></tr>');
                    return;
                }
                $body.html(res.data.map(function (row) {
                    const date = new Date(row.transaction_date.replace(' ', 'T')).toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
                    return `<tr data-id="${row.transaction_id}">
                        <td class="ps-3 text-muted small cb-mono">${date}</td>
                        <td class="cb-account-number">${esc(row.account_number)}</td>
                        <td>${esc(`${row.first_name} ${row.last_name}`)}</td>
                        <td>${esc(row.transaction_type)}</td>
                        <td class="text-end cb-amount">${money(row.amount)}</td>
                        <td class="text-end pe-3"><button class="btn btn-sm btn-outline-secondary btn-view-transaction" title="View transaction"><i class="bi bi-eye"></i></button></td>
                    </tr>`;
                }).join(''));
            })
            .fail(() => $body.html('<tr><td colspan="6" class="text-center text-danger py-4">Could not load transactions. Please refresh.</td></tr>'));
    }

    function openModal(title, url) {
        $('#transactionModalTitle').text(title);
        $('#transactionModalBody').html('<div class="text-center text-muted py-4">Loading…</div>');
        modal.show();
        $.get(url).done((html) => $('#transactionModalBody').html(html))
            .fail(() => $('#transactionModalBody').html('<p class="text-danger mb-0">Could not load this content. Please try again.</p>'));
    }

    $('#btnNewTransaction').on('click', () => openModal('New Transaction', 'create.php'));
    $body.on('click', '.btn-view-transaction', function () { openModal('Transaction Details', `view.php?id=${$(this).closest('tr').data('id')}`); });
    $('#transactionSearch').on('input', function () { clearTimeout(searchTimer); searchTimer = setTimeout(loadTransactions, 350); });
    $('#transactionType').on('change', loadTransactions);

    $('#transactionModalBody').on('change', '#transactionTypeSelect', function () {
        const transfer = $(this).val() === 'Transfer';
        $('#receiverAccountGroup').toggleClass('d-none', !transfer);
        $('#receiverId').prop('required', transfer);
    });
    $('#transactionModalBody').on('submit', '#transactionForm', function (event) {
        event.preventDefault();
        const $button = $('#transactionSubmit').prop('disabled', true);
        $.post('store.php', $(this).serialize())
            .done(function (res) {
                if (res.success) {
                    modal.hide();
                    cbToast('success', res.message);
                    loadTransactions();
                } else cbAlert('error', 'Transaction not processed', res.message);
            })
            .fail(() => cbAlert('error', 'Something went wrong', 'Please try again.'))
            .always(() => $button.prop('disabled', false));
    });
    loadTransactions();
});
