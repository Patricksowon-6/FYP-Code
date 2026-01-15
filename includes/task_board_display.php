<?php 
require_once(__DIR__ . '/../config.php');
?> 

<div class="container">
    <center>
        <h1>Task Board</h1>
    </center>

  <!-- 🔹 Global Add Task Form -->
  <div class="add-task-form">
    <input type="text" id="taskContent" placeholder="Task description...">
    <input type="date" id="taskDeadline">
    <select id="taskStatus">
      <option value="todo">To Do</option>
      <option value="inprogress">In Progress</option>
      <option value="forreview">For Review</option>
      <option value="done">Done</option>
    </select>
    <button id="addTaskBtn"><i class="fa-solid fa-plus"></i> Add Task</button>
  </div>
</div>

<div class="container columns">
<?php
$statuses = ['todo'=>'To Do','inprogress'=>'In Progress','forreview'=>'For Review','done'=>'Done'];
foreach($statuses as $key=>$label): ?>
  <div class="column" data-status="<?= $key ?>">
      <div class="column-title"><h3 data-tasks="0"><?= $label ?></h3></div>
      <div class="tasks"></div>
  </div>
<?php endforeach; ?>
</div>

<!-- Confirm Delete Modal -->
<dialog class="confirm-modal">
  <form method="dialog">
    <h3>Delete Task?</h3>
    <div class="preview"></div>
    <menu>
      <button type="button" id="cancel">Cancel</button>
      <button type="submit" id="confirm">Yes, delete it.</button>
    </menu>
  </form>
</dialog>
