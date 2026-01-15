document.addEventListener("DOMContentLoaded", () => {
  const loader = document.getElementById("upload-loader");

  // Show spinner
  const showLoader = () => {
    if (loader) loader.style.display = "flex";
  };

  // Hide spinner
  const hideLoader = () => {
    if (loader) loader.style.display = "none";
  };

  // Expose functions globally so file_upload.js can call them
  window.showLoader = showLoader;
  window.hideLoader = hideLoader;
});


