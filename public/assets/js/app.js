const API_BASE = '';
const token = localStorage.getItem('token');
const user = JSON.parse(localStorage.getItem('user') || '{}');

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show`;
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

async function api(path, options = {}) {
    const headers = { 'Content-Type': 'application/json', ...options.headers };
    if (token) headers['Authorization'] = 'Bearer ' + token;
    const res = await fetch(API_BASE + path, { ...options, headers });
    const data = await res.json();
    if (!res.ok && data.error) throw new Error(data.error);
    return data;
}

function updateNav() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const guestLinks = document.getElementById('guestLinks');
    const guestLinks2 = document.getElementById('guestLinks2');
    const userMenu = document.getElementById('userMenu');
    const userName = document.getElementById('userName');
    const staffLinks = [
        document.getElementById('staffMenu'),
        document.getElementById('staffMenu2'),
        document.getElementById('staffMenu3'),
        document.getElementById('staffMenu4'),
    ];
    if (token && user.firstName) {
        if (guestLinks) guestLinks.style.display = 'none';
        if (guestLinks2) guestLinks2.style.display = 'none';
        if (userMenu) userMenu.classList.remove('d-none');
        if (userName) userName.textContent = user.firstName;
        const isStaff = user.role === 'admin' || user.role === 'staff';
        staffLinks.forEach(el => { if (el) el.classList.toggle('d-none', !isStaff); });
    }
}
}

document.addEventListener('DOMContentLoaded', () => {
    updateNav();

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/';
        });
    }
});

if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        try {
            const data = await api('/api/auth/patient/login', {
                method: 'POST',
                body: JSON.stringify({ email, password }),
            });
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.patient));
            showToast('Login successful!');
            var redirect = new URLSearchParams(window.location.search).get('redirect') || '/';
            setTimeout(() => window.location.href = redirect, 800);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });
}

if (document.getElementById('registerForm')) {
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            firstName: document.getElementById('regFirstName').value,
            lastName: document.getElementById('regLastName').value,
            email: document.getElementById('regEmail').value,
            phone: document.getElementById('regPhone').value,
            gender: document.getElementById('regGender').value,
            dateOfBirth: document.getElementById('regDob').value,
            address: document.getElementById('regAddress').value,
            password: document.getElementById('regPassword').value,
        };
        try {
            const result = await api('/api/auth/patient/register', {
                method: 'POST',
                body: JSON.stringify(data),
            });
            localStorage.setItem('token', result.token);
            localStorage.setItem('user', JSON.stringify(result.patient));
            showToast('Registration successful! Please check your email to verify.');
            var redirect = new URLSearchParams(window.location.search).get('redirect') || '/';
            setTimeout(() => window.location.href = redirect, 1500);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });
}

if (document.getElementById('forgotPasswordLink')) {
    document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
        e.preventDefault();
        const email = prompt('Enter your email address:');
        if (email) {
            api('/api/auth/forgot-password', {
                method: 'POST',
                body: JSON.stringify({ email }),
            }).then(() => showToast('If the email exists, a reset link has been sent.'))
              .catch(err => showToast(err.message, 'danger'));
        }
    });
}

if (document.getElementById('resetPasswordForm')) {
    document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const token = document.getElementById('resetToken').value;
        const password = document.getElementById('resetPassword').value;
        try {
            const data = await api('/api/auth/reset-password', {
                method: 'POST',
                body: JSON.stringify({ token, password }),
            });
            showToast(data.message);
            setTimeout(() => window.location.href = '/login', 1500);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });
}

if (document.getElementById('staffLoginForm')) {
    document.getElementById('staffLoginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('staffEmail').value;
        const password = document.getElementById('staffPassword').value;
        try {
            const data = await api('/api/auth/staff/login', {
                method: 'POST',
                body: JSON.stringify({ email, password }),
            });
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.staff));
            showToast('Staff login successful!');
            setTimeout(() => window.location.href = '/admin', 800);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });
}

if (document.getElementById('statTotal')) {
    loadDashboardStats();
}

async function loadDashboardStats() {
    try {
        const summary = await api('/api/admin/reports/appointments-summary');
        let total = 0, pending = 0, noShow = 0;
        summary.forEach(r => {
            total += r.total || 0;
            pending += r.pending || 0;
            noShow += r.no_show || 0;
        });
        document.getElementById('statTotal').textContent = total;
        document.getElementById('statToday').textContent = summary.length + ' doctors';
        document.getElementById('statPending').textContent = pending;
        document.getElementById('statNoShow').textContent = noShow;
    } catch (e) {
        console.error('Failed to load dashboard stats');
    }
}

async function loadDepartments(selectId, selectedValue) {
    try {
        const depts = await api('/api/departments');
        const select = document.getElementById(selectId);
        if (!select) return;
        select.innerHTML = '<option value="">All Departments</option>';
        depts.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d._id;
            opt.textContent = d.name;
            if (d._id === selectedValue) opt.selected = true;
            select.appendChild(opt);
        });
    } catch (err) { console.error('Failed to load departments', err); }
}

async function loadDoctors(selectId, departmentId, selectedValue) {
    try {
        let url = '/api/doctors';
        if (departmentId) url += '?department=' + departmentId;
        const doctors = await api(url);
        const select = document.getElementById(selectId);
        if (!select) return;
        select.innerHTML = '<option value="">Select Doctor...</option>';
        doctors.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d._id;
            opt.textContent = `Dr. ${d.firstName} ${d.lastName}`;
            if (d._id === selectedValue) opt.selected = true;
            select.appendChild(opt);
        });
    } catch (err) { console.error('Failed to load doctors', err); }
}

if (document.getElementById('departmentFilter')) {
    loadDepartments('departmentFilter');
    document.getElementById('departmentFilter').addEventListener('change', loadDoctorsList);
    document.getElementById('doctorSearch').addEventListener('input', loadDoctorsList);
    loadDoctorsList();
}

async function loadDoctorsList() {
    const deptId = document.getElementById('departmentFilter').value;
    const search = document.getElementById('doctorSearch').value.toLowerCase();
    const container = document.getElementById('doctorsList');
    try {
        let url = '/api/doctors';
        if (deptId) url += '?department=' + deptId;
        const doctors = await api(url);
        container.innerHTML = '';
        if (doctors.length === 0) {
            container.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-search" style="font-size:2rem"></i><p class="mt-2">No doctors found</p></div>';
            return;
        }
        doctors.filter(d => (d.firstName + ' ' + d.lastName + ' ' + (d.bio || '')).toLowerCase().includes(search))
            .forEach(d => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                col.innerHTML = `<div class="card doctor-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width:60px;height:60px;font-size:1.5rem;font-weight:bold;">
                            ${d.firstName[0]}${d.lastName[0]}
                        </div>
                        <div>
                            <h5 class="mb-1">Dr. ${d.firstName} ${d.lastName}</h5>
                            <span class="badge bg-info">${d.departmentId || 'General'}</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-2">${(d.bio || '').substring(0, 120)}${(d.bio || '').length > 120 ? '...' : ''}</p>
                    <div class="mt-auto">
                        <small class="text-muted"><i class="bi bi-clock"></i> ${d.slotDurationMinutes || 30} min slots</small>
                        <a href="/book?doctor=${d._id}" class="btn btn-sm btn-outline-primary float-end">Book</a>
                    </div>
                </div>`;
                container.appendChild(col);
            });
    } catch (err) {
        container.innerHTML = `<div class="col-12 text-center text-danger py-5">Failed to load doctors: ${err.message}</div>`;
    }
}

if (document.getElementById('bookDepartment')) {
    loadDepartments('bookDepartment');
    const params = new URLSearchParams(window.location.search);
    if (params.get('doctor')) {
        setTimeout(() => {
            document.getElementById('bookDoctor').value = params.get('doctor');
            loadTimeSlots();
        }, 500);
    }
    document.getElementById('bookDepartment').addEventListener('change', function() {
        loadDoctors('bookDoctor', this.value);
        document.getElementById('timeSlots').innerHTML = '<p class="text-muted">Select a doctor and date</p>';
    });
    document.getElementById('bookDoctor').addEventListener('change', loadTimeSlots);
    document.getElementById('bookDate').addEventListener('change', loadTimeSlots);
}

let selectedSlot = null;

async function loadTimeSlots() {
    const doctorId = document.getElementById('bookDoctor').value;
    const date = document.getElementById('bookDate').value;
    const slotContainer = document.getElementById('timeSlots');
    const submitBtn = document.getElementById('bookSubmit');
    selectedSlot = null;
    submitBtn.disabled = true;
    if (!doctorId || !date) {
        slotContainer.innerHTML = '<p class="text-muted">Select a doctor and date to see available slots</p>';
        return;
    }
    try {
        const data = await api(`/api/doctors/${doctorId}/availability?date=${date}`);
        slotContainer.innerHTML = '';
        if (data.slots.length === 0) {
            slotContainer.innerHTML = '<p class="text-muted">No available slots for this date</p>';
            return;
        }
        data.slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary slot-btn';
            btn.textContent = slot;
            btn.onclick = () => {
                document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selectedSlot = slot;
                submitBtn.disabled = false;
            };
            slotContainer.appendChild(btn);
        });
    } catch (err) {
        slotContainer.innerHTML = `<p class="text-danger">${err.message}</p>`;
    }
}

if (document.getElementById('bookingForm')) {
    document.getElementById('bookingForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!selectedSlot) { showToast('Please select a time slot', 'warning'); return; }
        const data = {
            doctorId: document.getElementById('bookDoctor').value,
            departmentId: document.getElementById('bookDepartment').value,
            appointmentDate: document.getElementById('bookDate').value,
            timeSlot: selectedSlot,
            reasonForVisit: document.getElementById('bookReason').value,
        };
        const btn = document.getElementById('bookSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Booking...';
        try {
            const result = await api('/api/appointments', {
                method: 'POST',
                body: JSON.stringify(data),
            });
            showToast('Appointment booked successfully! Check your email for confirmation.');
            setTimeout(() => window.location.href = '/my-appointments', 1000);
        } catch (err) {
            showToast(err.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Booking';
        }
    });
}

if (document.getElementById('appointmentsList')) {
    loadMyAppointments();
    document.getElementById('apptStatusFilter').addEventListener('change', loadMyAppointments);
}

async function loadMyAppointments() {
    const status = document.getElementById('apptStatusFilter').value;
    let url = '/api/appointments/mine';
    if (status) url += '?status=' + status;
    try {
        const appointments = await api(url);
        const container = document.getElementById('appointmentsList');
        if (appointments.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="bi bi-calendar-x"></i><p>No appointments found</p><a href="/book" class="btn btn-primary">Book an Appointment</a></div>';
            return;
        }
        container.innerHTML = '<div class="table-responsive"><table class="table table-hover bg-white rounded shadow-sm"><thead class="table-primary"><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Actions</th></tr></thead><tbody id="apptTableBody"></tbody></table></div>';
        const tbody = document.getElementById('apptTableBody');
        appointments.forEach(a => {
            const tr = document.createElement('tr');
            const statusBadge = { pending: 'warning', confirmed: 'success', completed: 'secondary', cancelled: 'danger', no_show: 'dark' };
            tr.innerHTML = `
                <td>${a.appointmentDate}</td>
                <td>${a.timeSlot}</td>
                <td>${a.doctorId || 'N/A'}</td>
                <td><span class="badge bg-${statusBadge[a.status] || 'secondary'}">${a.status}</span></td>
                <td>
                    ${(a.status === 'confirmed' || a.status === 'pending') ? `
                        <button class="btn btn-sm btn-danger" onclick="cancelAppointment('${a._id}')">Cancel</button>
                        <button class="btn btn-sm btn-outline-primary" onclick="rescheduleAppointment('${a._id}')">Reschedule</button>
                    ` : ''}
                </td>`;
            tbody.appendChild(tr);
        });
    } catch (err) {
        document.getElementById('appointmentsList').innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
    }
}

async function cancelAppointment(id) {
    if (!confirm('Are you sure you want to cancel this appointment?')) return;
    try {
        const data = await api(`/api/appointments/${id}/cancel`, {
            method: 'PATCH',
            body: JSON.stringify({ reason: 'Cancelled by patient' }),
        });
        showToast(data.message);
        loadMyAppointments();
    } catch (err) {
        showToast(err.message, 'danger');
    }
}

function rescheduleAppointment(id) {
    window.location.href = '/book?reschedule=' + id;
}

if (document.getElementById('appointmentsTable')) {
    loadAdminDoctors();
    loadAdminAppointments();
    document.getElementById('filterDate').addEventListener('change', loadAdminAppointments);
    document.getElementById('filterStatus').addEventListener('change', loadAdminAppointments);
    document.getElementById('filterDoctor').addEventListener('change', loadAdminAppointments);
    document.getElementById('filterPatient').addEventListener('input', debounce(loadAdminAppointments, 500));
}

function debounce(fn, ms) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

async function loadAdminDoctors() {
    try {
        const doctors = await api('/api/admin/doctors');
        ['filterDoctor', 'walkInDoctor', 'bulkDoctor'].forEach(id => {
            const select = document.getElementById(id);
            if (!select) return;
            select.innerHTML = '<option value="">All Doctors</option>';
            doctors.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d._id;
                opt.textContent = `Dr. ${d.firstName} ${d.lastName}`;
                select.appendChild(opt);
            });
        });
    } catch (err) { console.error(err); }
}

async function loadAdminAppointments() {
    const params = new URLSearchParams();
    const date = document.getElementById('filterDate').value;
    const status = document.getElementById('filterStatus').value;
    const doctor = document.getElementById('filterDoctor').value;
    const patient = document.getElementById('filterPatient').value;
    if (date) params.set('date', date);
    if (status) params.set('status', status);
    if (doctor) params.set('doctorId', doctor);
    if (patient) params.set('patientName', patient);
    try {
        const appointments = await api('/api/admin/appointments?' + params.toString());
        const tbody = document.getElementById('appointmentsTable');
        if (appointments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No appointments found</td></tr>';
            return;
        }
        const statusBadge = { pending: 'warning', confirmed: 'success', completed: 'secondary', cancelled: 'danger', no_show: 'dark' };
        tbody.innerHTML = appointments.map(a => `
            <tr>
                <td>${a.patientId || 'N/A'}</td>
                <td>${a.doctorId || 'N/A'}</td>
                <td>${a.appointmentDate}</td>
                <td>${a.timeSlot}</td>
                <td><span class="badge bg-${statusBadge[a.status] || 'secondary'}">${a.status}</span></td>
                <td>
                    ${a.status === 'confirmed' ? `
                        <button class="btn btn-sm btn-success" onclick="completeAppt('${a._id}')">Complete</button>
                        <button class="btn btn-sm btn-dark" onclick="noShowAppt('${a._id}')">No Show</button>
                        <button class="btn btn-sm btn-danger" onclick="staffCancelAppt('${a._id}')">Cancel</button>
                    ` : ''}
                    ${a.status === 'completed' ? `<span class="text-muted small">Completed</span>` : ''}
                </td>
            </tr>
        `).join('');
    } catch (err) {
        document.getElementById('appointmentsTable').innerHTML = `<tr><td colspan="6" class="text-danger">${err.message}</td></tr>`;
    }
}

async function completeAppt(id) {
    try {
        await api(`/api/admin/appointments/${id}/complete`, { method: 'PATCH' });
        showToast('Marked as completed');
        loadAdminAppointments();
    } catch (err) { showToast(err.message, 'danger'); }
}

async function noShowAppt(id) {
    try {
        await api(`/api/admin/appointments/${id}/no-show`, { method: 'PATCH' });
        showToast('Marked as no-show');
        loadAdminAppointments();
    } catch (err) { showToast(err.message, 'danger'); }
}

async function staffCancelAppt(id) {
    const reason = prompt('Reason for cancellation:');
    if (reason === null) return;
    try {
        await api(`/api/admin/appointments/${id}/cancel`, {
            method: 'PATCH',
            body: JSON.stringify({ reason: reason || 'Cancelled by staff' }),
        });
        showToast('Appointment cancelled, patient notified');
        loadAdminAppointments();
    } catch (err) { showToast(err.message, 'danger'); }
}

if (document.getElementById('walkInSubmit')) {
    loadDepartments('walkInDepartment');
    document.getElementById('walkInDepartment').addEventListener('change', function() {
        loadDoctors('walkInDoctor', this.value);
    });
    document.getElementById('walkInPhone').addEventListener('input', function() {
        if (this.value.length >= 8) {
            document.getElementById('walkInNameFields').style.display = 'none';
        } else {
            document.getElementById('walkInNameFields').style.display = 'block';
        }
    });
    document.getElementById('walkInSubmit').addEventListener('click', async () => {
        const data = {
            phone: document.getElementById('walkInPhone').value,
            firstName: document.getElementById('walkInFirstName').value,
            lastName: document.getElementById('walkInLastName').value,
            doctorId: document.getElementById('walkInDoctor').value,
            departmentId: document.getElementById('walkInDepartment').value,
            appointmentDate: document.getElementById('walkInDate').value,
            timeSlot: document.getElementById('walkInSlot').value,
            reasonForVisit: document.getElementById('walkInReason').value,
        };
        try {
            await api('/api/admin/appointments', { method: 'POST', body: JSON.stringify(data) });
            showToast('Walk-in appointment booked');
            bootstrap.Modal.getInstance(document.getElementById('walkInModal')).hide();
            loadAdminAppointments();
        } catch (err) { showToast(err.message, 'danger'); }
    });
}

if (document.getElementById('bulkCancelSubmit')) {
    loadDepartments('bulkCancelDepartment');
    document.getElementById('bulkCancelSubmit').addEventListener('click', async () => {
        if (!confirm('This will cancel ALL appointments for this doctor on this date and notify patients. Continue?')) return;
        const data = {
            doctorId: document.getElementById('bulkDoctor').value,
            date: document.getElementById('bulkDate').value,
            reason: document.getElementById('bulkReason').value || 'Doctor unavailable',
        };
        try {
            const result = await api('/api/admin/appointments/bulk-cancel', { method: 'POST', body: JSON.stringify(data) });
            showToast(result.message);
            bootstrap.Modal.getInstance(document.getElementById('bulkCancelModal')).hide();
            loadAdminAppointments();
        } catch (err) { showToast(err.message, 'danger'); }
    });
}

if (document.getElementById('doctorsTable')) {
    loadDepartments('docDepartment');
    loadDoctorsTable();
    document.getElementById('doctorSave').addEventListener('click', saveDoctor);
}

async function loadDoctorsTable() {
    try {
        const doctors = await api('/api/admin/doctors');
        const tbody = document.getElementById('doctorsTable');
        tbody.innerHTML = doctors.map(d => `
            <tr>
                <td>Dr. ${d.firstName} ${d.lastName}</td>
                <td>${d.email}</td>
                <td>${d.phone || 'N/A'}</td>
                <td>${d.departmentId || 'N/A'}</td>
                <td><span class="badge bg-${d.isActive ? 'success' : 'secondary'}">${d.isActive ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editDoctor('${d._id}')">Edit</button>
                </td>
            </tr>
        `).join('');
    } catch (err) { showToast(err.message, 'danger'); }
}

function editDoctor(id) {
    showToast('Edit functionality - click Save after changes', 'info');
    document.getElementById('doctorId').value = id;
}

async function saveDoctor() {
    const data = {
        firstName: document.getElementById('docFirstName').value,
        lastName: document.getElementById('docLastName').value,
        email: document.getElementById('docEmail').value,
        phone: document.getElementById('docPhone').value,
        departmentId: document.getElementById('docDepartment').value,
        bio: document.getElementById('docBio').value,
        slotDurationMinutes: parseInt(document.getElementById('docSlotDuration').value) || 30,
        isActive: document.getElementById('docActive').value === '1',
    };
    const id = document.getElementById('doctorId').value;
    try {
        if (id) {
            await api(`/api/doctors/${id}`, { method: 'PATCH', body: JSON.stringify(data) });
            showToast('Doctor updated');
        } else {
            await api('/api/admin/doctors', { method: 'POST', body: JSON.stringify(data) });
            showToast('Doctor added');
        }
        bootstrap.Modal.getInstance(document.getElementById('doctorModal')).hide();
        loadDoctorsTable();
    } catch (err) { showToast(err.message, 'danger'); }
}

if (document.getElementById('departmentForm')) {
    document.getElementById('departmentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            name: document.getElementById('deptName').value,
            description: document.getElementById('deptDesc').value,
        };
        try {
            await api('/api/departments', { method: 'POST', body: JSON.stringify(data) });
            showToast('Department added');
            document.getElementById('deptName').value = '';
            document.getElementById('deptDesc').value = '';
            loadDepartmentsTable();
        } catch (err) { showToast(err.message, 'danger'); }
    });
    loadDepartmentsTable();
}

async function loadDepartmentsTable() {
    try {
        const depts = await api('/api/departments');
        const tbody = document.getElementById('departmentsTable');
        tbody.innerHTML = depts.map(d => `
            <tr>
                <td>${d.name}</td>
                <td>${d.description || ''}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteDept('${d._id}')">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (err) { showToast(err.message, 'danger'); }
}

