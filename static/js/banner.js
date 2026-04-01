const modal = document.getElementById('deleteModal');
const openBtn = document.getElementById('openDeleteModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmBtn = document.getElementById('confirmDeleteBtn');
const form = document.getElementById('projectForm');

// Open modal when delete button is clicked
openBtn.addEventListener('click', (e) => {
    e.preventDefault();
    modal.style.display = 'flex';
});

// Cancel button closes modal
cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Confirm Delete submits form with action
confirmBtn.addEventListener('click', () => {
    // Add hidden input for action=delete_project
    let actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete_project';
    form.appendChild(actionInput);

    // Submit the form
    form.submit();
});

// Close modal when clicking outside the box
modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});