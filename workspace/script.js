document.addEventListener("DOMContentLoaded", () => {

    let shootDates = [];
    let selectedShootDate = null;

    const shootDateModal = document.getElementById("shootDateModal");
    const shootDateForm = document.getElementById("shootDateForm");
    const assetModal = document.getElementById("assetModal");
    const previewModal = document.getElementById("previewModal");
    const previewFrame = document.getElementById("previewFrame");
    const previewTitle = document.getElementById("previewTitle");
    const previewFallback = document.getElementById("previewFallback");
    const existingFilesContainer = document.getElementById("existingFilesContainer");
    
    const existingSection = document.getElementById("existingFilesSection");
    const uploadSection = document.getElementById("uploadNewSection");
    const chooseExistingBtn = document.getElementById("chooseExistingBtn");
    const uploadNewBtn = document.getElementById("uploadNewBtn");
    const uploadNewForm = document.getElementById("uploadNewForm");
    const addAssetBtn = document.getElementById("addAssetBtn");

    /* ===============================
       CLOSE MODALS
    =============================== */
    function closeModals() {
        shootDateModal.style.display = "none";
        assetModal.style.display = "none";
        previewModal.style.display = "none";
        previewFrame.src = "";
    }
    document.querySelectorAll(".close-btn").forEach(btn => btn.onclick = closeModals);
    window.onclick = (e) => { if (e.target.classList.contains("modal")) closeModals(); };

    /* ===============================
       LOAD SHOOT DATES (SESSION)
    =============================== */
    async function loadShootDates() {
        try {
            const res = await fetch("../handlers/scene_handler.php?action=get_shoot_dates");
            shootDates = await res.json();
            refreshShootDates();
        } catch (err) {
            console.error("Failed to load shoot dates:", err);
        }
    }

    function refreshShootDates() {
        const list = document.getElementById("shootDateList");
        list.innerHTML = "";
        if (!shootDates.length) {
            list.innerHTML = "<li class='placeholder'>No shoot dates yet</li>";
            return;
        }
        shootDates.forEach(sd => {
            const li = document.createElement("li");
            li.className = "shoot-date-item";
            li.textContent = `${sd.date} — ${sd.scene}`;
            li.onclick = () => loadAssets(sd);
            list.appendChild(li);
        });
    }

    /* ===============================
       SELECT SHOOT DATE & LOAD ASSETS
    =============================== */
    async function loadAssets(shootDate) {
        selectedShootDate = shootDate;
        document.getElementById("dashboardTitle").textContent =
            `Assets for ${shootDate.scene} (${shootDate.date})`;

        const grid = document.getElementById("assetGrid");
        grid.innerHTML = `<p class="placeholder">Loading assets...</p>`;

        try {
            const res = await fetch(`../handlers/scene_handler.php?action=get_assets_for_shoot&shoot_date_id=${shootDate.shoot_date_id}`);
            const assets = await res.json();
            grid.innerHTML = "";

            assets.forEach(asset => {
                const card = document.createElement("div");
                card.classList.add("asset-card");

                const thumb = asset.type === "image" ? asset.url : "pdf_placeholder.png";
                card.innerHTML = `<img src="${thumb}"><h4>${asset.title}</h4>`;
                card.addEventListener("click", () => openPreview(asset));
                grid.appendChild(card);
            });
        } catch (err) {
            console.error("Failed to load assets:", err);
        }
    }

    /* ===============================
       OPEN PREVIEW MODAL
    =============================== */
    function openPreview(asset) {
        previewTitle.textContent = asset.title;
        if (asset.type === "image" || asset.type === "pdf") {
            previewFrame.src = asset.url;
            previewFrame.style.display = "block";
            previewFallback.style.display = "none";
        } else {
            previewFrame.style.display = "none";
            previewFallback.style.display = "block";
        }
        previewModal.style.display = "block";
    }

    /* ===============================
       ADD SHOOT DATE
    =============================== */
    document.getElementById("addShootDateBtn").onclick = () => shootDateModal.style.display = "block";

    shootDateForm.onsubmit = async (e) => {
        e.preventDefault();
        const data = new FormData();
        data.append("action", "add_shoot_date");
        data.append("date", document.getElementById("shootDateInput").value);
        data.append("scene", document.getElementById("shootSceneInput").value);

        try {
            const res = await fetch("../handlers/scene_handler.php", { method: "POST", body: data });
            const result = await res.json();
            if (result.success) {
                shootDateForm.reset();
                shootDateModal.style.display = "none";
                loadShootDates();
            } else {
                alert("Failed to add shoot date: " + (result.error ?? ""));
            }
        } catch (err) {
            console.error("Failed to save shoot date:", err);
        }
    };

    /* ===============================
       OPEN ASSET MODAL (RIGHT + BUTTON)
    =============================== */
    addAssetBtn.onclick = () => {
        if (!selectedShootDate) {
            alert("Please select a shoot date first!");
            return;
        }

        assetModal.style.display = "block";

        // Default section
        existingSection.classList.remove("hidden");
        uploadSection.classList.add("hidden");

        existingFilesContainer.innerHTML = `<p class="placeholder">Select a category to load files.</p>`;
    };

    /* ===============================
       TOGGLE EXISTING / UPLOAD
    =============================== */
    chooseExistingBtn.onclick = () => {
        existingSection.classList.remove("hidden");
        uploadSection.classList.add("hidden");
    };
    uploadNewBtn.onclick = () => {
        uploadSection.classList.remove("hidden");
        existingSection.classList.add("hidden");
    };

    /* ===============================
       LOAD EXISTING FILES BY CATEGORY
    =============================== */
    document.querySelectorAll(".asset-cat-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const category = btn.dataset.category;
            existingFilesContainer.innerHTML = `<p class="placeholder">Loading ${category}...</p>`;

            try {
                const res = await fetch(`../handlers/scene_handler.php?action=get_files&category=${category}`);
                const files = await res.json();
                existingFilesContainer.innerHTML = "";

                files.forEach(file => {
                    const preview = document.createElement("div");
                    preview.className = "preview";
                    preview.onclick = () => attachFileToShoot(file);

                    if (file.is_image) {
                        const img = document.createElement("img");
                        img.src = file.public_url;
                        preview.appendChild(img);
                    } else if (file.is_video) {
                        const video = document.createElement("video");
                        video.src = file.public_url;
                        video.controls = true;
                        preview.appendChild(video);
                    } else if (file.is_audio) {
                        const audio = document.createElement("audio");
                        audio.src = file.public_url;
                        audio.controls = true;
                        preview.appendChild(audio);
                    } else {
                        const div = document.createElement("div");
                        div.textContent = file.original_name;
                        div.style.fontSize = '12px';
                        div.style.textAlign = 'center';
                        div.style.padding = '5px';
                        preview.appendChild(div);
                    }

                    existingFilesContainer.appendChild(preview);
                });
            } catch (err) {
                console.error("Failed to load files:", err);
            }
        });
    });

    /* ===============================
       ATTACH FILE TO SHOOT DATE
    =============================== */
    async function attachFileToShoot(file) {
        if (!selectedShootDate) return;

        const data = new FormData();
        data.append("action", "attach_file");
        data.append("shoot_date_id", selectedShootDate.shoot_date_id);
        data.append("file_id", file.file_id);

        try {
            await fetch("../handlers/scene_handler.php", { method: "POST", body: data });
            assetModal.style.display = "none";
            loadAssets(selectedShootDate);
        } catch (err) {
            console.error("Failed to attach file:", err);
        }
    }

    /* ===============================
       UPLOAD NEW FILE
    =============================== */
    uploadNewForm.onsubmit = async (e) => {
        e.preventDefault();
        if (!selectedShootDate) {
            alert("Select a shoot date first");
            return;
        }

        const fileInput = document.getElementById("newAssetFile");
        const nameInput = document.getElementById("newAssetName");
        const descInput = document.getElementById("newAssetDesc");
        const categoryInput = document.getElementById("newAssetCategory");

        if (!fileInput.files.length) return alert("Select a file");

        const data = new FormData();
        data.append("action", "upload_new_file");
        data.append("shoot_date_id", selectedShootDate.shoot_date_id);
        data.append("file", fileInput.files[0]);
        data.append("name", nameInput.value);
        data.append("desc", descInput.value);
        data.append("category", categoryInput.value);

        try {
            const res = await fetch("../handlers/scene_handler.php", { method: "POST", body: data });
            const result = await res.json();
            if (result.success) {
                uploadNewForm.reset();
                assetModal.style.display = "none";
                loadAssets(selectedShootDate);
            } else {
                alert("Failed to upload: " + (result.error ?? ""));
            }
        } catch (err) {
            console.error("Failed to upload file:", err);
        }
    };

    /* ===============================
       INIT
    =============================== */
    loadShootDates(); // populate sidebar
});
