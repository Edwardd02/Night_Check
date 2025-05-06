// Function to navigate to a specific date
function setDate(date) {
    // Update to new consolidated PHP file
    const baseUrl = window.location.href.split('?')[0];
    window.location.href = `${baseUrl}?q=/modules/Night+Check/nightcheck.php&date=${date}`;
}

// Notification system (unchanged)
function showNotification(message, isSuccess) {
    const notification = document.createElement('div');
    notification.className = `notification ${isSuccess ? 'success' : 'error'}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Event listener for individual attendance select changes
document.querySelectorAll('.attendance-select').forEach(select => {
    select.addEventListener('change', function() {
        const studentId = this.dataset.studentId;
        const dateId = document.querySelector('input[name="attendance_date"]').value;
        const attendanceStatus = this.value;

        this.className = `attendance-select ${this.value.toLowerCase()}`;

        // Updated endpoint with action parameter
        fetch('/modules/Night Check/night_check_attendance.php?action=single', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: studentId,
                date_id: dateId,
                attendance_status: attendanceStatus
            })
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error saving attendance:', data.error);
                    showNotification(data.error || "An error occurred", false);
                } else {
                    showNotification("Saved successfully!", true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification("Network error - please try again", false);
            });
    });
});

// Bulk update function
async function setAll(status) {
    try {
        const students = Array.from(document.querySelectorAll('.attendance-select'))
            .map(select => select.dataset.studentId);
        const dateId = document.querySelector('input[name="attendance_date"]').value;

        // Store original values
        document.querySelectorAll('.attendance-select').forEach(select => {
            select.dataset.originalValue = select.value;
            select.value = status;
            select.className = `attendance-select ${status.toLowerCase()}`;
        });

        // Updated endpoint with action parameter
        const response = await fetch('/modules/Night Check/night_check_attendance.php?action=bulk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_ids: students,
                date_id: dateId,
                attendance_status: status
            })
        });
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Invalid response: ${text.substring(0, 100)}`);
        }
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Server error');

        showNotification(`Saved ${data.count} records!`, true);

    } catch (error) {
        console.error('Error:', error);
        // Revert UI changes with null check
        document.querySelectorAll('.attendance-select').forEach(select => {
            if (select.dataset.originalValue) {
                select.value = select.dataset.originalValue;
                select.className = `attendance-select ${select.dataset.originalValue.toLowerCase()}`;
            }
        });
        showNotification(`Save failed: ${error.message}`, false);
    }
}

// Sort function (unchanged)
function sortTable(columnIndex, dataType) {
    const table = document.querySelector('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('th')[columnIndex];
    const isAsc = header.classList.contains('sort-asc');

    table.querySelectorAll('th').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));

    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        return dataType === 'number'
            ? (isAsc ? parseInt(aValue) - parseInt(bValue) : parseInt(bValue) - parseInt(aValue))
            : (isAsc ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue));
    });

    rows.forEach(row => tbody.appendChild(row));
    header.classList.add(isAsc ? 'sort-desc' : 'sort-asc');
}

// DOMContentLoaded listener (unchanged)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('th[onclick]').forEach(header => {
        header.classList.add('sortable');
    });
});