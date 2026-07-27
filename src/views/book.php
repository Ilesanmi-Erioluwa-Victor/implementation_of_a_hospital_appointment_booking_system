<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-calendar-plus"></i> Book Appointment</h2>
    <div class="row">
        <div class="col-lg-8">
            <div class="card p-4">
                <form id="bookingForm">
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="bookDepartment" required>
                            <option value="">Select Department...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Doctor</label>
                        <select class="form-select" id="bookDoctor" required>
                            <option value="">Select Doctor...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="bookDate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Time Slots</label>
                        <div id="timeSlots" class="d-flex flex-wrap gap-2">
                            <p class="text-muted">Select a doctor and date to see available slots</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Visit</label>
                        <textarea class="form-control" id="bookReason" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="bookSubmit" disabled>
                        <i class="bi bi-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4">
                <h5><i class="bi bi-info-circle"></i> Booking Tips</h5>
                <ul class="small text-muted">
                    <li class="mb-2">Select a department and doctor first</li>
                    <li class="mb-2">Choose a date to see available time slots</li>
                    <li class="mb-2">Slots shown are in real-time availability</li>
                    <li class="mb-2">You'll receive a confirmation email</li>
                    <li>You can cancel up to 2 hours before</li>
                </ul>
            </div>
        </div>
    </div>
</div>
