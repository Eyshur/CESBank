</main>

<!-- jQuery CDN -->
<script src="/CESBANK/js/jquery.min.js"></script>

<!-- Bootstrap 5 Bundle (includes Popper) CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    /**
     * CESBANK SweetAlert2 helpers.
     * Use these everywhere instead of the browser's alert()/confirm().
     */

    // Small corner toast, e.g. cbToast('success', 'Deposit recorded');
    function cbToast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    // Full centered alert, e.g. cbAlert('error', 'Login failed', 'Incorrect username or password.');
    function cbAlert(icon, title, text = '') {
        return Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonColor: '#1F4FD8',
        });
    }

    // Confirmation dialog, returns a Promise<boolean>.
    // Usage: cbConfirm('Delete this user?', 'This cannot be undone.').then(ok => { if (ok) { ... } });
    function cbConfirm(title, text = '', confirmText = 'Yes, continue') {
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#D63A3A',
            cancelButtonColor: '#6B7280',
            reverseButtons: true,
        }).then((result) => result.isConfirmed);
    }

    // Blocking loading state for longer AJAX calls.
    function cbLoading(title = 'Please wait…') {
        Swal.fire({
            title: title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });
    }

    function cbLoadingClose() {
        Swal.close();
    }
</script>
<?php if (!empty($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
