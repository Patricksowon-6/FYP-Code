const detailsModal = document.getElementById('detailsModal');
const closeModal = detailsModal.querySelector('.close');
const detailsForm = document.getElementById('detailsForm');
const addCardBtn = document.getElementById('addCardBtn');
const cardsContainer = document.querySelector('.cards');

const mainCardId = new URLSearchParams(window.location.search).get('main_card_id');

let currentCard = null;
let isNewCard = false;

// -----------------------------
// Open modal for new subcard
// -----------------------------
addCardBtn.addEventListener('click', () => {
    currentCard = null;
    isNewCard = true;
    document.getElementById('modalTitle').textContent = "Add New Subcard";
    detailsForm.reset();
    detailsModal.style.display = 'flex';
});

// -----------------------------
// Close modal
// -----------------------------
closeModal.onclick = () => detailsModal.style.display = 'none';
window.onclick = e => { if (e.target === detailsModal) detailsModal.style.display = 'none'; };

// -----------------------------
// Load all subcards
// -----------------------------
async function loadSubCards(mainCardId) {
    if (!mainCardId) return;

    // Remove existing subcards (keep Add button)
    cardsContainer.querySelectorAll('.card:not(.add-card)').forEach(c => c.remove());

    try {
        const res = await fetch(`../handlers/sub_card_handler.php?main_card_id=${mainCardId}`);
        const subcards = await res.json();

        console.log('Loaded subcards: ', subcards);

        subcards.forEach(card => {
            const div = document.createElement('div');
            div.className = 'card';
            div.dataset.id = card.sub_card_id;

            div.innerHTML = `
                <div class="left">
                    <img src="${card.image_url || 'https://via.placeholder.com/300x200'}" alt="${card.title}">
                </div>
                <div class="right">
                    <h1>${card.title}</h1>
                    <p>${card.description}</p>
                    <button class="editBtn btn">Edit</button>
                    <button class="deleteBtn btn">Delete</button>
                </div>
            `;

            // Edit button
            div.querySelector('.editBtn').addEventListener('click', () => openEditModal(div));

            // Delete button
            div.querySelector('.deleteBtn').addEventListener('click', async () => {
                if (confirm("Delete this subcard?")) {
                    const sub_card_id = div.dataset.id;
                    const delRes = await fetch('../handlers/sub_card_handler.php', {
                        method: 'DELETE',
                        headers: {'Content-Type':'application/x-www-form-urlencoded'},
                        body: `sub_card_id=${sub_card_id}`
                    });
                    const data = await delRes.json();
                    if (data.success) div.remove();
                }
            });

            cardsContainer.insertBefore(div, document.querySelector('.add-card'));
        });

    } catch (err) {
        console.error('Failed to load subcards:', err);
    }
}

// -----------------------------
// Save (Add/Edit) subcard
// -----------------------------
detailsForm.addEventListener('submit', async e => {
    e.preventDefault();

    const title = document.getElementById('cardTitle').value;
    const description = document.getElementById('cardDescription').value;
    const imgInput = document.getElementById('cardImg');

    const formData = new FormData();
    formData.append('main_card_id', mainCardId);
    formData.append('title', title);
    formData.append('description', description);

    if (imgInput.files[0]) formData.append('image', imgInput.files[0]);
    if (!isNewCard && currentCard) formData.append('sub_card_id', currentCard.dataset.id);

    try {
        const res = await fetch('../handlers/sub_card_handler.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            loadSubCards(mainCardId); // Refresh display immediately
        } else {
            console.error('Save error:', data.error);
        }
    } catch (err) {
        console.error('Save error:', err);
    }

    detailsModal.style.display = 'none';
});

// -----------------------------
// Open Edit Modal
// -----------------------------
function openEditModal(card) {
    currentCard = card;
    isNewCard = false;
    document.getElementById('modalTitle').textContent = "Edit Subcard";

    document.getElementById('cardTitle').value = card.querySelector('h1').textContent;
    document.getElementById('cardDescription').value = card.querySelector('p').textContent;
    document.getElementById('cardImg').value = ''; // reset file input

    detailsModal.style.display = 'flex';
}

// -----------------------------
// Initial load
// -----------------------------
if (mainCardId) loadSubCards(mainCardId);
