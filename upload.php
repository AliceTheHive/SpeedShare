<?php require_once './includes/secure_conn.php';
//Check if session has user, else quit to index.php
session_start();
require_once ('../pdo_config.php'); // Connect to the db.

//Verify that user (NOT admin) is logged in:
if (!isset($_SESSION['username'])) {
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
if (isset($_SESSION['admin'])) {
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
else {
    $username = $_SESSION['username'];
    $dirPath = "../SS_uploads/".$username."/";
}
require './includes/header.php';
//Check submission of button
if (isset($_POST['uploadbtn'])) {
    //Perform file checks...
    if (isset($_FILES['imagefile'])){
        //Validate type: JPG/JPEG/GIF/PNG/BMP
        $allowed_types = array ('image/pjpeg', 'image/jpeg', 'image/JPG', 'image/X-PNG', 'image/PNG', 'image/png', 'image/x-png', 'image/gif', 'image/GIF', 'image/bmp', 'image/x-windows-bmp');
        if (in_array($_FILES['imagefile']['type'], $allowed_types)){
            //Move file from TEMP to username-designated folder
            if (move_uploaded_file($_FILES['imagefile']['tmp_name'], "../SS_uploads/$username/{$_FILES['imagefile']['name']}")){
                //Get current file name and type
                $name = $_FILES['imagefile']['name'];
                $type = $_FILES['imagefile']['type'];

                //Iterate and re-set permissions
                $diriterator = new DirectoryIterator($dirPath);
                error_reporting(E_ALL ^ E_WARNING); //temp suppress warnings
                foreach ($diriterator as $item) {
                    chmod($item->getPathname(), 0777);
                    if ($item->isDir() && !$item->isDot()) {
                        chmod_r($item->getPathname());
                    }
                }
                error_reporting(E_ALL);

                //INSERT username, filename, filetype into SS_images
                try{
                    $sqlimg = "INSERT INTO SS_images (username, filename, filetype) VALUES (:username, :filename, :filetype)";
                    $stmt= $conn->prepare($sqlimg);
                    $stmt->bindValue(':username', $username);
                    $stmt->bindValue(':filename', $name);
                    $stmt->bindValue(':filetype', $type);
                    $success = $stmt->execute();
                }
                catch (PDOException $e) {
                    echo '<h3>We are unable to process your request at this time.</h3>';
                    include './includes/footer.php';
                    // Delete the TEMP file if it still exists:
                    if (file_exists ($_FILES['imagefile']['tmp_name']) && is_file($_FILES['imagefile']['tmp_name'])) {
                        unlink ($_FILES['imagefile']['tmp_name']);
                    }
                    exit();
                }

                //Upon success...
                $url = 'storage.php';
                echo '<main><br><br><br>';
                echo '<p style="text-align: center"><em>Redirecting to your Storage in a second...</em></p>';
                echo '<meta http-equiv="refresh" content="1;url='. $url .'">';
                echo '</main>';
                include './includes/footer.php';

                // Delete the TEMP file if it still exists:
                if (file_exists ($_FILES['imagefile']['tmp_name']) && is_file($_FILES['imagefile']['tmp_name'])) {
                    unlink ($_FILES['imagefile']['tmp_name']);
                }
                exit();
            } //end move check
        } //end allowed array check
    } //end FILES set check
} //end submission check
?>
    <!-- Maxwell Crawford -->
    <br>
    <section>
        <h3>&#8226;&nbsp;Upload - send new images to put into your personal storage!</h3>
    </section>
    <form enctype="multipart/form-data" method="post" action="upload.php">
        <!-- Used to hold max 12mb file size var -->
        <input type="hidden" name="MAX_FILE_SIZE" value="12582900">
        <fieldset>
            <legend>Choose a JPEG/GIF/PNG/BMP image of 12M or less to be uploaded:</legend>
            <p>
                <label for="imagefile">Upload image:</label>
                <input type="file" name="imagefile" id="imagefile">
            </p>
            <p>
                <input type="submit" name="uploadbtn" value="Upload">
            </p>
        </fieldset>
    </form>
<?php include './includes/footer.php'; ?>