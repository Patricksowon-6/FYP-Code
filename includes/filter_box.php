<div id="filterModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Filter Assets</h2>

        <form id="assetFilterForm" class="controls">
            <!-- Search fields -->
            <input id="filterFileName" type="text" placeholder="Search by File Name" />
            <input id="filterUsername" type="text" placeholder="Search by Username" />

            <!-- Asset type -->
            <select id="filterAssetType">
                <option value="">— Any Asset Type —</option>
                <option value="images">Images</option>
                <option value="documents">Documents</option>
                <option value="videos">Videos</option>
                <option value="audio">Audio</option>
                <option value="models">Models</option>
            </select>

            <!-- File extension -->
            <select id="filterExtension">
                <option value="">— Any Extension —</option>
                <option value="jpg">JPG</option>
                <option value="png">PNG</option>
                <option value="gif">GIF</option>
                <option value="mp3">MP3</option>
                <option value="mp4">MP4</option>
                <option value="pdf">PDF</option>
                <option value="blend">BLEND</option>
                <option value="docx">DOCX</option>
                <option value="obj">OBJ</option>
                <option value="fbx">FBX</option>
            </select>

            <!-- Add custom extension -->
            <input id="addExtension" type="text" placeholder="Any Other Extension (e.g., txt, wav)" />

            <div class="actions">
                <button type="button" id="applyFilterBtn">Apply</button>
                <button type="button" id="clearFilterBtn">Clear</button>
            </div>
        </form>
    </div>
</div>
