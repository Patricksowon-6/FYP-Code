<?php 
    require_once __DIR__ . '/../config.php'; 

    
?>

<div class="upload-container">
    <!-- DOCUMENTS -->
    <div class="upload-box" data-type="documents">
        <h2>Documents</h2>
        <input type="file" multiple accept=".pdf, .doc, .docx, .txt, .html, .htm, .csv, .md, .kitsp">
        <div class="previews"></div><br><br>
        <button type="button" class="upload_button" id="document_upload">Upload</button>
    </div>

    <!-- IMAGES -->
    <div class="upload-box" data-type="images">
        <h2>Images</h2>
        <input type="file" multiple accept=".gif, .png, .jpg, .jpeg, .webp, .svg">
        <div class="previews"></div><br><br>
        <button type="button" class="upload_button" id="image_upload">Upload</button>
    </div>

    <!-- AUDIO -->
    <div class="upload-box" data-type="audio">
        <h2>Audio</h2>
        <input type="file" multiple accept=".mp3, .wav, .mscz">
        <div class="previews"></div><br><br>
        <button type="button" class="upload_button" id="audio_upload">Upload</button>
    </div>

    <!-- VIDEO -->
    <div class="upload-box" data-type="videos">
        <h2>Video</h2>
        <input type="file" multiple accept=".mp4, .webm">
        <div class="previews"></div><br><br>
        <button type="button" class="upload_button" id="video_upload">Upload</button>
    </div>

    <!-- MODELS -->
    <div class="upload-box" data-type="models">
        <h2>Models</h2>
        <input type="file" multiple accept=".blend, .fbx, .obj">
        <div class="previews"></div><br><br>
        <button type="button" class="upload_button" id="model_upload">Upload</button>
    </div>

    <div class="upload-box" data-type="models">
        <h2>Other</h2>
        <input type="file" multiple accept="*">
        <div class="previews"></div><br><br>
        <button type="button" class="upload_button" id="other_upload">Upload</button>
    </div>
</div>

<script src="<?= JS_PATH ?>file_upload.js"></script>
