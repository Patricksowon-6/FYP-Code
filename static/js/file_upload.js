document.addEventListener('DOMContentLoaded', () => {
    const uploadBoxes = document.querySelectorAll('.upload-box');

    uploadBoxes.forEach(box => {
        const input = box.querySelector('input[type="file"]');
        const previews = box.querySelector('.previews');
        const btn = box.querySelector('.upload_button');
        let selectedFiles = [];

        // Handle file selection and previews
        input.addEventListener('change', () => {
            Array.from(input.files).forEach(file => {
                selectedFiles.push(file);

                const preview = document.createElement('div');
                preview.className = 'preview';

                const removeBtn = document.createElement('button');
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', () => {
                    selectedFiles = selectedFiles.filter(f => f !== file);
                    preview.remove();
                });
                preview.appendChild(removeBtn);

                // File preview
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                } else if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(file);
                    video.controls = true;
                    preview.appendChild(video);
                } else if (file.type.startsWith('audio/')) {
                    const audio = document.createElement('audio');
                    audio.src = URL.createObjectURL(file);
                    audio.controls = true;
                    preview.appendChild(audio);
                } else {
                    const div = document.createElement('div');
                    div.textContent = file.name;
                    div.style.fontSize = '12px';
                    div.style.textAlign = 'center';
                    div.style.padding = '5px';
                    preview.appendChild(div);
                }

                previews.appendChild(preview);
            });

            input.value = '';
        });

        // Handle upload
        btn.addEventListener('click', async () => {
            if (selectedFiles.length === 0) {
                alert('Please select at least one file to upload.');
                return;
            }

            if (window.showLoader) window.showLoader();

            const uploadedFiles = [];

            for (const file of selectedFiles) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', btn.id);
                formData.append('project_id', window.PROJECT_ID);

                try {
                    const res = await fetch('../handlers/upload_file.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!res.ok) throw new Error(`HTTP error ${res.status}`);

                    const data = await res.json();

                    // ----------------------------------
                    // 🚫 PERMISSION CHECK (GOES HERE)
                    // ----------------------------------
                    if (data.permission === false) {
                        openPermissionsModal();
                        continue; // stop this file upload
                    }

                    // ----------------------------------
                    // 🔁 DUPLICATE CHECK
                    // ----------------------------------
                    if (data.duplicate) {
                        await new Promise(resolve => {
                            showDuplicateModal(data.original_name, resolve);
                        }).then(async choice => {
                            if (choice === "version") {
                                const formData2 = new FormData();
                                formData2.append("file", file);
                                formData2.append("type", btn.id);
                                formData2.append("force_version", 1);
                                formData2.append("project_id", window.PROJECT_ID);

                                const versionRes = await fetch('../handlers/upload_file.php', {
                                    method: 'POST',
                                    body: formData2
                                });

                                const versionData = await versionRes.json();
                                if (versionData.error) {
                                    alert(`❌ Version save failed: ${versionData.error}`);
                                } else {
                                    uploadedFiles.push({ name: file.name, url: versionData.url });
                                }
                            }
                        });

                        continue;
                    }


                    if (data.error) {
                        alert(`❌ ${file.name}: ${data.error}`);
                    } else {
                        uploadedFiles.push({ name: file.name, url: data.url });
                    }

                } catch (err) {
                    console.error(err);
                    alert(`❌ ${file.name}: Upload failed`);
                }
            }

            if (window.hideLoader) window.hideLoader();

            if (uploadedFiles.length) {
                alert(`✅ ${uploadedFiles.length} file(s) uploaded successfully!`);
                uploadedFiles.forEach(f => {
                    const link = document.createElement('a');
                    link.href = f.url;
                    link.textContent = f.name;
                    link.target = '_blank';
                    previews.appendChild(link);
                    previews.appendChild(document.createElement('br'));
                });
            }

            // Clear selections and previews
            selectedFiles = [];
            previews.querySelectorAll('.preview').forEach(el => el.remove());
        });
    });
}); // END DOMContentLoaded




// ----------------------------
// Duplicate Modal 
// ----------------------------
let duplicateResolve = null; 

function showDuplicateModal(fileName, resolveCallback) {
    duplicateResolve = resolveCallback;

    const modal = document.getElementById('duplicateModal');
    document.getElementById('duplicateText').innerHTML =
        `The file "<strong>${fileName}</strong>" already exists.<br><br>
        Would you like to cancel the upload or save this as a new version (v1, v2, v3, etc)?`;

    modal.classList.remove('hidden');
}

function cancelDuplicateUpload() {
    document.getElementById('duplicateModal').classList.add('hidden');
    if (duplicateResolve) duplicateResolve("cancel");
}

function saveVersion() {
    document.getElementById('duplicateModal').classList.add('hidden');
    if (duplicateResolve) duplicateResolve("version");
}



// ----------------------------
// Permission Modal 
// ----------------------------
function openPermissionsModal() {
    document.getElementById('permissionsModal').classList.remove('hidden');
}

function closePermissionsModal() {
    document.getElementById('permissionsModal').classList.add('hidden');
}
