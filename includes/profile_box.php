<div class="profile-container">
    <div class="profile-header">
        <div class="profile-image">
            <img src="<?= IMG_PATH ?>Me.jpg" alt="Profile Picture">
        </div>
        <div class="profile-info">
            <h1 id="username"><?= htmlspecialchars($user['user_name']); ?></h1>
            <p id="email">Email: <?= htmlspecialchars($user['email']); ?></p>
        </div>
        <button id="editBtn">Edit Profile</button>
    </div>

    <div class="profile-bio">
        <h2>About Me</h2>
        <p id="bio">Lorem ipsum dolor sit amet consectetur adipisicing elit. Fugiat dolorem, debitis laborum possimus voluptates maiores iusto placeat, sed, ipsa perspiciatis facilis illo quo sequi nam optio nobis quas aut veritatis.</p>
    </div>

    <div class="profile-actions">
        <button id="saveBtn" class="hidden">Save</button>
        <button id="cancelBtn" class="hidden">Cancel</button>
    </div>
</div>