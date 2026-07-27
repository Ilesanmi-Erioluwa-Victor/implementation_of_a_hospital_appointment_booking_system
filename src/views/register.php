<div class="container">
    <div class="auth-card card p-4">
        <h3 class="text-center mb-4"><i class="bi bi-person-plus"></i> Patient Registration</h3>
        <form id="registerForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" id="regFirstName" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="regLastName" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" class="form-control" id="regEmail" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="regPhone" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select class="form-select" id="regGender" required>
                    <option value="">Select...</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="regDob">
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" id="regAddress" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="regPassword" required minlength="8">
                <div class="form-text">Minimum 8 characters</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>
        <hr>
        <p class="text-center mb-0"><a href="/login<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">Already have an account? Login</a></p>
    </div>
</div>
