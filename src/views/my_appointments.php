<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-calendar-check"></i> My Appointments</h2>
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" id="apptStatusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="no_show">No Show</option>
                </select>
            </div>
        </div>
    </div>
    <div id="appointmentsList"></div>
</div>
