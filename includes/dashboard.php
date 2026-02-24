<section class="hero">
    <div class="hero-content">
        <h2>Welcome back, <?php echo $_SESSION['user_name'] ?></h2>
        <p>Let's get straight back into the action:</p>

        <section class="cards-section">
            <!-- <div class="card">
                <h3>📊 Task Board</h3>
                <p>Complete tasks before deadlines come about.</p>
                <a href="<?= PAGES_URL ?>task_board.php">Go</a>
            </div> -->

            <div class="card">
                <h3>🗂️ Projects</h3>
                <p>Manage your ongoing projects to hit the completion mark.</p>
                <a href="<?= PAGES_URL ?>projects.php">Open</a>
            </div>

            <!-- <div class="card">
                <h3>📆 Calendar</h3>
                <p>Stay up-to-date on the most recent events.</p>
                <a href="<?= PAGES_URL ?>calendar.php">Check Up</a>
            </div> -->
        </section>
    </div>
</section>


