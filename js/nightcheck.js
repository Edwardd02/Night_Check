// Function to navigate to a specific date
function setDate(date) {
    // Construct the proper Gibbon URL
    const baseUrl = window.location.href.split('?')[0];
    window.location.href = `${baseUrl}?q=/modules/Night+Check/night_check_attendance.php&date=${date}`;
}

// Notification system
function showNotification(message, isSuccess) {
    const notification = document.createElement('div');
    notification.className = `notification ${isSuccess ? 'success' : 'error'}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Trigger animation
    setTimeout(() => notification.classList.add('show'), 10);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Event listener for individual attendance select changes
document.querySelectorAll('.attendance-select').forEach(select => {
    select.addEventListener('change', function() {
        // Get required data from the select element's attributes and the hidden date input
        const studentId = this.dataset.studentId;
        const studentName = this.dataset.studentName;
        const dateId = document.querySelector('input[name="attendance_date"]').value;
        const attendanceStatus = this.value;

        // Update visual state of the select element based on the selected value
        this.className = `attendance-select ${this.value.toLowerCase()}`;

        // Send update to server to save the attendance status
        fetch('/modules/Night Check/save_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                student_id: studentId,
                student_name: studentName,
                date_id: dateId,
                attendance_status: attendanceStatus
            })
        })
            .then(response => response.json())
            .then(data => {
                // Handle the response from the server
                if (!data.success) {
                    console.error('Error saving attendance:', data.error);
                    showNotification("An error occurred while saving attendance, please contact support", false);
                }
                else{
                    showNotification("Save successfully!", true);
                }
            })
            .catch(error => {
                // Handle any errors that occurred during the fetch request
                console.error('Error:', error);
                showNotification("An error occurred while saving attendance, please contact support", false);
            });
    });
});
async function setAll(status) {
    try {
        const students = Array.from(document.querySelectorAll('.attendance-select'))
            .map(select => parseInt(select.dataset.studentId));
        const dateId = document.querySelector('input[name="attendance_date"]').value;

        // Store original values
        document.querySelectorAll('.attendance-select').forEach(select => {
            select.dataset.originalValue = select.value;
            select.value = status;
            select.className = `attendance-select ${status.toLowerCase()}`;
        });

        // Send request
        const response = await fetch('/modules/Night Check/bulk_attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_ids: students,
                date_id: dateId,
                attendance_status: status
            })
        });

        // Handle response
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Server error');

        showNotification(`Saved ${data.count} records successfully!`, true);

    } catch (error) {
        console.error('Error:', error);

        // Revert UI changes
        document.querySelectorAll('.attendance-select').forEach(select => {
            select.value = select.dataset.originalValue;
            select.className = `attendance-select ${select.dataset.originalValue.toLowerCase()}`;
        });

        showNotification(`Save failed: ${error.message}`, false);
    }
}

// Function to sort a table by a specific column
function sortTable(columnIndex, dataType) {
    const table = document.querySelector('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('th')[columnIndex];
    const isAsc = header.classList.contains('sort-asc');

    // Remove all sorting classes from all headers
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });

    // Sort the rows based on the specified column and data type
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();

        if (dataType === 'number') {
            return isAsc
                ? parseInt(aValue) - parseInt(bValue)
                : parseInt(bValue) - parseInt(aValue);
        } else {
            return isAsc
                ? aValue.localeCompare(bValue)
                : bValue.localeCompare(aValue);
        }
    });

    // Re-append the sorted rows to the table body
    rows.forEach(row => tbody.appendChild(row));

    // Update the class of the clicked header to indicate the sorting direction
    header.classList.add(isAsc ? 'sort-desc' : 'sort-asc');
}

// Event listener to add the 'sortable' class to table headers with an onclick attribute after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const headers = document.querySelectorAll('th[onclick]');
    headers.forEach(header => {
        header.classList.add('sortable');
    });
});