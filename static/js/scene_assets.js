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

    const CATEGORY_THUMBS = {
        images: null, // real image
        videos: '../media/images/video_icon.png',
        audio: '../media/images/audio_icon.png',
        documents: '../media/images/document_icon.png',
        models: '../media/images/model_icon.png',
        other: '../media/images/other_icon.png'
    };


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
                const status = asset.status || "not_ready";
                const card = document.createElement("div");
                card.className = `asset-card status-${status}`;

                let thumbSrc = CATEGORY_THUMBS[asset.category] || CATEGORY_THUMBS.other;
                if (asset.category === "images") thumbSrc = asset.public_url;

                card.innerHTML = `
                    <span class="asset-status-badge">
                        ${status.replace("_", " ").toUpperCase()}
                    </span>
                    <img src="${thumbSrc}" alt="${asset.title}">
                    <h4>${asset.title}</h4>
                    <div class="asset-status-buttons" style="margin-top:10px; display:flex; gap:5px;">
                        <button class="footer-btn ready">Ready</button>
                        <button class="footer-btn in_progress">In Progress</button>
                        <button class="footer-btn not_ready">Not Ready</button>
                    </div>
                `;

                // Preview on click
                card.querySelector("img, h4").addEventListener("click", () => openPreview(asset));

                // Status button handlers
                card.querySelectorAll(".footer-btn").forEach(btn => {
                    btn.addEventListener("click", async (e) => {
                        e.stopPropagation();
                        const newStatus = btn.classList[1]; // 'ready', 'in_progress', 'not_ready'

                        const data = new FormData();
                        data.append('action', 'update_asset_status'); // ✅ required
                        data.append('asset_id', asset.asset_id);       // asset primary key
                        data.append('status', newStatus);             // new status

                        try {
                            const res = await fetch("../handlers/scene_handler.php", {
                                method: "POST",
                                body: data
                            });

                            const result = await res.json();
                            if (result.success) {
                                asset.status = newStatus;
                                card.className = `asset-card status-${newStatus}`;
                                card.querySelector(".asset-status-badge").textContent = newStatus.replace("_", " ").toUpperCase();
                            } else {
                                alert("Failed to update status: " + (result.error ?? ""));
                            }
                        } catch (err) {
                            console.error("Failed to update status:", err);
                        }
                    });
                });

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
        const container = document.getElementById("previewContainer");

        container.innerHTML = "";
        previewFallback.style.display = "none";

        if (asset.category === "images") {
            const img = document.createElement("img");
            img.src = asset.public_url;
            container.appendChild(img);

        } else if (asset.category === "videos") {
            const video = document.createElement("video");
            video.src = asset.public_url;
            video.controls = true;
            container.appendChild(video);

        } else if (asset.category === "audio") {
            const audio = document.createElement("audio");
            audio.src = asset.public_url;
            audio.controls = true;
            container.appendChild(audio);

        } else {
            const iframe = document.createElement("iframe");
            iframe.src = asset.public_url;
            container.appendChild(iframe);
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

                    if (file.category === "images") {
                        const img = document.createElement("img");
                        img.src = file.public_url;
                        preview.appendChild(img);
                    } else {
                        const div = document.createElement("div");
                        div.textContent = file.original_name;
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


