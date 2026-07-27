<div class="container">
    <div class="auth-card card p-4">
        <h3 class="text-center mb-4"><i class="bi bi-person"></i> Patient Login</h3>
        <div class="alert alert-info small py-2">
            <i class="bi bi-info-circle"></i> This login is for registered patients only.
            Staff should use the <a href="/admin" class="alert-link">staff login</a>.
        </div>
        <form id="loginForm">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" class="form-control" id="loginEmail" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="loginPassword" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <hr>
        <div class="text-center">
            <p class="mb-1"><a href="/register?redirect=<?= urlencode($_GET['redirect'] ?? '/') ?>">Don't have an account? Register</a></p>
            <p class="mb-0"><a href="#" id="forgotPasswordLink">Forgot Password?</a></p>
        </div>
    </div>
</div>
