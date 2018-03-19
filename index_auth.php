<?php require_once './includes/secure_conn.php';
//Check if session has user, else quit to index.php
session_start();
if (!isset($_SESSION['username'])) {
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
require './includes/header.php'; ?>
    <!-- Maxwell Crawford -->
    <h5> Welcome back to SpeedShare! </h5>
    <br>
    <section>
        <h3>&#8226; Use 'Upload' to submit new images or <br>
            &#8226; Use 'Storage' to view current images and URLs.</h3>
    </section>
<?php include './includes/footer.php'; ?>