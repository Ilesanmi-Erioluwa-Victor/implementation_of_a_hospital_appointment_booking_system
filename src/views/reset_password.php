<div class="container">
    <div class="auth-card card p-4">
        <h3 class="text-center mb-4"><i class="bi bi-key"></i> Reset Password</h3>
        <form id="resetPasswordForm">
            <input type="hidden" id="resetToken">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" id="resetPassword" required minlength="8">
                <div class="form-text">Minimum 8 characters</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
</div>
<script>
const params = new URLSearchParams(window.location.search);
const token = params.get('token');
if (token) {
    document.getElementById('resetToken').value = token;
} else {
    document.querySelector('.auth-card').innerHTML = '<p class="text-danger text-center">No reset token provided.</p>';
}
</script>
