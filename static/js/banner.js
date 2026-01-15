const modal = document.getElementById('deleteModal');
const openBtn = document.getElementById('openDeleteModal');
const cancelBtn = modal.querySelector('button[type="button"]');

// Open modal
openBtn.addEventListener('click', (e) => {
    e.preventDefault(); // only here
    modal.style.display = 'flex';
});

// Close modal (Cancel button)
cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Close when clicking outside modal box
modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
