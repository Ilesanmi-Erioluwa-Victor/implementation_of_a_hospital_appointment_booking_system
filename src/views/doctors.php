<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-people"></i> Find a Doctor</h2>
    <div class="filter-section">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter by Department</label>
                <select class="form-select" id="departmentFilter">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="doctorSearch" placeholder="Search doctors...">
            </div>
        </div>
    </div>
    <div class="row g-4" id="doctorsList">
        <div class="col-12 text-center text-muted py-5">
            <i class="bi bi-arrow-up" style="font-size: 2rem;"></i>
            <p>Select a department above to find doctors</p>
        </div>
    </div>
</div>
