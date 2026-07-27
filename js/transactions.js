/**
 * transactions.js
 * Drives the Transactions index table: loads rows via AJAX, wires up
 * search/filter. New Transaction and View are plain page links
 * (create.php, view.php) — no modal involved.
 */

$(function () {
    const $body = $('#transactionsTableBody');
    let searchTimer;

    const esc = (value) => $('<div>').text(value || '').html();
    const money = (value) => `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    function loadTransactions() {
        $body.html('<tr><td colspan="6" class="text-center text-muted py-4">Loading transactions…</td></tr>');

        $.get('index.php', {
            type: $('#transactionType').val(),
            search: $('#transactionSearch').val(),
        })
        .done(function (res) {
            if (!res.success || !res.data.length) {
                $body.html('<tr><td colspan="6" class="text-center text-muted py-4">No transactions found.</td></tr>');
                return;
            }

            $body.html(res.data.map(function (row) {
                const date = new Date(row.transaction_date.replace(' ', 'T')).toLocaleString('en-PH', {
                    dateStyle: 'medium', timeStyle: 'short',
                });

                return `<tr data-id="${row.transaction_id}">
                    <td class="ps-3 text-muted small cb-mono">${date}</td>
                    <td class="cb-account-number">${esc(row.account_number)}</td>
                    <td>${esc(`${row.first_name} ${row.last_name}`)}</td>
                    <td>${esc(row.transaction_type)}</td>
                    <td class="text-end cb-amount">${money(row.amount)}</td>
                    <td class="text-end pe-3">
                        <a href="view.php?id=${row.transaction_id}" class="btn btn-sm btn-outline-secondary" title="View transaction">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>`;
            }).join(''));
        })
        .fail(function () {
            $body.html('<tr><td colspan="6" class="text-center text-danger py-4">Could not load transactions. Please refresh.</td></tr>');
        });
    }

    $('#transactionSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadTransactions, 350);
    });

    $('#transactionType').on('change', loadTransactions);

    loadTransactions();
});