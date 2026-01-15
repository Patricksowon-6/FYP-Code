document.getElementById("shareForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const status = document.getElementById("shareStatus");
    status.textContent = "Sharing...";

    const formData = new FormData(this);

    const response = await fetch("../handlers/share_project.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.success) {
        status.style.color = "green";
        status.textContent = result.message;
        this.reset();
    } else {
        status.style.color = "red";
        status.textContent = result.message;
    }
});
