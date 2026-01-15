document.addEventListener('DOMContentLoaded', () => {
	const form = document.getElementById('noteForm');
	const modal = document.getElementById('modal');
	const closeBtn = document.getElementById('close');
	const confirmBtn = document.getElementById('confirm');
	const noteTitle = document.getElementById('noteTitle');
	const noteColor = document.getElementById('noteColor');
	const noteContent = document.getElementById('noteContent');
	const list = document.getElementById('list');

	// Load saved notes
	loadNotes();

	// Open modal
	form.addEventListener('click', (e) => {
		e.preventDefault();
		modal.classList.add('show-modal');
		noteTitle.value = '';
		noteContent.value = '';
		noteColor.value = '#e6b905';
		setTimeout(() => noteTitle.focus(), 100);
	});

	// Close modal
	closeBtn.addEventListener('click', () => modal.classList.remove('show-modal'));
	window.addEventListener('click', (e) => {
		if (e.target === modal) modal.classList.remove('show-modal');
	});

	// Confirm and add note
	confirmBtn.addEventListener('click', (e) => {
		e.preventDefault();
		const title = noteTitle.value.trim();
		const content = noteContent.value.trim();
		if (!content) return;

		const date = new Date();
		const formattedDate = date.toLocaleString();

		const id = Date.now().toString(); // unique note ID
		const noteData = {
			id,
			title: title || 'Untitled',
			content,
			color: noteColor.value,
			date: formattedDate,
			x: 50,
			y: 50
		};

		createNote(noteData);
		saveNoteToLocalStorage(noteData);

		modal.classList.remove('show-modal');
	});

	// Remove note
	list.addEventListener('click', (e) => {
		if (e.target.classList.contains('close')) {
			const noteEl = e.target.closest('.note');
			removeNoteFromLocalStorage(noteEl.dataset.id);
			noteEl.remove();
		}
	});

	// ============ FUNCTIONS ============

	function createNote(note) {
		const newNote = document.createElement('div');
		newNote.classList.add('note');
		newNote.dataset.id = note.id;
		newNote.style.position = 'absolute';
		newNote.style.left = note.x + 'px';
		newNote.style.top = note.y + 'px';
		newNote.innerHTML = `
			<div class="note-top" style="background-color:${note.color}">${escapeHtml(note.title)}</div>
			<div class="note-content"><textarea>${escapeHtml(note.content)}</textarea></div>
			<div class="note-footer">
				<span>${note.date}</span>
				<span class="close">&times;</span>
			</div>
		`;

		makeDraggable(newNote);
		list.appendChild(newNote);
	}

	function makeDraggable(note) {
		let offsetX, offsetY, isDragging = false;

		note.addEventListener('mousedown', (e) => {
			if (e.target.tagName === 'TEXTAREA') return; // prevent dragging inside text area
			isDragging = true;
			offsetX = e.clientX - note.offsetLeft;
			offsetY = e.clientY - note.offsetTop;
			note.style.zIndex = 999;
		});

		document.addEventListener('mousemove', (e) => {
			if (!isDragging) return;
			note.style.left = e.clientX - offsetX + 'px';
			note.style.top = e.clientY - offsetY + 'px';
		});

		document.addEventListener('mouseup', () => {
			if (isDragging) {
				isDragging = false;
				note.style.zIndex = '';
				updateNotePosition(note.dataset.id, note.offsetLeft, note.offsetTop);
			}
		});
	}

	function saveNoteToLocalStorage(note) {
		const notes = JSON.parse(localStorage.getItem('stickyNotes') || '[]');
		notes.push(note);
		localStorage.setItem('stickyNotes', JSON.stringify(notes));
	}

	function loadNotes() {
		const notes = JSON.parse(localStorage.getItem('stickyNotes') || '[]');
		notes.forEach(note => createNote(note));
	}

	function removeNoteFromLocalStorage(id) {
		const notes = JSON.parse(localStorage.getItem('stickyNotes') || '[]');
		const updated = notes.filter(note => note.id !== id);
		localStorage.setItem('stickyNotes', JSON.stringify(updated));
	}

	function updateNotePosition(id, x, y) {
		const notes = JSON.parse(localStorage.getItem('stickyNotes') || '[]');
		const note = notes.find(n => n.id === id);
		if (note) {
			note.x = x;
			note.y = y;
			localStorage.setItem('stickyNotes', JSON.stringify(notes));
		}
	}

	function escapeHtml(str) {
		return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
	}
});
