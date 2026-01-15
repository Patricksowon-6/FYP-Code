<?php

    function sign_in($conn)
    {
        // Only run on POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["user_name"]) && isset($_POST["password"])) {

            // Safely get inputs
            $username = $_POST['user_name'];
            $password = $_POST['password'];

            if ($username && $password) {
                // Use prepared statement to avoid SQL injection
                $stmt = $conn->prepare("SELECT user_id, user_name, password FROM Users WHERE user_name = ? LIMIT 1");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $user_data = $result->fetch_assoc();

                    // Verify hashed password
                    if (password_verify($password, $user_data['password'])) {
                        $_SESSION['user_id'] = $user_data['user_id'];
                        $_SESSION['user_name'] = $user_data['user_name'];

                        header("Location: welcome.php");
                        exit;
                    } else {
                        echo "<script>alert('Incorrect password. Please try again.');</script>";
                    }
                } else {
                    echo "<script>alert('No user found with that username.');</script>";
                }
            }
        }
    }

    function gather_banner_details($conn) {
        // 1️⃣ Get current user info
        $username = $_SESSION['user_name'] ?? null;
        if (!$username) {
            die("User not logged in.");
        }

        $sql = "SELECT user_id FROM Users WHERE user_name = ? LIMIT 1";
        $stmt_user = $conn->prepare($sql);
        $stmt_user->bind_param("s", $username);
        $stmt_user->execute();
        $result = $stmt_user->get_result();

        if (!$result || $result->num_rows === 0) {
            die("User not found.");
        }

        $user_data = $result->fetch_assoc();
        $user_id = $user_data['user_id'];

        // 2️⃣ Helper function: Upload to Supabase
        function upload_to_supabase($file, $user_id, $folder = 'banner') {
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

            // Sanitize filename and prepare path
            $filename = preg_replace('/\s+/', '_', basename($file['name']));
            $path = "user_{$user_id}/{$folder}/" . $filename;

            // Supabase upload endpoint
            $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . SUPABASE_BUCKET . "/" . $path . "?upsert=true";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => "PUT",
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
                    "apikey: " . SUPABASE_SERVICE_KEY,
                    "Content-Type: " . mime_content_type($file['tmp_name'])
                ],
                CURLOPT_POSTFIELDS => file_get_contents($file['tmp_name'])
            ]);

            $response  = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code >= 200 && $http_code < 300) {
                return $path; // we return "user_X/banner/filename"
            }

            return null;
        }


        // 3️⃣ Gather form text inputs
        $show_title = $_POST['show_title'] ?? '';
        $quote      = $_POST['quote'] ?? '';

        $emojis = [];
        $genres = [];
        for ($i = 1; $i <= 5; $i++) {
            $emojis[$i] = $_POST["emoji$i"] ?? '';
            $genres[$i] = $_POST["genre$i"] ?? '';
        }

        // Upload main banner images
        $banner_img  = upload_to_supabase($_FILES['banner_img'] ?? null, $user_id, 'banner');
        $quote_img   = upload_to_supabase($_FILES['quote_img']  ?? null, $user_id, 'banner');
        $profile_img = upload_to_supabase($_FILES['profile_img']?? null, $user_id, 'banner');

        // Upload circle images into subfolder
        $circle_imgs = [];
        for ($i = 1; $i <= 5; $i++) {
            $circle_imgs[$i] = upload_to_supabase($_FILES["circle_img_$i"] ?? null, $user_id, 'banner/circles');
        }

        // 5️⃣ Insert into database safely
        $stmt = $conn->prepare("
            INSERT INTO banners (
                user_id, show_title, quote,
                emoji1, genre1, emoji2, genre2, emoji3, genre3, emoji4, genre4, emoji5, genre5,
                banner_img, quote_img, profile_img,
                circle_img_1, circle_img_2, circle_img_3, circle_img_4, circle_img_5
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        if (!$stmt) {
            die("MySQL Prepare failed: " . $conn->error);
        }

        // bind_param: 1 integer + 20 strings = 21 total
        $stmt->bind_param(
            "issssssssssssssssssss",
            $user_id,
            $show_title,
            $quote,
            $emojis[1], $genres[1],
            $emojis[2], $genres[2],
            $emojis[3], $genres[3],
            $emojis[4], $genres[4],
            $emojis[5], $genres[5],
            $banner_img, $quote_img, $profile_img,
            $circle_imgs[1], $circle_imgs[2], $circle_imgs[3], $circle_imgs[4], $circle_imgs[5]
        );

        if (!$stmt->execute()) {
            die("MySQL Execute failed: " . $stmt->error);
        }

        // 6️⃣ Optional: store data in session
        $_SESSION['show_title']  = $show_title;
        $_SESSION['quote']       = $quote;
        for ($i = 1; $i <= 5; $i++) {
            $_SESSION["emoji$i"]       = $emojis[$i];
            $_SESSION["genre$i"]       = $genres[$i];
            $_SESSION["circle_img_$i"] = $circle_imgs[$i];
        }
        $_SESSION['banner_img']  = $banner_img;
        $_SESSION['quote_img']   = $quote_img;
        $_SESSION['profile_img'] = $profile_img;

        // 7️⃣ Redirect after success
        header("Location: welcome.php");
        exit;
    }

?>