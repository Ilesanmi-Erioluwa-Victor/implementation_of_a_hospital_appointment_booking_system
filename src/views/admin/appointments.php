<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-calendar-week"></i> Manage Appointments</h2>
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" id="filterDate">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no_show">No Show</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Doctor</label>
                <select class="form-select" id="filterDoctor">
                    <option value="">All Doctors</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Patient Name</label>
                <input type="text" class="form-control" id="filterPatient" placeholder="Search patient...">
            </div>
        </div>
    </div>
    <div class="mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#walkInModal">
            <i class="bi bi-person-plus"></i> Walk-in Booking
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover bg-white rounded shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="appointmentsTable"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="walkInModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Walk-in Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="walkInForm">
                    <div class="mb-3">
                        <label class="form-label">Patient Phone</label>
                        <input type="tel" class="form-control" id="walkInPhone" required>
                    </div>
                    <div class="mb-3" id="walkInNameFields">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">First Name (if new)</label>
                                <input type="text" class="form-control" id="walkInFirstName">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name (if new)</label>
                                <input type="text" class="form-control" id="walkInLastName">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="walkInDepartment"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Doctor</label>
                        <select class="form-select" id="walkInDoctor"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="walkInDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Time Slot</label>
                        <select class="form-select" id="walkInSlot"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" id="walkInReason" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="walkInSubmit">Book Appointment</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkCancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Cancel Appointments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkCancelForm">
                    <div class="mb-3">
                        <label class="form-label">Doctor</label>
                        <select class="form-select" id="bulkDoctor"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="bulkDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" id="bulkReason" rows="2" placeholder="e.g. Doctor called in sick"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="bulkCancelSubmit">
                    <i class="bi bi-exclamation-triangle"></i> Cancel All & Notify Patients
                </button>
            </div>
        </div>
    </div>
</div>
