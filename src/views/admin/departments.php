<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-building"></i> Manage Departments</h2>
    <div class="row">
        <div class="col-md-5">
            <div class="card p-4">
                <h5>Add Department</h5>
                <form id="departmentForm">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="deptName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="deptDesc" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Department</button>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="table-responsive">
                <table class="table table-hover bg-white rounded shadow-sm">
                    <thead class="table-primary">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="departmentsTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
