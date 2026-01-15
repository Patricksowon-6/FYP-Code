<?php
    if (session_status() === PHP_SESSION_NONE){
        session_start();
    }

    
    // BASE_PATH → server-side filesystem path
    define('BASE_PATH', __DIR__ . '/');

    // BASE_URL → browser links (CSS, JS, images)
    define('BASE_URL', '/FYP/');

    // Asset URLs
    define('CSS_PATH', BASE_URL . 'static/css/');
    define('JS_PATH', BASE_URL . 'static/js/');

    define('VIDEO_PATH', BASE_URL . 'media/videos/');
    define('IMG_PATH', BASE_URL . 'media/images/');
    define('AUDIO_PATH', BASE_URL . 'media/audio/');


    // PHP includes paths (server-side)
    define('INCLUDES_PATH', BASE_PATH . 'includes/');
    define('HANDLER_PATH', BASE_PATH . 'handlers/');

    // Pages URL for links in HTML
    define('PAGES_URL', BASE_URL . 'pages/');

    // ⚙️ Replace these with your own project details:
    define('SUPABASE_URL', 'https://oqvgaisaqwkgxtqgqesn.supabase.co');
    define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9xdmdhaXNhcXdrZ3h0cWdxZXNuIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MDkxMzg4MCwiZXhwIjoyMDc2NDg5ODgwfQ.xHPV_CYBGCJACa58w_bpzEpcCg7z6KLe7vEQoZ1Ovr0');
    




    // Database Connection
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) 
    {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->query("CREATE DATABASE IF NOT EXISTS FYP_DB");


	if (!$connect = mysqli_select_db($conn, 'FYP_DB'))
    {
        echo 'not done';
    }


$conn->query("
CREATE TABLE IF NOT EXISTS users (
    user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(40) NOT NULL,
    user_name VARCHAR(40) NOT NULL,
    email VARCHAR(30) UNIQUE NOT NULL,
    password TEXT NOT NULL,
    date_of_reg TIMESTAMP DEFAULT NOW() NOT NULL
)");





$conn->query("
CREATE TABLE IF NOT EXISTS projects (
    project_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

$conn->query("
CREATE TABLE IF NOT EXISTS project_banner (
    banner_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    show_title VARCHAR(255),
    description TEXT,
    emoji1 VARCHAR(10), genre1 VARCHAR(100),
    emoji2 VARCHAR(10), genre2 VARCHAR(100),
    emoji3 VARCHAR(10), genre3 VARCHAR(100),
    emoji4 VARCHAR(10), genre4 VARCHAR(100),
    emoji5 VARCHAR(10), genre5 VARCHAR(100),
    banner_img VARCHAR(255),
    quote_img VARCHAR(255),
    profile_img VARCHAR(255),
    circle_img_1 VARCHAR(255),
    circle_img_2 VARCHAR(255),
    circle_img_3 VARCHAR(255),
    circle_img_4 VARCHAR(255),
    circle_img_5 VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW() NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
)");

$conn->query("
CREATE TABLE IF NOT EXISTS project_users (
    project_user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('Owner', 'Co-Owner', 'Editor', 'Viewer') DEFAULT 'Viewer',
    user_position TEXT NOT NULL,
    
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);");





$conn->query("
CREATE TABLE IF NOT EXISTS tasks (
    task_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    status ENUM('todo','inprogress','forreview','done') DEFAULT 'todo',
    deadline DATE NOT NULL,  
    created_at TIMESTAMP DEFAULT NOW() NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);");





$conn->query("
CREATE TABLE IF NOT EXISTS user_files (
    file_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    category VARCHAR(50) NOT NULL,
    path TEXT NOT NULL,
    original_name TEXT NOT NULL,
    file_approval ENUM('approved', 'pending') NOT NULL DEFAULT 'approved',
    uploaded_at TIMESTAMP DEFAULT NOW(),

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);");

$conn->query("
CREATE TABLE IF NOT EXISTS file_versions (
    version_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    path TEXT NOT NULL,
    original_name TEXT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT NOW(),

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES user_files(file_id) ON DELETE CASCADE
)");




$conn->query("
CREATE TABLE IF NOT EXISTS main_card (
    main_card_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    card_name VARCHAR(50) NOT NULL,
    card_purpose VARCHAR(50) NOT NULL,
    card_type VARCHAR(25) NOT NULL,
    path TEXT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
)");

$conn->query("
CREATE TABLE IF NOT EXISTS sub_card (
    sub_card_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    main_card_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    path TEXT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (main_card_id) REFERENCES main_card(main_card_id) ON DELETE CASCADE
)");




$conn->query("
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    file_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (file_id) REFERENCES user_files(file_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);");

// $conn->query("
// CREATE TABLE IF NOT EXISTS comment_replies (
//     reply_id INT AUTO_INCREMENT PRIMARY KEY,
//     comment_id BIGINT UNSIGNED NOT NULL,
//     user_id BIGINT UNSIGNED NOT NULL,
//     comment_text TEXT NOT NULL,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
//     FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
// );");




// 
$conn->query("
CREATE TABLE IF NOT EXISTS shoot_dates (
    shoot_date_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    scene_name VARCHAR(255) NOT NULL,
    shoot_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);");

// 
$conn->query("
CREATE TABLE IF NOT EXISTS shoot_date_assets (
    asset_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shoot_date_id BIGINT UNSIGNED NOT NULL,
    file_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ready', 'not_ready') DEFAULT 'not_ready',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (shoot_date_id) REFERENCES shoot_dates(shoot_date_id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES user_files(file_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);");


?>