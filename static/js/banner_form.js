document.addEventListener("DOMContentLoaded", () => {
	const steps = document.querySelectorAll(".wizard-step");
	const dots = document.querySelectorAll(".wizard-progress .step");
	const nextBtns = document.querySelectorAll(".btn.primary");
	const backBtns = document.querySelectorAll(".btn.secondary");
	let currentStep = 0;

	function showStep(index) {
		steps.forEach(s => s.classList.remove("active"));
		steps[index].classList.add("active");
		dots.forEach((dot, i) => dot.classList.toggle("active", i <= index));

		if(index === steps.length-1) populateSummary();
	}

	nextBtns.forEach(btn => btn.addEventListener("click", () => {
		if(currentStep < steps.length-1) { 
			currentStep++; 
			showStep(currentStep); 
		}
	}));

	backBtns.forEach(btn => btn.addEventListener("click", () => {
		if(currentStep > 0) { 
		currentStep--; 
		showStep(currentStep); 
		}
	}));

// ==========================
// Image Preview Functions
// ==========================

function previewImage(input, previewContainer) {
	if(input.files && input.files[0]) {
	const reader = new FileReader();
	reader.onload = e => {
		let img = previewContainer.querySelector("img");
		if(!img) {
			img = document.createElement("img");
			img.style.maxWidth = "100%";
			img.style.borderRadius = "10px";
			previewContainer.appendChild(img);
		}
		img.src = e.target.result;
	}
	reader.readAsDataURL(input.files[0]);
	}
}

// Step 3: main images
const bannerInput = document.getElementById("banner_img");
const quoteInput = document.getElementById("quote_img");
const profileInput = document.getElementById("profile_img");

[bannerInput, quoteInput, profileInput].forEach(input => {
	const wrapper = document.createElement("div");
	wrapper.classList.add("preview-wrapper");
	input.parentNode.insertBefore(wrapper, input.nextSibling);
	wrapper.appendChild(input);
	input.addEventListener("change", () => previewImage(input, wrapper));
});

// Step 4: small circle images
const smallInputs = document.querySelectorAll(".small-img-input");
smallInputs.forEach(input => {
	const wrapper = document.createElement("div");
	wrapper.classList.add("small-preview-wrapper");
	input.parentNode.insertBefore(wrapper, input.nextSibling);
	wrapper.appendChild(input);
	input.addEventListener("change", () => {
	previewImage(input, wrapper);
	wrapper.querySelector("img").style.width = "100%";
	wrapper.querySelector("img").style.height = "100%";
	wrapper.querySelector("img").style.borderRadius = "50%";
	wrapper.querySelector("img").style.objectFit = "cover";
	});
});

// ==========================
// Populate Summary (Step 5)
// ==========================
function populateSummary() {
	// Text info
	document.getElementById("summary-show-title").textContent = document.getElementById("show_title").value;
	document.getElementById("summary-quote").textContent = document.getElementById("quote").value;
	document.getElementById("user-role").textContent = document.getElementById("user_type").value;

	// Themes
	const themesList = document.getElementById("summary-themes");
	themesList.innerHTML = "";
	document.querySelectorAll(".theme-pair").forEach(pair => {
	const emoji = pair.querySelector(".emoji-input").value;
	const genre = pair.querySelector(".genre-input").value;
	if(emoji || genre) {
		const li = document.createElement("li");
		li.textContent = `${emoji} — ${genre}`;
		themesList.appendChild(li);
	}
	});

	// Main images
	const bannerFile = bannerInput.files[0];
	const quoteFile = quoteInput.files[0];
	const profileFile = profileInput.files[0];
	document.getElementById("summary-banner-img").textContent = bannerFile ? bannerFile.name : "None";
	document.getElementById("summary-quote-img").textContent = quoteFile ? quoteFile.name : "None";
	document.getElementById("summary-profile-img").textContent = profileFile ? profileFile.name : "None";

	// Small circle images
	const smallList = document.getElementById("summary-small-images");
	smallList.innerHTML = "";
	smallInputs.forEach(input => {
	const file = input.files[0];
	const li = document.createElement("li");
	li.textContent = file ? file.name : "None";
	smallList.appendChild(li);
	});
}

showStep(currentStep);
});
