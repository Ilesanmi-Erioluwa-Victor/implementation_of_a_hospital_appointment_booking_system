<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-person-badge"></i> Manage Doctors</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#doctorModal">
        <i class="bi bi-plus-circle"></i> Add Doctor
    </button>
    <div class="table-responsive">
        <table class="table table-hover bg-white rounded shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="doctorsTable"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="doctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="doctorForm">
                    <input type="hidden" id="doctorId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="docFirstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="docLastName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="docEmail" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="docPhone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="docDepartment" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea class="form-control" id="docBio" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slot Duration (minutes)</label>
                        <input type="number" class="form-control" id="docSlotDuration" value="30" min="15" step="5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Active</label>
                        <select class="form-select" id="docActive">
                            <option value="1">Active</option>
                            <option value="">Inactive</option>
                        </select>
                    </div>
                    <hr>
                    <h6>Working Hours</h6>
                    <div id="workingHoursContainer">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="doctorSave">Save</button>
            </div>
        </div>
    </div>
</div>
