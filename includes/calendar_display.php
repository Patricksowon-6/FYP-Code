<?php 
  require_once(__DIR__ . '/../config.php');
?>

<div class="calendar-app">
  <h1>📅 My Important Dates</h1>

  <div class="calendar-header">
    <button id="prevMonth">←</button>
    <h2 id="monthYear"></h2>
    <button id="nextMonth">→</button>
  </div>

  <div class="day-labels">
    <div>Sun</div>
    <div>Mon</div>
    <div>Tue</div>
    <div>Wed</div>
    <div>Thu</div>
    <div>Fri</div>
    <div>Sat</div>
  </div>

  <div id="calendar" class="calendar-grid"></div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>
    <h3 id="modalTitle">Add Details</h3>
    <p id="selectedDate"></p>
    <textarea id="eventDetails" placeholder="Enter details for this date..."></textarea>
    <button id="saveEvent">Save</button>
    <div id="deadlineTasks" style="margin-top:10px;color:#f87171;font-weight:600;"></div>
  </div>
</div>

<script>
  const taskDeadlines = <?= json_encode($taskDeadlines) ?>;
</script>

<script src="<?= JS_PATH ?>calendar.js"></script>
