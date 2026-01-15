<?php
    require_once(__DIR__ . '/../config.php');

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $main_card_id = $_GET['main_card_id'] ?? null;
    $image_url = $_GET['image'];

    if (!$main_card_id) {
        echo "<p style='color:red; text-align:center;'>Main card not specified.</p>";
        exit;
    }
?>
    <div class="content" style="margin-top: 100px;">
        <!-- Main Card Image -->
        <div class="big_image">
            <img id="big_image" src="<?= htmlspecialchars($image_url) ?>" alt="Main Card Image">
        </div>

        <!-- Subcards -->
        <div class="cards">
            <div class="card add-card">
                <button id="addCardBtn">➕ Add Subcard</button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add Subcard</h2>

            <!-- IMPORTANT: enctype for file uploads -->
            <form id="detailsForm" enctype="multipart/form-data">
                <!-- hidden field not strictly required since JS appends sub_card_id, but safe to have -->
                <input type="hidden" id="subCardId" name="sub_card_id" value="">

                <label>Title:<br>
                    <input type="text" id="cardTitle" name="title" required>
                </label><br><br>

                <label>Description:<br>
                    <textarea id="cardDescription" name="description" rows="8" required></textarea>
                </label><br><br>

                <label>Image:<br>
                    <input type="file" id="cardImg" name="image" accept="image/*">
                </label><br><br>

                <button type="submit" id="saveBtn">Save</button>
            </form>
        </div>
    </div>

    <script>
        const MAIN_CARD_ID = <?= json_encode($main_card_id) ?>;
        const HANDLER_PATH = "../handlers/sub_card_handler.php";
    </script>