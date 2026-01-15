<?php
require_once(__DIR__ . '/../config.php');
require_once(HANDLER_PATH . 'create_project.php');
require_once(HANDLER_PATH . 'sign_in_up.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gather_details'])) {
    gather_details($conn);
    exit;
}

?>

<form method="POST" class="banner-wizard" enctype="multipart/form-data">

    <!-- Progress bar -->
    <div class="wizard-progress">
        <div class="step active">1</div>
        <div class="bar"></div>
        <div class="step">2</div>
        <div class="bar"></div>
        <div class="step">3</div>
        <div class="bar"></div>
        <div class="step">4</div>
        <div class="bar"></div>
        <div class="step">5</div>
    </div>

    <!-- Step 1: Text Info -->
    <div class="wizard-step active" id="step1">
        <h2>Step 1 — Text Setup</h2>
        <p>Enter the main information that appears on your banner.</p>

        <div class="form-grid">
            <div class="field">
                <label for="show_title">Show Title</label>
                <input type="text" id="show_title" placeholder="Enter show title" name="show_title">
            </div>
        </div>

        <div class="field full">
            <label for="quote">Project Description</label>
            <textarea id="quote" placeholder="Give a brief description of your work." name="quote"></textarea>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="user_type">Your Role</label>
                <input type="text" id="user_type" placeholder="What's your role in this project?" name="user_type">
            </div>
        </div>

        <div class="wizard-buttons">
            <button type="button" onclick="location.href='<?= PAGES_URL ?>logged_in.php'" class="btn secondary">← Back</button>
            <button type="button" class="btn primary">Next →</button>
        </div>
    </div>

    <!-- Step 2: Themes/Genres -->
    <div class="wizard-step" id="step2">
        <h2>Step 2 — Themes & Genres</h2>
        <p class="subtitle">Add up to five emoji and theme/genre combinations that capture your show's vibe.</p>

        <div class="themes-container">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="theme-pair">
                    <input type="text" class="emoji-input" maxlength="2" placeholder="🔥" name="emoji<?= $i ?>">
                    <input type="text" class="genre-input" placeholder="Action Drama" name="genre<?= $i ?>">
                </div>
            <?php endfor; ?>
        </div>

        <div class="wizard-buttons">
            <button type="button" class="btn secondary">← Back</button>
            <button type="button" class="btn primary">Next →</button>
        </div>
    </div>

    <!-- Step 3: Images -->
    <div class="wizard-step" id="step3">
        <h2>Step 3 — Main Images</h2>
        <p>Upload your main banner, quote, and profile picture.</p>

        <div class="form-grid">
            <div class="field">
                <label>Banner Image</label>
                <input type="file" id="banner_img" accept="image/*" name="banner_img">
            </div>
            <div class="field">
                <label>Description Image</label>
                <input type="file" id="quote_img" accept="image/*" name="quote_img">
            </div>
            <div class="field">
                <label>Profile Image</label>
                <input type="file" id="profile_img" accept="image/*" name="profile_img">
            </div>
        </div>

        <div class="wizard-buttons">
            <button type="button" class="btn secondary">← Back</button>
            <button type="button" class="btn primary">Next →</button>
        </div>
    </div>

    <!-- Step 4: Small Circle Images -->
    <div class="wizard-step" id="step4">
        <h2>Step 4 — Extra Images</h2>
        <p>Upload up to 5 small circle images to display in the banner.</p>

        <div class="small-images-container">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="small-image">
                    <input type="file" class="small-img-input" accept="image/*" name="circle_img_<?= $i ?>">
                </div>
            <?php endfor; ?>
        </div>

        <div class="wizard-buttons">
            <button type="button" class="btn secondary">← Back</button>
            <button type="button" class="btn primary">Next →</button>
        </div>
    </div>

    <!-- Step 5: Summary -->
    <div class="wizard-step" id="step5">
        <h2>Step 5 — Summary</h2>
        <p>Review all information before finishing.</p>

        <div class="summary-box">
            <h3>Text Info</h3>
            <p><strong>Show Title:</strong> <span id="summary-show-title"></span></p>
            <p><strong>Description:</strong> <span id="summary-quote"></span></p>
            <p><strong>Role:</strong> <span id="user-role"></span></p>

            <h3>Themes & Genres</h3>
            <ul id="summary-themes"></ul>

            <h3>Main Images</h3>
            <ul>
                <li><strong>Banner:</strong> <span id="summary-banner-img"></span></li>
                <li><strong>Description Image:</strong> <span id="summary-quote-img"></span></li>
                <li><strong>Profile:</strong> <span id="summary-profile-img"></span></li>
            </ul>

            <h3>Small Circle Images</h3>
            <ul id="summary-small-images"></ul>

            <div class="wizard-buttons">
                <button type="button" class="btn secondary">← Back</button>
                <button type="submit" class="btn primary" name="gather_details">Finish</button>
            </div>
        </div>
    </div>
</form>

<script src="<?= JS_PATH ?>banner_form.js"></script>