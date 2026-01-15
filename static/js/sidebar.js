// Handles sidebar navigation
document.querySelectorAll(".category-btn").forEach(btn => {
    btn.addEventListener("click", e => {
        const type = btn.dataset.type;

        // ✅ Prevent the Filter button from triggering navigation
        if (btn.id === "openFilterBtn") {
            e.preventDefault();
            e.stopPropagation();
            // Let filter_box.js handle showing the modal
            return;
        }

        // ✅ For all other buttons, navigate normally
        if (type) {
            window.location.href = `media.php?type=${type}`;
        }
    });
});



document.getElementById("openFilterBtn").addEventListener("click", () => {
    document.getElementById("filterModal").style.display = "flex";
});

function closeFilterModal() {
    document.getElementById("filterModal").style.display = "none";
}

// Optional: close modal if user clicks outside
window.addEventListener("click", (e) => {
    const modal = document.getElementById("filterModal");
    if (e.target === modal) modal.style.display = "none";
});

document.getElementById("openFilterBtn").addEventListener("click", (e) => {
  e.preventDefault();
  document.getElementById("filterModal").style.display = "flex";
});
