// Video Player
document.addEventListener('DOMContentLoaded', () => {
    const videoModal = document.getElementById("videoModal");
    const videoPlayer = document.getElementById("videoPlayer");
    const closeVideo = videoModal.querySelector(".close");

    document.querySelectorAll(".videoPreviewBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            const src = btn.dataset.video;
            if (!src) return;

            videoPlayer.src = src;
            videoModal.style.display = "flex";
            videoPlayer.play().catch(err => console.log(err));
        });
    });

    closeVideo.onclick = () => {
        videoModal.style.display = "none";
        videoPlayer.pause();
        videoPlayer.currentTime = 0;
    };

    window.addEventListener("click", e => {
        if (e.target === videoModal) {
            videoModal.style.display = "none";
            videoPlayer.pause();
            videoPlayer.currentTime = 0;
        }
    });
});
