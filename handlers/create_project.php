<?php
require_once(__DIR__ . '/../config.php');

    function gather_details($conn){

        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) die("User not logged in.");

        // 2️⃣ Create new project
        $project_title = $_POST['show_title'] ?? 'New Project';
        $stmt = $conn->prepare("INSERT INTO projects (user_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) die("Project creation failed: " . $stmt->error);

        // Get the newly created project_id
        $project_id = $conn->insert_id;

        // 3️⃣ Create Supabase bucket for this project
        $bucket_name = "project_" . $project_id;
        create_supabase_bucket($bucket_name); // helper defined below

        // 4️⃣ Gather banner form inputs
        $show_title = $_POST['show_title'] ?? '';
        $quote      = $_POST['quote'] ?? '';
        $user_type = $_POST['user_type'] ?? '';

        $emojis = $genres = [];
        for ($i = 1; $i <= 5; $i++) {
            $emojis[$i] = $_POST["emoji$i"] ?? '';
            $genres[$i] = $_POST["genre$i"] ?? '';
        }

        // 5️⃣ Upload banner images
        $banner_img  = upload_to_supabase($_FILES['banner_img'] ?? null, $project_id, 'banner', $bucket_name);
        $quote_img   = upload_to_supabase($_FILES['quote_img']  ?? null, $project_id, 'banner', $bucket_name);
        $profile_img = upload_to_supabase($_FILES['profile_img']?? null, $project_id, 'banner', $bucket_name);

        // Upload circle images
        $circle_imgs = [];
        for ($i = 1; $i <= 5; $i++) {
            $circle_imgs[$i] = upload_to_supabase($_FILES["circle_img_$i"] ?? null, $project_id, 'banner/circles', $bucket_name);
        }

        // 6️⃣ Insert banner into database
        $stmt = $conn->prepare("
            INSERT INTO project_banner (
                project_id, show_title, description,
                emoji1, genre1, emoji2, genre2, emoji3, genre3, emoji4, genre4, emoji5, genre5,
                banner_img, quote_img, profile_img,
                circle_img_1, circle_img_2, circle_img_3, circle_img_4, circle_img_5
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "issssssssssssssssssss",
            $project_id,
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

        if (!$stmt->execute()) die("Banner insert failed: " . $stmt->error);

        $_SESSION['project_id']   = $project_id;
        $_SESSION['show_title']   = $show_title;
        $_SESSION['quote']        = $quote;
        $_SESSION['user_type']        = $user_type;
        $_SESSION['banner_img']   = $banner_img;
        $_SESSION['quote_img']    = $quote_img;
        $_SESSION['profile_img']  = $profile_img;
        for ($i = 1; $i <= 5; $i++) {
            $_SESSION["emoji$i"]       = $emojis[$i];
            $_SESSION["genre$i"]       = $genres[$i];
            $_SESSION["circle_img_$i"] = $circle_imgs[$i];
        }

        // After creating a project
        $insert = $conn->prepare("
            INSERT INTO project_users (project_id, user_id, role, user_position)
            VALUES (?, ?, 'Owner', ?)
        ");

        $insert->execute([$project_id, $user_id, $user_type]);

        // 7️⃣ Redirect to project page
        header("Location: projects.php");
        exit;
    }



    
    function create_supabase_bucket($bucket_name) {
        $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/bucket";
        $payload = json_encode(["name" => $bucket_name, "public" => true]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
                "apikey: " . SUPABASE_SERVICE_KEY,
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => $payload
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code === 409 || ($http_code >= 200 && $http_code < 300));
    }





    function upload_to_supabase($file, $project_id, $folder = 'banner', $bucket_name = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
        if (!$bucket_name) die("No bucket specified for upload.");

        $filename = preg_replace('/\s+/', '_', basename($file['name']));
        $path = "project_{$project_id}/{$folder}/" . $filename;

        $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . $bucket_name . "/" . $path . "?upsert=true";

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

        return ($http_code >= 200 && $http_code < 300) ? $path : null;
    }
?>
