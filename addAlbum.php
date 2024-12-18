<?php 
    session_start();
    if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
        echo 'Unauthorized access';
        exit;
    }
    ob_start();
    include 'navbar.php';
    ['connect_db' => $connect_db] = require('./src/db/db_connect.php');
    require 'vendor/autoload.php';
    $pdo = $connect_db(); 

    $error_message = '';
    
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $album_title = htmlspecialchars($_POST['album-title']);
        $singer = htmlspecialchars($_POST['singer']);
        $genre = htmlspecialchars($_POST['genre']);
        $release_date = htmlspecialchars($_POST['release-date']);
        
        $target_cover_dir = "./public/music/visual/album/";
        $cover_file = $target_cover_dir . basename($_FILES["cover-upload"]["name"]);

        if (!is_dir($target_cover_dir)){
            mkdir($target_cover_dir, 0777, true);
        }

        $file_mime_type = mime_content_type($_FILES["cover-upload"]["tmp_name"]);
        if (in_array($file_mime_type, $allowed_mime_types)){

            if (move_uploaded_file($_FILES["cover-upload"]["tmp_name"], $cover_file)) {
                $stmt = $pdo->prepare('INSERT INTO album (penyanyi, total_duration, judul, image_path, tanggal_terbit, genre)
                                        VALUES (:penyanyi, :total_duration, :judul, :image_path, :tanggal_terbit, :genre)'
                                     );
                if ($stmt->execute([
                    ':penyanyi' => $singer, 
                    ':total_duration' => 0,
                    ':judul' => $album_title,
                    ':image_path' => $cover_file,
                    ':tanggal_terbit' => $release_date, 
                    ':genre' => $genre
                ])) {
                    header('Location: album.php');
                    exit;
                } else { 
                    $error_message = "Error adding album to the database";
                }
            } else {
                $error_message = "Error uploading files.";
            }
        } else {
            $error_message = "Invalid file type. Only images are allowed.";
        }

    }
    ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Album</title>
    <link rel="stylesheet" href="public/css/addAlbum.css" type="text/css">
    <script src="./src/scripts/dateUtil.js"></script>
</head>

<body>
    <div class="add-album-container">
        <div class="add-album-form">
            <h2>Add Album</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="album-title">Album Title</label>
                    <input type="text" id="album-title" name="album-title" required>
                </div>
                <div class="input-group">
                    <label for="singer">Singer</label>
                    <input type="text" id="singer" name="singer" required>
                </div>
                <div class="input-group">
                    <label for="genre">Genre</label>
                    <input type="genre" id="genre" name="genre" required>
                </div>
                <div class="input-group">
                    <label for="release-date">Release Date</label>
                    <input type="date" id="release-date" name="release-date" required>
                </div>
                <div class="input-group">
                    <label for="cover-upload">Upload Cover</label>
                    <input type="file" id="cover-upload" name="cover-upload" accept="image/*" required> 
                </div>
                <div>
                    <button type="submit">Add Album</button>
                </div>
            </form>
        </div>
    </div>
    <?php if (!empty($error_message)): ?>
        <script>
            alert("<?php echo $error_message; ?>");
        </script>
    <?php endif; ?>
</body>




</html>