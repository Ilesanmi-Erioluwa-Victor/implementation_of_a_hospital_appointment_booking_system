<div class="container">
    <div class="auth-card card p-4 text-center">
        <h3><i class="bi bi-x-circle"></i> Cancel Appointment</h3>
        <div id="cancelStatus">
            <div class="spinner-border text-primary my-4" role="status"></div>
            <p>Processing cancellation...</p>
        </div>
    </div>
</div>
<script>
const params = new URLSearchParams(window.location.search);
const id = params.get('id');
if (id) {
    const token = localStorage.getItem('token');
    if (!token) {
        document.getElementById('cancelStatus').innerHTML = '<p class="text-danger">Please <a href="/login">login</a> to cancel your appointment.</p>';
    } else {
        fetch('/api/appointments/' + id + '/cancel', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({reason: 'Cancelled via email link'})
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('cancelStatus').innerHTML = data.error
                ? `<div class="text-danger"><p>${data.error}</p></div>`
                : `<div class="text-success"><i class="bi bi-check-circle" style="font-size:3rem"></i><p class="mt-2">${data.message}</p></div>`;
        });
    }
} else {
    document.getElementById('cancelStatus').innerHTML = '<p class="text-danger">No appointment ID provided.</p>';
}
</script>
