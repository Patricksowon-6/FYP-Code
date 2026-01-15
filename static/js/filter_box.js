const filterModal = document.getElementById("filterModal");
const openFilterBtn = document.getElementById("openFilterBtn");
const closeFilterBtn = document.querySelector(".close-modal");

const filterFileName = document.getElementById("filterFileName");
const filterUsername = document.getElementById("filterUsername");
const filterExtension = document.getElementById("filterExtension");
const addExtension = document.getElementById("addExtension");
const filterAssetType = document.getElementById("filterAssetType");

const applyFilterBtn = document.getElementById("applyFilterBtn");
const clearFilterBtn = document.getElementById("clearFilterBtn");

const tableContainer = document.getElementById("tableBody"); // or wherever your files go
const API_URL = '../handlers/filter_files.php';

// -------------------- MODAL --------------------
openFilterBtn.addEventListener("click", e => {
    e.preventDefault();
    filterModal.style.display = "flex";
});
closeFilterBtn.addEventListener("click", () => filterModal.style.display = "none");
window.addEventListener("click", e => {
    if (e.target === filterModal) filterModal.style.display = "none";
});

// -------------------- FETCH AND RENDER --------------------
async function fetchFiles(filters = {}) {
    const url = new URL(API_URL, window.location.origin);
    Object.entries(filters).forEach(([key, val]) => { if(val) url.searchParams.append(key, val); });

    const res = await fetch(url);
    const data = await res.json();
    renderFiles(data);
}

function renderFiles(files) {
    if (!files.length) {
        tableContainer.innerHTML = "<p>No files found</p>";
        return;
    }

    tableContainer.innerHTML = files.map(file => `
        <div class="video-box">
            <center>
                ${file.category === "videos" ? 
                    `<video controls width="300" preload="metadata">
                        <source src="${file.url}" type="video/mp4">
                        Your browser does not support video.
                    </video>` 
                    : `<img src="${file.url}" width="200" alt="${file.original_name}" />`
                }
                <h1 class="file-name">${file.original_name}</h1>
                <h3 class="person-name">By ${file.user_id}</h3>
            </center>
        </div>
    `).join("");
}

// -------------------- FILTER BUTTONS --------------------
applyFilterBtn.addEventListener("click", () => {
    const params = new URLSearchParams();

    if (filterFileName.value.trim()) params.append('file_name', filterFileName.value.trim());
    if (filterUsername.value.trim()) params.append('user_name', filterUsername.value.trim());
    if (filterExtension.value) params.append('extension', filterExtension.value);
    if (addExtension.value.trim()) params.set('extension', addExtension.value.trim()); // override if custom
    if (filterAssetType.value) params.append('asset_type', filterAssetType.value);

    // Redirect to filtered page
    const url = `../pages/media.php?type=filtered&${params.toString()}`;
    window.location.href = url;
});


clearFilterBtn.addEventListener("click", () => {
    filterFileName.value = "";
    filterUsername.value = "";
    filterExtension.value = "";
    addExtension.value = "";
    filterAssetType.value = "";
    fetchFiles();
    filterModal.style.display = "none";
});

// -------------------- INITIAL LOAD --------------------
fetchFiles();
