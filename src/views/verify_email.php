<div class="container">
    <div class="auth-card card p-4 text-center">
        <h3><i class="bi bi-envelope-check"></i> Email Verification</h3>
        <div id="verifyStatus">
            <div class="spinner-border text-primary my-4" role="status"></div>
            <p>Verifying your email...</p>
        </div>
    </div>
</div>
<script>
const params = new URLSearchParams(window.location.search);
const token = params.get('token');
if (token) {
    fetch('/api/auth/patient/verify-email', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({token})
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('verifyStatus').innerHTML = data.error
            ? `<div class="text-danger"><i class="bi bi-x-circle" style="font-size:3rem"></i><p class="mt-2">${data.error}</p></div>`
            : `<div class="text-success"><i class="bi bi-check-circle" style="font-size:3rem"></i><p class="mt-2">${data.message}</p><a href="/login" class="btn btn-primary mt-3">Login</a></div>`;
    });
} else {
    document.getElementById('verifyStatus').innerHTML = '<p class="text-danger">No verification token provided.</p>';
}
</script>
