<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5 class="text-muted">Total Appointments</h5>
                <h3 id="statTotal">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5 class="text-muted">Today</h5>
                <h3 id="statToday">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5 class="text-muted">Pending</h5>
                <h3 id="statPending">-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5 class="text-muted">No Shows</h5>
                <h3 id="statNoShow">-</h3>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h5><i class="bi bi-gear"></i> Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="/admin/appointments" class="btn btn-outline-primary">Manage Appointments</a>
                    <a href="/admin/doctors" class="btn btn-outline-primary">Manage Doctors</a>
                    <a href="/admin/departments" class="btn btn-outline-primary">Manage Departments</a>
                    <a href="/admin/reports" class="btn btn-outline-primary">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4">
                <h5><i class="bi bi-person-badge"></i> Staff Login</h5>
                <form id="staffLoginForm">
                    <div class="mb-3">
                        <label class="form-label">Staff Email</label>
                        <input type="email" class="form-control" id="staffEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="staffPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Staff Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
