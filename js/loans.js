/**
 * loans.js
 * Drives the Loans index table: loads rows via AJAX, wires up the
 * status filter. View is a plain page link (view.php) — no modal.
 */

$(function () {
    const $body = $('#loansTableBody');
    const isStaff = window.CB_IS_STAFF === true;
    const colspan = isStaff ? 7 : 6;

    const esc = (value) => $('<div>').text(value || '').html();
    const money = (value) => `₱${Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    function statusBadgeClass(status) {
        return { Pending: 'pending', Accepted: 'active', Declined: 'closed' }[status] || 'pending';
    }

    function loadLoans() {
        $body.html(`<tr><td colspan="${colspan}" class="text-center text-muted py-4">Loading loans…</td></tr>`);

        $.get('index.php', { status: $('#loanStatus').val() })
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $body.html(`<tr><td colspan="${colspan}" class="text-center text-muted py-4">No loan applications found.</td></tr>`);
                    return;
                }

                $body.html(res.data.map(function (row) {
                    const applied = new Date(row.application_date).toLocaleDateString('en-US', {
                        year: 'numeric', month: 'short', day: 'numeric',
                    });
                    const customerCell = isStaff
                        ? `<td>${esc(`${row.first_name} ${row.last_name}`)}</td>`
                        : '';

                    return `<tr data-id="${row.loan_id}">
                        <td class="ps-3 text-muted small cb-mono">${applied}</td>
                        ${customerCell}
                        <td class="text-end cb-amount">${money(row.loan_amount)}</td>
                        <td>${Number(row.interest_rate).toFixed(2)}%</td>
                        <td>${row.loan_term} mo.</td>
                        <td><span class="badge rounded-pill badge-status-${statusBadgeClass(row.status)}">${row.status}</span></td>
                        <td class="text-end pe-3">
                            <a href="view.php?id=${row.loan_id}" class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>`;
                }).join(''));
            })
            .fail(function () {
                $body.html(`<tr><td colspan="${colspan}" class="text-center text-danger py-4">Could not load loans. Please refresh.</td></tr>`);
            });
    }

    $('#loanStatus').on('change', loadLoans);

    loadLoans();
});