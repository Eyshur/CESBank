/**
 * user-form.js
 * Used by both create.php and edit.php. The form itself carries:
 *   - data-mode="create" or "edit"
 *   - data-action="store.php" or "update.php"
 *   - data-user-id="123" (edit only)
 * so this one file can drive both pages.
 */

$(function () {
    const $form = $('#userForm');
    if (!$form.length) return;

    const mode   = $form.data('mode');
    const action = $form.data('action');

    // Toggle the customer-only fields based on the selected role.
    $('#roleSelect').on('change', function () {
        $('#customerFields').toggleClass('d-none', $(this).val() !== 'Customer');
    }).trigger('change');

    $form.on('submit', function (e) {
        e.preventDefault();

        const data = $form.serializeArray().reduce((acc, f) => {
            acc[f.name] = f.value;
            return acc;
        }, {});

        if (mode === 'edit') {
            data.user_id = $form.data('user-id');
        }

        const $btn = $('#userFormSubmit');
        $btn.prop('disabled', true);

        $.post(action, data)
            .done(function (res) {
                if (res.success) {
                    cbAlert('success', mode === 'edit' ? 'Changes saved' : 'User created', res.message)
                        .then(() => {
                            window.location.href = 'index.php';
                        });
                } else {
                    cbAlert('error', mode === 'edit' ? 'Could not save changes' : 'Could not create user', res.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                cbAlert('error', 'Something went wrong', 'Please try again.');
                $btn.prop('disabled', false);
            });
    });
});