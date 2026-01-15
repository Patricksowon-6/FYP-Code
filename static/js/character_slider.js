// component_manager.js
const API_URL = '../handlers/card_handler.php'; // <- adjust path if needed

const list = document.getElementById('list');
const addCard = document.getElementById('addCard');
const modal = document.getElementById('editModal');
const closeModal = modal.querySelector('.close');
const editForm = document.getElementById('editForm');
const modalTitle = document.getElementById('modalTitle');

const typeSelect = document.getElementById('editType');
const typeOtherInput = document.getElementById('editTypeOther');
const editIdInput = document.getElementById('editId');

let currentCard = null;
let isNewCard = false;

// Show "Other" input if selected
typeSelect.addEventListener('change', () => {
  typeOtherInput.style.display = typeSelect.value === 'Other' ? 'block' : 'none';
});

// --- Fetch cards from server
async function loadCards() {
  try {
    const res = await fetch(API_URL + '?fetch=1', { credentials: 'same-origin' });
    const cards = await res.json();
    // remove existing cards (except add-card)
    list.querySelectorAll('.item:not(.add-card)').forEach(c => c.remove());
    if (Array.isArray(cards)) cards.forEach(createCardElement);
  } catch (err) {
    console.error('Failed to load cards', err);
  }
}

// Open new card modal
addCard.addEventListener('click', () => {
  isNewCard = true;
  currentCard = null;
  modalTitle.textContent = "Add New Card";
  editForm.reset();
  editIdInput.value = '';
  typeOtherInput.style.display = 'none';
  modal.style.display = 'flex';
});

// Close modal
closeModal.onclick = () => modal.style.display = 'none';
window.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

// Save or update card via fetch
editForm.addEventListener('submit', async e => {
  e.preventDefault();
  document.getElementById('saveBtn').disabled = true;

  const name = document.getElementById('editName').value.trim();
  const purpose = document.getElementById('editPurpose').value.trim();
  let type = typeSelect.value;
  if (type === 'Other') type = typeOtherInput.value.trim() || 'Other';
  const imgInput = document.getElementById('editImg');

  const formData = new FormData();
  formData.append('name', name);
  formData.append('purpose', purpose);
  formData.append('card_type', type);
  formData.append('type', 'main'); // explicit create/update main card
  formData.append('project_id', window.PROJECT_ID);

  // if editing, include id
  if (!isNewCard && editIdInput.value) {
    formData.append('id', editIdInput.value);
  }

  if (imgInput.files[0]) formData.append('image', imgInput.files[0]);

  try {
    const res = await fetch(API_URL, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });

    const data = await res.json();
    console.log('Save result:', data);

    if (data.success) {
      await loadCards();
      modal.style.display = 'none';
    } else {
      alert('Save failed: ' + (data.error || JSON.stringify(data)));
    }
  } catch (err) {
    console.error('Save error', err);
    alert('Save failed (see console).');
  } finally {
    document.getElementById('saveBtn').disabled = false;
  }
});

// Create a card element
function createCardElement(data) {
  const card = document.createElement('div');
  card.className = 'item';
  card.dataset.id = data.main_card_id || data.id || '';
  card.dataset.name = data.card_name || data.name || '';
  card.dataset.purpose = data.card_purpose || data.purpose || '';
  card.dataset.type = data.card_type || data.type || '';
  card.dataset.img = data.signed_url || data.image_url || 'https://via.placeholder.com/200x150';

  card.innerHTML = `
    <img src="${card.dataset.img}" alt="${data.card_name || data.name || ''}">
    <div class="details">
        <div class="name"><b>Name:</b> ${card.dataset.name}</div>
        <div class="purpose"><b>Purpose:</b> ${card.dataset.purpose}</div>
        <div class="type"><b>Card Type:</b> ${card.dataset.type}</div>
        <button class="seePartsBtn BTN">See Parts</button>
        <button class="editBtn BTN">Edit</button>
        <button class="deleteBtn BTN">Delete</button>
    </div>
  `;

    const seePartsBtn = card.querySelector('.seePartsBtn');
    seePartsBtn.addEventListener('click', () => {
        const mainCardId = card.dataset.id;
        const imageUrl = encodeURIComponent(card.dataset.img);
        window.location.href = `component_parts.php?main_card_id=${mainCardId}&image=${imageUrl}`;
    });

  // Edit button
  card.querySelector('.editBtn').addEventListener('click', () => openEditModal(card));
  // Delete button
  card.querySelector('.deleteBtn').addEventListener('click', async () => {
    if (!confirm("Delete this card?")) return;
    try {
      const id = card.dataset.id;
      const body = new URLSearchParams({ main_card_id: id });
      const res = await fetch(API_URL, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
      });
      const result = await res.json();
      console.log('Delete result', result);
      if (result.success) card.remove();
      else alert('Delete failed: ' + (result.error || JSON.stringify(result)));
    } catch (err) {
      console.error('Delete error', err);
      alert('Delete failed (see console).');
    }
  });

  list.insertBefore(card, addCard);
}

// Open modal to edit card; populate fields and set editId hidden input
function openEditModal(card) {
  isNewCard = false;
  currentCard = card;
  modalTitle.textContent = "Edit Card";

  document.getElementById('editName').value = card.dataset.name;
  document.getElementById('editPurpose').value = card.dataset.purpose;
  typeSelect.value = ['Scene','Character','Costume','Set','Prop'].includes(card.dataset.type) ? card.dataset.type : 'Other';
  typeOtherInput.value = !['Scene','Character','Costume','Set','Prop'].includes(card.dataset.type) ? card.dataset.type : '';
  typeOtherInput.style.display = typeSelect.value === 'Other' ? 'block' : 'none';

  editIdInput.value = card.dataset.id || '';
  modal.style.display = 'flex';
}

// Initial load
loadCards();
