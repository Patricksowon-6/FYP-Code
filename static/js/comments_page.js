document.addEventListener("DOMContentLoaded", () => {
    const sendBtn = document.getElementById('send-comment');
    const textarea = document.querySelector('textarea');
    const commentsList = document.getElementById('comments-list');
    const USER_ID = Number(commentsList.dataset.userId);
    const FILE_ID = commentsList.dataset.fileId;

    // Initial load
    loadComments();

    // ------------------------
    // Load comments from server
    // ------------------------
    function loadComments() {
        fetch(`../handlers/comments.php?file_id=${FILE_ID}`)
            .then(res => res.json())
            .then(data => {
                commentsList.innerHTML = "";
                data.forEach(comment => renderComment(comment));
            });
    }


    // ------------------------
    // Send new comment
    // ------------------------
    sendBtn.addEventListener('click', () => {
        const text = textarea.value.trim();
        if (!text) return;

        fetch("../handlers/comments.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                file_id: FILE_ID,
                comment_text: text
            })
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                textarea.value = "";
                loadComments();
            }
        });
    });

    // ------------------------
    // Render comment DOM element
    // ------------------------
    function renderComment(c) {
        const item = document.createElement('div');
        item.className = "comment-item";

        const isOwner = Number(c.user_id) === Number(USER_ID);

        item.innerHTML = `
            <img src="${c.profile_img}" class="comment-avatar" alt="avatar">
            <div class="comment-content">
                <span class="comment-username">${c.user_name}</span><br>
                <p class="comment-text">${c.comment_text}</p> <br><br>

                ${isOwner ? `
                <div class="comment-actions">
                    <i class="fa-solid fa-pen-to-square edit-btn" data-id="${c.comment_id}"></i>
                    <i class="fa-solid fa-trash delete-btn" data-id="${c.comment_id}"></i>
                </div>
                ` : ""}
            </div>
        `;

        commentsList.appendChild(item);
    }


    // ------------------------
    // Edit functionality
    // ------------------------
    function editComment(item, comment_id, currentText) {
        const textEl = item.querySelector(".comment-text");
        const actions = item.querySelector(".comment-actions");

        // Replace text with textarea
        textEl.outerHTML = `
            <textarea class="edit-area">${currentText}</textarea>
        `;

        // Replace actions with Save + Cancel
        actions.innerHTML = `
            <button class="save-edit">Save</button>
            <button class="cancel-edit">Cancel</button>
        `;

        // SAVE EDIT
        actions.querySelector(".save-edit").addEventListener("click", () => {
            const newText = item.querySelector(".edit-area").value.trim();
            if (!newText) return;

            fetch("../handlers/comments.php", {
                method: "PUT",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    comment_id,
                    comment_text: newText
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) loadComments();
            });
        });

        // CANCEL
        actions.querySelector(".cancel-edit").addEventListener("click", () => {
            loadComments();
        });
    }


    // ------------------------
    // Delete comment
    // ------------------------
    function deleteComment(comment_id) {
        if (!confirm("Delete this comment?")) return;

        fetch("../handlers/comments.php", {
            method: "DELETE",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ comment_id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadComments();
        });
    }


    // ------------------------
    // Event Delegation (edit + delete buttons)
    // ------------------------
    commentsList.addEventListener("click", (e) => {

        // DELETE click
        if (e.target.classList.contains("delete-btn")) {
            const comment_id = e.target.dataset.id;
            deleteComment(comment_id);
            return;
        }

        // EDIT click
        if (e.target.classList.contains("edit-btn")) {
            const item = e.target.closest(".comment-item");
            const text = item.querySelector(".comment-text").textContent.trim();
            const comment_id = e.target.dataset.id;

            editComment(item, comment_id, text);
            return;
        }

    });

});
