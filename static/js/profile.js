const editBtn = document.getElementById('editBtn');
const saveBtn = document.getElementById('saveBtn');
const cancelBtn = document.getElementById('cancelBtn');

const usernameEl = document.getElementById('username');
const emailEl = document.getElementById('email');
const bioEl = document.getElementById('bio');

editBtn.addEventListener('click', () => {
    enableEditing();
});

cancelBtn.addEventListener('click', () => {
    disableEditing();
});

saveBtn.addEventListener('click', () => {
    saveProfile();
});

// Enable editing
function enableEditing() {
    const username = usernameEl.textContent;
    const email = emailEl.textContent;
    const bio = bioEl.textContent;

    usernameEl.innerHTML = `<input type="text" id="usernameInput" value="${username}">`;
    emailEl.innerHTML = `<input type="email" id="emailInput" value="${email}">`;
    bioEl.innerHTML = `<textarea id="bioInput">${bio}</textarea>`;

    editBtn.classList.add('hidden');
    saveBtn.classList.remove('hidden');
    cancelBtn.classList.remove('hidden');
}

// Disable editing without saving
function disableEditing() {
    usernameEl.textContent = usernameEl.querySelector('#usernameInput').value;
    emailEl.textContent = emailEl.querySelector('#emailInput').value;
    bioEl.textContent = bioEl.querySelector('#bioInput').value;

    editBtn.classList.remove('hidden');
    saveBtn.classList.add('hidden');
    cancelBtn.classList.add('hidden');
}

// Save profile via AJAX
function saveProfile() {
    const username = document.getElementById('usernameInput').value;
    const email = document.getElementById('emailInput').value;
    const bio = document.getElementById('bioInput').value;

    fetch('update_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, email, bio })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            usernameEl.textContent = username;
            emailEl.textContent = email;
            bioEl.textContent = bio;

            editBtn.classList.remove('hidden');
            saveBtn.classList.add('hidden');
            cancelBtn.classList.add('hidden');

            alert('Profile updated successfully!');
        } else {
            alert('Failed to update profile.');
        }
    })
    .catch(err => console.error(err));
}