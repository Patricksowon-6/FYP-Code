let assetToDelete = null;

function openDeleteModal(assetId) {
	assetToDelete = assetId;
	document.getElementById("deleteModal").style.display = "flex";
}

function closeDeleteModal() {
	document.getElementById("deleteModal").style.display = "none";
	assetToDelete = null;
}

document.getElementById("confirmDeleteBtn").addEventListener("click", async () => {
	if (!assetToDelete) return closeDeleteModal();

	const btn = document.getElementById("confirmDeleteBtn");
	const prevText = btn.innerText;
	btn.disabled = true;
	btn.innerText = "Deleting...";

	try {
		// ✅ Adjusted path (this JS is in /static/js/)
		const res = await fetch("../handlers/delete_file.php", {
		method: "POST",
		headers: { "Content-Type": "application/json" },
		body: JSON.stringify({ asset_id: assetToDelete })
		});

		const data = await res.json();

		if (res.ok && data.success) {
			const el = document.querySelector(`[data-asset-id="${assetToDelete}"]`);
			if (el) el.remove();
			alert("✅ " + data.message);
		} else {
			alert("❌ Delete failed: " + (data.message || "Unknown error"));
			console.error("Delete error:", data);
		}
	} catch (err) {
		console.error("Network or JS error:", err);
		alert("Network error — check console for details.");
	} finally {
		btn.disabled = false;
		btn.innerText = prevText;
		closeDeleteModal();
	}
	});

	window.addEventListener("click", (e) => {
	const modal = document.getElementById("deleteModal");
	if (e.target === modal) closeDeleteModal();
});
