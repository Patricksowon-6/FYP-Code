document.addEventListener("DOMContentLoaded", () => {
  const columns = document.querySelectorAll(".column");
  const modal = document.querySelector(".confirm-modal");
  let currentTask = null;
  const fetchUrl = '../handlers/tasks.php';

  // --- Create task element
  function createTask(content, deadline = null, task_id = null) {
    const task = document.createElement("div");
    task.className = "task";
    task.draggable = true;
    task.dataset.taskId = task_id;
    task.innerHTML = `
      <div>
        <strong>${content}</strong>
        ${deadline ? `<small class="deadline">Deadline: ${deadline}</small>` : ""}
      </div>
      <menu>
        <button data-edit><i class="fa-solid fa-pen-to-square"></i></button>
        <button data-delete><i class="fa-solid fa-trash"></i></button>
      </menu>
    `;
    task.addEventListener("dragstart", () => task.classList.add("dragging"));
    task.addEventListener("dragend", () => task.classList.remove("dragging"));
    return task;
  }

  function updateAllCounters() {
    columns.forEach(column => {
      const count = column.querySelector(".tasks").children.length;
      column.querySelector("h3").dataset.tasks = count;
    });
  }

  async function saveTaskToDB(content, deadline, status, el) {
    const formData = new FormData();
    formData.append('content', content);
    formData.append('deadline', deadline);
    formData.append('status', status);
    const res = await fetch(fetchUrl, { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) el.dataset.taskId = data.task_id;
  }

  async function updateTaskInDB(task_id, content, deadline, status) {
    await fetch(fetchUrl, {
      method: 'PUT',
      body: new URLSearchParams({ task_id, content, deadline, status })
    });
  }

  async function deleteTaskFromDB(task_id) {
    await fetch(fetchUrl, {
      method: 'DELETE',
      body: new URLSearchParams({ task_id })
    });
  }

  async function loadTasks() {
    const res = await fetch(fetchUrl + '?fetch=1');
    const text = await res.text();
    try {
      const tasks = JSON.parse(text);
      const statusMap = { todo: 0, inprogress: 1, forreview: 2, done: 3 };
      tasks.forEach(task => {
        const el = createTask(task.content, task.deadline, task.task_id);
        columns[statusMap[task.status]].querySelector(".tasks").appendChild(el);
      });
      updateAllCounters();
    } catch (err) {
      console.error("JSON Parse Error:", err);
    }
  }

  loadTasks();

  // 🔹 Global Add Task
  document.querySelector("#addTaskBtn").addEventListener("click", async () => {
    const content = document.querySelector("#taskContent").value.trim();
    const deadline = document.querySelector("#taskDeadline").value;
    const status = document.querySelector("#taskStatus").value;
    if (!content) return;

    const taskEl = createTask(content, deadline);
    document.querySelector(`.column[data-status="${status}"] .tasks`).appendChild(taskEl);
    await saveTaskToDB(content, deadline, status, taskEl);

    document.querySelector("#taskContent").value = "";
    document.querySelector("#taskDeadline").value = "";
    updateAllCounters();
  });

  // --- Edit & Delete Handlers
  document.body.addEventListener("click", e => {
    const task = e.target.closest(".task");
    if (!task) return;

    // ✏️ Edit
    if (e.target.closest("button[data-edit]")) {
      const currentText = task.querySelector("strong").innerText;
      const currentDeadline = task.querySelector(".deadline")?.innerText.replace("Deadline: ", "") || "";

      const editBox = document.createElement("div");
      editBox.classList.add("edit-box");
      editBox.innerHTML = `
        <input type="text" class="edit-text" value="${currentText}">
        <input type="date" class="edit-date" value="${currentDeadline}">
        <div class="edit-actions">
          <button type="button" class="save-edit">💾 Save</button>
          <button type="button" class="cancel-edit">❌ Cancel</button>
        </div>
      `;
      task.querySelector("div").replaceWith(editBox);

      const textInput = editBox.querySelector(".edit-text");
      const dateInput = editBox.querySelector(".edit-date");
      const saveBtn = editBox.querySelector(".save-edit");
      const cancelBtn = editBox.querySelector(".cancel-edit");

      textInput.focus();

      async function saveEdit() {
        const newText = textInput.value.trim() || "Untitled";
        const newDeadline = dateInput.value;
        const column = task.closest(".column");

        const newContent = document.createElement("div");
        newContent.innerHTML = `
          <strong>${newText}</strong>
          ${newDeadline ? `<small class="deadline">Deadline: ${newDeadline}</small>` : ""}
        `;
        editBox.replaceWith(newContent);
        await updateTaskInDB(task.dataset.taskId, newText, newDeadline, column.dataset.status);
      }

      function cancelEdit() {
        const originalContent = document.createElement("div");
        originalContent.innerHTML = `
          <strong>${currentText}</strong>
          ${currentDeadline ? `<small class="deadline">Deadline: ${currentDeadline}</small>` : ""}
        `;
        editBox.replaceWith(originalContent);
      }

      saveBtn.addEventListener("click", saveEdit);
      cancelBtn.addEventListener("click", cancelEdit);
    }

    // 🗑️ Delete
    if (e.target.closest("button[data-delete]")) {
      currentTask = task;
      modal.querySelector(".preview").innerText = task.querySelector("strong").innerText;
      modal.showModal();
    }
  });

  // --- Confirm Delete
  modal.addEventListener("submit", async () => {
    const column = currentTask.closest(".column");
    await deleteTaskFromDB(currentTask.dataset.taskId);
    currentTask.remove();
    updateAllCounters();
    modal.close();
    currentTask = null;
  });

  modal.querySelector("#cancel").addEventListener("click", () => {
    modal.close();
    currentTask = null;
  });

  // --- Drag & Drop
  function handleDragOver(e) {
    e.preventDefault();
    const dragged = document.querySelector(".dragging");
    const target = e.target.closest(".task,.tasks");
    if (!target || target === dragged) return;

    if (target.classList.contains("tasks")) {
      target.appendChild(dragged);
    } else {
      const { top, height } = target.getBoundingClientRect();
      e.clientY < top + height / 2 ? target.before(dragged) : target.after(dragged);
    }

    const column = dragged.closest(".column");
    const content = dragged.querySelector("strong").innerText;
    const deadline = dragged.querySelector(".deadline")?.innerText.replace("Deadline: ", "");
    updateTaskInDB(dragged.dataset.taskId, content, deadline, column.dataset.status);
    updateAllCounters();
  }

  document.querySelectorAll(".tasks").forEach(el => el.addEventListener("dragover", handleDragOver));
});
