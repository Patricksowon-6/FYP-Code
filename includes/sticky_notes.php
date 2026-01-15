<?php 
	require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sticky Notes</title>
	<link rel="stylesheet" href="<?= CSS_PATH; ?>sticky_notes.css">
</head>
<body>

	<main style="margin-top: 100px;">
		<form id="noteForm">
			<button type="button" id="createBtn">+</button>
		</form>
		<div id="list" class="notes-grid"></div>
	</main>

	<!-- Modal -->
	<div class="modal-container" id="modal">
		<div class="modal">
			<button class="close-btn" id="close">&times;</button>
			<div class="modal-header">
				<h3>Create a Note</h3>
			</div>
			<div class="modal-content">
				<p>Enter your note details below:</p>
				<label for="noteTitle">Title:</label>
				<input type="text" id="noteTitle" placeholder="Enter title..." />

				<label for="noteColor">Top color:</label>
				<input type="color" id="noteColor" value="#38bdf8" />

				<label for="noteContent">Content:</label>
				<textarea id="noteContent" rows="6" placeholder="Write your note here..."></textarea>
				<br>
				<button class="confirm-btn" id="confirm">Confirm</button>
			</div>
		</div>
	</div>

	<script src="<?= JS_PATH; ?>sticky_notes.js"></script>
</body>
</html>
