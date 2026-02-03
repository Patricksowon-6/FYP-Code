<div class="container">

    <!-- Sidebar: Shoot Date List -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Shoot Dates</h2>
            <button id="addShootDateBtn" class="add-btn">+</button>
        </div>

        <!-- Scrollable shoot dates -->
        <div class="shoot-date-container">
            <ul id="shootDateList"></ul>
        </div><br><br>

        <!-- Fixed bottom buttons -->
        <!-- <div class="sidebar-footer">
            <button class="footer-btn ready">Ready</button>
            <button class="footer-btn in_progress">In Progress</button>
            <button class="footer-btn not_ready">Not Ready</button>
        </div> -->
    </aside>


    <!-- Main Dashboard -->
    <main class="main-panel">
        <div class="main-header">
            <h2 id="dashboardTitle">Select a Shoot Date</h2>
            <button id="addAssetBtn" class="add-btn">+</button>
        </div>

        <div id="assetGrid" class="asset-grid"></div>
    </main>
</div>


<!-- Preview Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content large">
        <span class="close-btn">&times;</span>
        <h3 id="previewTitle"></h3>

        <div id="previewContainer" class="preview-container"></div>

        <p id="previewFallback" style="display:none;">Preview not available.</p>
    </div>
</div>


<!-- Add Shoot Date Modal -->
<div id="shootDateModal" class="modal">
    <form id="shootDateForm" class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>Add New Shoot Date</h3>

        <label>Date:</label>
        <input type="date" id="shootDateInput" required>

        <label>Scene Name:</label>
        <input type="text" id="shootSceneInput" placeholder="e.g., Scene 12" required>

        <button type="submit" class="save-btn">Save</button>
    </form>
</div>

<!-- Add Asset Modal (Updated with categories + upload/new choice) -->
<div id="assetModal" class="modal">
    <div class="modal-content wide">

        <span class="close-btn">&times;</span>
        <h3>Add Asset to Shoot Date</h3>

        <!-- Option Buttons -->
        <div class="asset-choice">
            <button id="chooseExistingBtn" class="choice-btn">Choose Existing</button>
            <button id="uploadNewBtn" class="choice-btn">Upload New</button>
        </div>

        <!-- SECTION 1: Choose Existing Asset -->
        <div id="existingFilesSection" class="hidden section">

            <h4>Select Category:</h4>

            <div class="asset-category-list">
                <button class="asset-cat-btn" data-category="images">
                    <i class="fa-solid fa-image"></i> Images
                </button>

                <button class="asset-cat-btn" data-category="videos">
                    <i class="fa-solid fa-video"></i> Videos
                </button>

                <button class="asset-cat-btn" data-category="audio">
                    <i class="fa-solid fa-headphones"></i> Audio
                </button>

                <button class="asset-cat-btn" data-category="documents">
                    <i class="fa-solid fa-file-lines"></i> Documents
                </button>

                <button class="asset-cat-btn" data-category="models">
                    <i class="fa-solid fa-pen-ruler"></i> Models & Art
                </button>

                <button class="asset-cat-btn" data-category="other">
                    <i class="fa-solid fa-globe"></i> Other
                </button>
            </div>

            <!-- Results container -->
            <div id="existingFilesContainer" class="existing-files"></div>
        </div>

        <!-- SECTION 2: Upload New Asset -->
        <div id="uploadNewSection" class="hidden section">

            <form id="uploadNewForm">

                <label>Upload File:</label>
                <input type="file" id="newAssetFile" required>

                <label>Asset Name:</label>
                <input type="text" id="newAssetName" required>

                <label>Description:</label>
                <textarea id="newAssetDesc" placeholder="Optional…"></textarea>

                <label>Category:</label>
                <select id="newAssetCategory" required>
                    <option value="images">Images</option>
                    <option value="videos">Videos</option>
                    <option value="audio">Audio</option>
                    <option value="documents">Documents</option>
                    <option value="models">Models & Art</option>
                    <option value="other">Other</option>
                </select><br><br>

                <button type="submit" class="save-btn upload-btn">Upload</button>
            </form><br><br>

        </div><br><br>

    </div><br><br>
</div>
