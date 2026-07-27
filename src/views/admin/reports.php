<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-bar-chart"></i> Reports</h2>
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" id="reportStart">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" id="reportEnd">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary" id="loadReports"><i class="bi bi-search"></i> Load</button>
                <a class="btn btn-success" id="exportCsv" href="#"><i class="bi bi-download"></i> Export CSV</a>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-12">
            <div class="card p-4">
                <h5>Appointments Summary</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Doctor ID</th><th>Total</th><th>Confirmed</th><th>Completed</th><th>Cancelled</th><th>No Show</th><th>Pending</th></tr>
                        </thead>
                        <tbody id="summaryTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-4">
                <h5>No-Show Rate by Doctor</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Doctor ID</th><th>Total</th><th>No Shows</th><th>Rate (%)</th></tr>
                        </thead>
                        <tbody id="noShowTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
