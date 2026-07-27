/**
 * users.js
 * Drives the Users & Customers index table: loads rows via AJAX, wires up
 * search/filter, and handles status toggle + delete with SweetAlert2
 * confirmations. View/Edit/Add are plain page links (view.php, edit.php,
 * create.php) — no modal involved.
 */

$(function () {
    const $tableBody   = $('#usersTableBody');
    const $searchInput = $('#searchInput');
    const $roleFilter  = $('#roleFilter');

    let searchTimer = null;

    function statusBadge(status) {
        return `<span class="badge rounded-pill badge-status-${status.toLowerCase()}">${status}</span>`;
    }

    function esc(text) {
        return $('<div>').text(text ?? '').html();
    }

    function renderRow(row) {
        const isCustomer = !!row.customer_id;
        const displayName = isCustomer ? `${row.first_name} ${row.last_name}` : row.username;
        const subLine = isCustomer ? row.username : '—';
        const contact = isCustomer ? (row.email || row.contact_no || '—') : '—';
        const created = new Date(row.created_at).toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
        });
        const nextStatus = row.status === 'Active' ? 'Inactive' : 'Active';
        const statusIcon = row.status === 'Active' ? 'bi-slash-circle' : 'bi-check-circle';
        const isAdmin = window.CB_ROLE === 'Administrator';

        const actions = `
            <a href="view.php?id=${row.user_id}" class="btn btn-outline-secondary" title="View">
                <i class="bi bi-eye"></i>
            </a>
            ${isAdmin ? `
            <a href="edit.php?id=${row.user_id}" class="btn btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-outline-secondary btn-status" title="${nextStatus === 'Active' ? 'Activate' : 'Deactivate'}" data-next-status="${nextStatus}">
                <i class="bi ${statusIcon}"></i>
            </button>
            <button class="btn btn-outline-danger btn-delete" title="Delete">
                <i class="bi bi-trash"></i>
            </button>` : ''}
        `;

        return `
            <tr data-id="${row.user_id}">
                <td class="ps-3">
                    <div class="fw-medium">${esc(displayName)}</div>
                    <div class="text-muted small">${esc(subLine)}</div>
                </td>
                <td><span class="text-muted small">${row.role}</span></td>
                <td class="small">${esc(contact)}</td>
                <td>${statusBadge(row.status)}</td>
                <td class="text-muted small cb-mono">${created}</td>
                <td class="text-end pe-3">
                    <div class="btn-group btn-group-sm">${actions}</div>
                </td>
            </tr>`;
    }

    function loadUsers() {
        $tableBody.html('<tr><td colspan="6" class="text-center text-muted py-4">Loading users…</td></tr>');

        $.get('index.php', {
            role: $roleFilter.val(),
            search: $searchInput.val(),
        })
        .done(function (res) {
            if (!res.success || !res.data.length) {
                $tableBody.html('<tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>');
                return;
            }
            $tableBody.html(res.data.map(renderRow).join(''));
        })
        .fail(function () {
            $tableBody.html('<tr><td colspan="6" class="text-center text-danger py-4">Could not load users. Please refresh.</td></tr>');
        });
    }

    // ---- Toolbar ----
    $searchInput.on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadUsers, 350);
    });

    $roleFilter.on('change', loadUsers);

    // ---- Row actions (delegated, since rows are re-rendered) ----
    $tableBody.on('click', '.btn-status', function () {
        const $row = $(this).closest('tr');
        const id = $row.data('id');
        const nextStatus = $(this).data('next-status');
        const verb = nextStatus === 'Active' ? 'activate' : 'deactivate';

        cbConfirm(`${verb.charAt(0).toUpperCase() + verb.slice(1)} this account?`, '').then((ok) => {
            if (!ok) return;

            $.post('status.php', { user_id: id, status: nextStatus })
                .done(function (res) {
                    if (res.success) {
                        cbToast('success', res.message);
                        loadUsers();
                    } else {
                        cbAlert('error', 'Could not update status', res.message);
                    }
                })
                .fail(function () {
                    cbAlert('error', 'Something went wrong', 'Please try again.');
                });
        });
    });

    $tableBody.on('click', '.btn-delete', function () {
        const id = $(this).closest('tr').data('id');

        cbConfirm('Delete this user?', 'This cannot be undone.', 'Yes, delete').then((ok) => {
            if (!ok) return;

            $.post('destroy.php', { user_id: id })
                .done(function (res) {
                    if (res.success) {
                        cbToast('success', res.message);
                        loadUsers();
                    } else {
                        cbAlert('error', 'Could not delete user', res.message);
                    }
                })
                .fail(function () {
                    cbAlert('error', 'Something went wrong', 'Please try again.');
                });
        });
    });

    loadUsers();
});