async function deleteDept(id) {
    if (!confirm('Delete this department?')) return;
    try {
        await api(`/api/departments/${id}`, {
            method: 'DELETE',
        });
        showToast('Department deleted');
        loadDepartmentsTable();
    } catch (err) { showToast(err.message, 'danger'); }
}

if (document.getElementById('loadReports')) {
    const today = new Date().toISOString().split('T')[0];
    const thirtyAgo = new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0];
    document.getElementById('reportStart').value = thirtyAgo;
    document.getElementById('reportEnd').value = today;
    document.getElementById('loadReports').addEventListener('click', loadReports);
    document.getElementById('exportCsv').addEventListener('click', (e) => {
        e.preventDefault();
        const start = document.getElementById('reportStart').value;
        const end = document.getElementById('reportEnd').value;
        window.location.href = `/api/admin/reports/export.csv?start=${start}&end=${end}`;
    });
    loadReports();
}

async function loadReports() {
    const start = document.getElementById('reportStart').value;
    const end = document.getElementById('reportEnd').value;
    const params = `?start=${start}&end=${end}`;
    try {
        const summary = await api('/api/admin/reports/appointments-summary' + params);
        const summaryTbody = document.getElementById('summaryTable');
        if (summary.length === 0) {
            summaryTbody.innerHTML = '<tr><td colspan="7" class="text-muted">No data for this period</td></tr>';
        } else {
            summaryTbody.innerHTML = summary.map(r => `<tr>
                <td>${r._id.doctorId}</td>
                <td>${r.total}</td>
                <td>${r.confirmed}</td>
                <td>${r.completed}</td>
                <td>${r.cancelled}</td>
                <td>${r.no_show}</td>
                <td>${r.pending}</td>
            </tr>`).join('');
        }
        const noShow = await api('/api/admin/reports/no-show-rate' + params);
        const noShowTbody = document.getElementById('noShowTable');
        if (noShow.length === 0) {
            noShowTbody.innerHTML = '<tr><td colspan="4" class="text-muted">No completed/no-show data for this period</td></tr>';
        } else {
            noShowTbody.innerHTML = noShow.map(r => `<tr>
                <td>${r._id}</td>
                <td>${r.total}</td>
                <td>${r.noShows}</td>
                <td>${r.noShowRate.toFixed(1)}%</td>
            </tr>`).join('');
        }
    } catch (err) { showToast(err.message, 'danger'); }
}
