<?php
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Upload</title>
<link rel="stylesheet" href="<?= CSS_PATH; ?>file_upload.css">
<link rel="stylesheet" href="<?= CSS_PATH; ?>header.css">
<link rel="stylesheet" href="<?= CSS_PATH; ?>spinner.css">
</head>
<body>

    <?php
        include(INCLUDES_PATH . 'project_header.php'); 
        include(INCLUDES_PATH . 'upload_box.php'); 
    ?>

    <div id="upload-loader" style="display:none;">
        <div class="spinner"></div>
    </div>

<div id="duplicateModal" class="modal hidden">
    <div class="modal-content">
        <h2>Duplicate File Detected</h2>
        <p id="duplicateText"></p>

        <div class="modal-buttons">
            <button class="btn cancel" onclick="cancelDuplicateUpload()">Cancel Upload</button>
            <button class="btn confirm" onclick="saveVersion()">Save as Version</button>
        </div>
    </div>
</div>

<div id="permissionsModal" class="modal hidden">
    <div class="modal-content">
        <h2>Upload Requires Approval</h2>

        <p>
            You don't currently have permission to upload this type of file.
            <br><br>
            Your upload has been placed in the <strong>Pending</strong> tab and will
            be available once a department member or (co)-owner approves it.
        </p>

        <div class="modal-buttons">
            <button class="btn confirm" onclick="closePermissionsModal()">OK</button>
        </div>
    </div>
</div>



<script src="<?= JS_PATH ?>file_upload.js"></script>
<script src="<?= JS_PATH ?>spinner.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

</body>
</html>

