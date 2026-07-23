/**
 * users.js
 * Drives the Users & Customers management page: loads the table via AJAX,
 * opens the shared modal for Add/Edit/View, and wires up status toggle
 * and delete with SweetAlert2 confirmations. Relies on cbToast/cbAlert/
 * cbConfirm/cbLoading helpers defined in layout/footer.php.
 */

$(function () {
    const $tableBody   = $('#usersTableBody');
    const $searchInput = $('#searchInput');
    const $roleFilter  = $('#roleFilter');
    const userModalEl  = document.getElementById('userModal');
    const userModal    = new bootstrap.Modal(userModalEl);

    let searchTimer = null;

    function statusBadge(status) {
        return `<span class="badge rounded-pill badge-status-${status.toLowerCase()}">${status}</span>`;
    }

    function renderRow(row) {
        const isCustomer = !!row.customer_id;
        const displayName = isCustomer
            ? `${row.first_name} ${row.last_name}`
            : row.username;
        const subLine = isCustomer ? row.username : '—';
        const contact = isCustomer ? (row.email || row.contact_no || '—') : '—';
        const created = new Date(row.created_at).toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
        });
        const nextStatus = row.status === 'Active' ? 'Inactive' : 'Active';
        const statusIcon = row.status === 'Active' ? 'bi-slash-circle' : 'bi-check-circle';

        return `
            <tr data-id="${row.user_id}">
                <td class="ps-3">
                    <div class="fw-medium">${$('<div>').text(displayName).html()}</div>
                    <div class="text-muted small">${$('<div>').text(subLine).html()}</div>
                </td>
                <td><span class="text-muted small">${row.role}</span></td>
                <td class="small">${$('<div>').text(contact).html()}</td>
                <td>${statusBadge(row.status)}</td>
                <td class="text-muted small cb-mono">${created}</td>
                <td class="text-end pe-3">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary btn-view" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-edit" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-status" title="${nextStatus === 'Active' ? 'Activate' : 'Deactivate'}" data-next-status="${nextStatus}">
                            <i class="bi ${statusIcon}"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-delete" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
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

    function openModal(title, url, submitLabel) {
        $('#userModalTitle').text(title);
        $('#userModalBody').html('<div class="text-center text-muted py-4">Loading…</div>');
        userModal.show();

        $.get(url).done(function (html) {
            $('#userModalBody').html(html);
        }).fail(function () {
            $('#userModalBody').html('<p class="text-danger mb-0">Could not load this form. Please try again.</p>');
        });
    }

    // ---- Toolbar ----
    $searchInput.on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadUsers, 350);
    });

    $roleFilter.on('change', loadUsers);

    $('#btnAddUser').on('click', function () {
        openModal('Add User', 'create.php');
    });

    // ---- Row actions (delegated, since rows are re-rendered) ----
    $tableBody.on('click', '.btn-view', function () {
        const id = $(this).closest('tr').data('id');
        openModal('User Details', `view.php?id=${id}`);
    });

    $tableBody.on('click', '.btn-edit', function () {
        const id = $(this).closest('tr').data('id');
        openModal('Edit User', `edit.php?id=${id}`);
    });

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

    // ---- Modal form: role toggle + submit (delegated, form is injected) ----
    $('#userModalBody').on('change', '#roleSelect', function () {
        $('#customerFields').toggleClass('d-none', $(this).val() !== 'Customer');
    });

    $('#userModalBody').on('submit', '#userForm', function (e) {
        e.preventDefault();

        const $form = $(this);
        const mode = $form.data('mode');
        const url = mode === 'edit' ? 'update.php' : 'store.php';
        const data = $form.serializeArray().reduce((acc, f) => {
            acc[f.name] = f.value;
            return acc;
        }, {});

        if (mode === 'edit') {
            data.user_id = $form.data('user-id');
        }

        const $btn = $('#userFormSubmit');
        $btn.prop('disabled', true);

        $.post(url, data)
            .done(function (res) {
                if (res.success) {
                    userModal.hide();
                    cbToast('success', res.message);
                    loadUsers();
                } else {
                    cbAlert('error', mode === 'edit' ? 'Could not save changes' : 'Could not create user', res.message);
                }
            })
            .fail(function () {
                cbAlert('error', 'Something went wrong', 'Please try again.');
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
    });

    loadUsers();
});