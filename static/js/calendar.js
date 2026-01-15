document.addEventListener("DOMContentLoaded", () => {
  const calendar = document.getElementById("calendar");
  const monthYear = document.getElementById("monthYear");
  const prevMonthBtn = document.getElementById("prevMonth");
  const nextMonthBtn = document.getElementById("nextMonth");

  const modal = document.getElementById("eventModal");
  const closeModal = document.getElementById("closeModal");
  const selectedDateDisplay = document.getElementById("selectedDate");
  const eventDetails = document.getElementById("eventDetails");
  const saveEventBtn = document.getElementById("saveEvent");
  const deadlineTasksDiv = document.getElementById("deadlineTasks");

  let currentDate = new Date();
  let selectedDate = null;

  const STORAGE_KEY = "calendar_events";
  let events = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};

  function renderCalendar() {
    calendar.innerHTML = "";
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    monthYear.textContent = currentDate.toLocaleDateString("default", {
      month: "long",
      year: "numeric"
    });

    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);
    const prevLastDay = new Date(year, month, 0).getDate();

    const startDay = firstDayOfMonth.getDay();
    const totalDays = lastDayOfMonth.getDate();

    const today = new Date();

    // Previous month trailing days
    for (let x = startDay; x > 0; x--) {
      const day = document.createElement("div");
      day.classList.add("day", "inactive");
      day.textContent = prevLastDay - x + 1;
      calendar.appendChild(day);
    }

    // Current month days
    for (let i = 1; i <= totalDays; i++) {
      const day = document.createElement("div");
      day.classList.add("day");

      const dateNum = document.createElement("div");
      dateNum.classList.add("date-num");
      dateNum.textContent = i;
      day.appendChild(dateNum);

      const dateKey = `${year}-${month + 1}-${i}`;

      // User notes
      if (events[dateKey]) {
        const dot = document.createElement("div");
        dot.classList.add("event-dot");
        day.appendChild(dot);
      }

      // Task deadlines
      if (taskDeadlines[dateKey]) {
        const dot = document.createElement("div");
        dot.classList.add("deadline-dot");
        day.appendChild(dot);
      }

      // Highlight today
      if (
        i === today.getDate() &&
        month === today.getMonth() &&
        year === today.getFullYear()
      ) {
        day.classList.add("today");
      }

      day.addEventListener("click", () => openModal(dateKey));
      calendar.appendChild(day);
    }

    // Next month leading days
    const totalCells = startDay + totalDays;
    const nextDays = 7 - (totalCells % 7);
    if (nextDays < 7) {
      for (let j = 1; j <= nextDays; j++) {
        const day = document.createElement("div");
        day.classList.add("day", "inactive");
        day.textContent = j;
        calendar.appendChild(day);
      }
    }
  }

  function openModal(dateKey) {
    selectedDate = dateKey;
    const [y, m, d] = dateKey.split("-");
    selectedDateDisplay.textContent = new Date(y, m - 1, d).toDateString();
    eventDetails.value = events[dateKey] || "";

    // Show task deadlines for this date
    if (taskDeadlines[dateKey]) {
      deadlineTasksDiv.innerHTML = "Tasks due: <ul>" + 
        taskDeadlines[dateKey].map(t => `<li>${t}</li>`).join('') + "</ul>";
    } else {
      deadlineTasksDiv.innerHTML = "";
    }

    modal.style.display = "flex";
  }

  function closeModalWindow() {
    modal.style.display = "none";
  }

  saveEventBtn.addEventListener("click", () => {
    if (selectedDate) {
      const text = eventDetails.value.trim();
      if (text) {
        events[selectedDate] = text;
      } else {
        delete events[selectedDate];
      }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(events));
      closeModalWindow();
      renderCalendar();
    }
  });

  closeModal.addEventListener("click", closeModalWindow);
  window.addEventListener("click", e => {
    if (e.target === modal) closeModalWindow();
  });

  prevMonthBtn.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  });

  nextMonthBtn.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  });

  renderCalendar();
});
