<?php require_once './includes/secure_conn.php';
session_start();
require_once ('../pdo_config.php'); // Connect to the db.
//Check for admin user in session, else boot back to index
$email_admin = false;
$pass_admin = false;
if (isset($_SESSION['username']) && (isset($_SESSION['admin']))){
    if (strcmp($_SESSION['username'], $_SESSION['admin']) == 0){
        $email_admin = true;
        $pass_admin = true;
    }
}
require './includes/header.php';

//If not admin:
if ((!$email_admin) || (!$pass_admin)){
    include './includes/footer.php';
    //Redirect to home
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

//Check for selectbtn1 (users)
if (isset($_POST['selectbtn1'])){
    //Perform select actions!
    try{
        $selectsql1 = 'SELECT * FROM SS_users';
        $selectresult1 = $conn->query($selectsql1);
        $numRows = $selectresult1->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Store results in array and pass to session
    $select_user = $selectresult1->fetchAll();
    $_SESSION['select_user'] = $select_user;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

//Check for insertbtn1 (users)
// username, firstname, lastname, email, password, phone_number
if (isset($_POST['insertbtn1'])) {
    //Grab and store vars
    $current_username = filter_input(INPUT_POST, inserttext1);
    $current_first = filter_input(INPUT_POST, inserttext2);
    $current_last = filter_input(INPUT_POST, inserttext3);
    $current_email = filter_input(INPUT_POST, inserttext4);
    $current_pass = password_hash(filter_input(INPUT_POST, inserttext5), PASSWORD_DEFAULT);
    $current_phone = filter_input(INPUT_POST, inserttext6);

    //Perform insert actions!
    try{
        $insstmt = $conn->prepare("INSERT INTO SS_users(username, firstname, lastname, email, password, phone_number) 
            VALUES(:current_username, :current_first, :current_last, :current_email, :current_pass, :current_phone)");
        $insstmt->bindValue(':current_username', $current_username);
        $insstmt->bindValue(':current_first', $current_first);
        $insstmt->bindValue(':current_last', $current_last);
        $insstmt->bindValue(':current_email', $current_email);
        $insstmt->bindValue(':current_pass', $current_pass);
        $insstmt->bindValue(':current_phone', $current_phone);
        $insstmt->execute();
        $inscount = $insstmt->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Create folder for new user...
    $dirPath = "../SS_uploads/".$current_username."/";
    mkdir($dirPath);
    chmod($dirPath,0777);

    //Store affected rows and pass to session
    $_SESSION['insert_user'] = $inscount;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

//Check for uploadbtn (upload user images per username)
if (isset($_POST['uploadbtn'])) {
    //Get username:
    $username = filter_input(INPUT_POST, uploadtext1);
    $dirPath = "../SS_uploads/".$username."/";
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

                //Set success
                $_SESSION['admin_message'] = 'success';
                include './includes/footer.php';

                // Delete the TEMP file if it still exists:
                if (file_exists ($_FILES['imagefile2']['tmp_name']) && is_file($_FILES['imagefile2']['tmp_name'])) {
                    unlink ($_FILES['imagefile2']['tmp_name']);
                }

                //Redirect to admin_message
                $url = 'admin_message.php';
                echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
                exit();
            } //end move check
        } //end allowed array check
    } //end FILES set check
} //end submission check

//Check for updatebtn1 (users)
//NOTE: only use ONE field at a time...
// if username/firstname/lastname/email -> text -> updatetext1
// if password -> password -> updatetext1p
// if phone_number -> tel -> updatetext1t
if (isset($_POST['updatebtn1'])){
    //Grab field to edit...
    $current_field = $_POST['updatefield'];

    //Check if pass:
    if (strcmp($current_field, 'password') == 0){
        $current_value = password_hash($_POST['updatetext1p'], PASSWORD_DEFAULT);
    }
    elseif (strcmp($current_field, 'phone_number') == 0){
        $current_value = $_POST['updatetext1t'];
    }
    else{
        $current_value = filter_input(INPUT_POST, 'updatetext1');
    }

    //Grab UserID to edit...
    $current_UserID = filter_input(INPUT_POST, 'updatetext2');

    //Perform update actions!
    try{
        if (strcmp($current_field, 'username') == 0){
            $updatesql = $conn->prepare("UPDATE SS_users SET username = :current_value WHERE UserID = :current_UserID");
        }
        elseif (strcmp($current_field, 'firstname') == 0){
            $updatesql = $conn->prepare("UPDATE SS_users SET firstname = :current_value WHERE UserID = :current_UserID");
        }
        elseif (strcmp($current_field, 'lastname') == 0){
            $updatesql = $conn->prepare("UPDATE SS_users SET lastname = :current_value WHERE UserID = :current_UserID");
        }
        elseif (strcmp($current_field, 'email') == 0){
            $updatesql = $conn->prepare("UPDATE SS_users SET email = :current_value WHERE UserID = :current_UserID");
        }
        elseif (strcmp($current_field, 'password') == 0){
            $updatesql = $conn->prepare("UPDATE SS_users SET password = :current_value WHERE UserID = :current_UserID");
        }
        elseif (strcmp($current_field, 'phone_number') == 0){
            $updatesql = $conn->prepare("UPDATE SS_users SET phone_number = :current_value WHERE UserID = :current_UserID");
        }
        $updatesql->bindValue(':current_value', $current_value);
        $updatesql->bindValue(':current_UserID', $current_UserID);
        $updatesql->execute();
        $updatecount = $updatesql->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Store affected rows and pass to session
    $_SESSION['update_user'] = $updatecount;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

//Check for deletebtn1 (user)
if (isset($_POST['deletebtn1'])){
    //Grab UserID from field
    $current_UserID = filter_input(INPUT_POST, 'deletetext1', FILTER_SANITIZE_STRING);

    //Grab username from UserID in DB...
    $current_username = "";
    try{
        $searchsql = $conn->prepare("SELECT username FROM SS_users WHERE UserID = :current_UserID");
        $searchsql->bindValue(':current_UserID', $current_UserID);
        $searchsql->execute();
        $searchres = $searchsql->fetchAll();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }
    foreach($searchres as $item){
        $current_username = $item['UserID'];
    }
    //Perform delete actions!
    try{
        $deluserstmt = $conn->prepare("DELETE FROM SS_users WHERE UserID = :current_UserID");
        $deluserstmt->bindValue(':current_UserID', $current_UserID);
        $deluserstmt->execute();
        $delusercount = $deluserstmt->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Iterate thru user images directory and unlink then rmdir!
    $dirPath = "../SS_uploads/".$current_username."/";
    $diriterator = new DirectoryIterator($dirPath);
    error_reporting(E_ALL ^ E_WARNING); //temp suppress warnings
    foreach ($diriterator as $item) {
        $pathname = $item->getPathname();
        if ($item->isFile()) {
            unlink($pathname);
        }
    }
    rmdir($dirPath);
    error_reporting(E_ALL);

    //Store affected rows and pass to session
    $_SESSION['delete_user'] = $delusercount;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

// Check for selectbtn2 (admin)
if (isset($_POST['selectbtn2'])){
    //Perform select actions!
    try{
        $selectsql1 = 'SELECT * FROM SS_admins';
        $selectresult1 = $conn->query($selectsql1);
        $numRows = $selectresult1->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Store results in array and pass to session
    $select_admin = $selectresult1->fetchAll();
    $_SESSION['select_admin'] = $select_admin;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
// Check for insertbtn2 (admin)
// UserID (null), username, password
if (isset($_POST['insertbtn2'])) {
    //Grab and store vars
    $current_username = filter_input(INPUT_POST, ainserttext1);
    $current_pass = password_hash(filter_input(INPUT_POST, ainserttext5), PASSWORD_DEFAULT);

    //Perform insert actions!
    try{
        $insstmt = $conn->prepare("INSERT INTO SS_admins(username, password) 
            VALUES(:current_username, :current_pass)");
        $insstmt->bindValue(':current_username', $current_username);
        $insstmt->bindValue(':current_pass', $current_pass);
        $insstmt->execute();
        $inscount = $insstmt->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Store affected rows and pass to session
    $_SESSION['insert_admin'] = $inscount;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

// Check for updatebtn2 (admin)
// if username -> text -> aupdatetext1
if (isset($_POST['updatebtn2'])){
    //Grab field to edit...
    $current_field = $_POST['aupdatefield'];

    //Check if pass:
    if (strcmp($current_field, 'password') == 0){
        $current_value = password_hash($_POST['aupdatetext1p'], PASSWORD_DEFAULT);
    }
    else{
        $current_value = filter_input(INPUT_POST, 'aupdatetext1');
    }

    //Grab UserID to edit...
    $current_UserID = filter_input(INPUT_POST, 'aupdatetext2');

    //Perform update actions!
    try{
        if (strcmp($current_field, 'username') == 0){
            $updatesql = $conn->prepare("UPDATE SS_admins SET username = :current_value WHERE UserID = :current_UserID");
        }
        elseif (strcmp($current_field, 'password') == 0){
            $updatesql = $conn->prepare("UPDATE SS_admins SET password = :current_value WHERE UserID = :current_UserID");
        }
        $updatesql->bindValue(':current_value', $current_value);
        $updatesql->bindValue(':current_UserID', $current_UserID);
        $updatesql->execute();
        $updatecount = $updatesql->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Store affected rows and pass to session
    $_SESSION['update_admin'] = $updatecount;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

// Check for deletebtn2
if (isset($_POST['deletebtn2'])){
    //Grab UserID from field
    $current_UserID = filter_input(INPUT_POST, 'adeletetext1', FILTER_SANITIZE_STRING);
    //Perform delete actions!
    try{
        $deluserstmt = $conn->prepare("DELETE FROM SS_admins WHERE UserID = :current_UserID");
        $deluserstmt->bindValue(':current_UserID', $current_UserID);
        $deluserstmt->execute();
        $delusercount = $deluserstmt->rowCount();
    }
    catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
        //Failure: redirect
        unset($_SESSION['admin_message']); //manually unset
        include './includes/footer.php';
        $url = 'admin_message.php';
        echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
        exit();
    }

    //Store affected rows and pass to session
    $_SESSION['delete_admin'] = $delusercount;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

$dirPath = "images/"; //global var for site files
// Check for viewbtn (view site images)
if (isset($_POST['viewbtn'])){
    //Store session var
    $_SESSION['view_site'] = 1;

    //Set success
    $_SESSION['admin_message'] = 'success';

    include './includes/footer.php';
    //Redirect to admin_message
    $url = 'admin_message.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}

?>
    <br>
    <section>
        <h2><b>   Admin Menu...</b></h2>
    </section>
    <form enctype="multipart/form-data" class="adminform" method="post" action="admin_menu.php">
        <fieldset class="adminfield">
            <legend>USERS</legend>
            <!-- Select users -->
            <h3><em>Select all Users...</em></h3>
            <label for="selectbtn1">Select Users</label>
            &nbsp;<input type="submit" name="selectbtn1" id="selectbtn1" value="Select"><br>
            <!-- Insert user info... NOTE: UserID are left NULL-->
            <h3><em>Insert a new User...</em></h3>
            <label for="inserttext1">Username:</label>&nbsp;
            <input type="text" name="inserttext1" id="inserttext1">
            <label for="inserttext2">First Name:</label>&nbsp;
            <input type="text" name="inserttext2" id="inserttext2">
            <label for="inserttext3">Last Name:</label>&nbsp;
            <input type="text" name="inserttext3" id="inserttext3"><br><br>
            <label for="inserttext4">Email:</label>&nbsp;
            <input type="text" name="inserttext4" id="inserttext4">
            <label for="inserttext5">Password:</label>&nbsp;
            <input type="password" name="inserttext5" id="inserttext5"><br><br>
            <label for="inserttext6">Phone Number (xxx-xxx-xxxx)</label>&nbsp;
            <input id="inserttext6" name="inserttext6" type="tel" pattern="^\d{3}-\d{3}-\d{4}$" placeholder="___-___-____">
            &nbsp;<input type="submit" name="insertbtn1" value="Insert"><br>
            <!-- Insert user images (per username)-->
            <h3><em>Upload user images...</em></h3>
            <label for="uploadtext1">Username:</label>&nbsp;
            <input type="text" name="uploadtext1" id="uploadtext1">&nbsp;&nbsp;&nbsp;
            <input type="hidden" name="MAX_FILE_SIZE" value="12582900">
            <label for="imagefile">Upload image:</label>
            <input type="file" name="imagefile" id="imagefile">
            <input type="submit" name="uploadbtn" value="Upload">
            <!-- Update user info...-->
            <h3><em>Update an existing User...</em></h3>
            <label for="updatefield">Field to Edit:</label>&nbsp;
            <select name="updatefield" id="updatefield">
                <option value="username" selected>Username</option>
                <option value="firstname">First Name</option>
                <option value="lastname">Last Name</option>
                <option value="email">Email</option>
                <option value="password">Password</option>
                <option value="phone_number">Phone Number</option>
            </select><br><br>
            <label for="updatetext1">Set Field Equal To:</label>&nbsp;
            (Text)<input type="text" name="updatetext1" id="updatetext1">&nbsp;<label for="updatetext1p">(Password) </label>&nbsp;<input type="password" name="updatetext1p" id="updatetext1p">&nbsp;<label for="updatetext1t">(Phone) </label>&nbsp;<input type="tel" name="updatetext1t" id="updatetext1t" pattern="^\d{3}-\d{3}-\d{4}$" placeholder="___-___-____"><br><br>
            <label for="updatetext2">Where UserID is Equal To:</label>&nbsp;
            <input type="text" name="updatetext2" id="updatetext2">
            &nbsp;<input type="submit" name="updatebtn1" value="Update"><br>
            <!-- Delete user  -->
            <h3><em>Delete an existing User...</em></h3>
            <label for="deletetext1">Delete User Where UserID is Equal To:</label>&nbsp;
            <input type="text" name="deletetext1" id="deletetext1">
            &nbsp;<input type="submit" name="deletebtn1" value="Delete">
        </fieldset><br>
        <fieldset class="adminfield">
            <legend>ADMINS</legend>
            <!-- Select admins -->
            <h3><em>Select all Admins...</em></h3>
            <label for="selectbtn2">Select Admins</label>
            &nbsp;<input type="submit" name="selectbtn2" id="selectbtn2" value="Select"><br>
            <!-- Insert admin info... NOTE: UserID is left NULL-->
            <h3><em>Insert a new Admin...</em></h3>
            <label for="ainserttext1">Username:</label>&nbsp;
            <input type="text" name="ainserttext1" id="ainserttext1">
            <label for="ainserttext5">Password:</label>&nbsp;
            <input type="password" name="ainserttext5" id="ainserttext5"><br><br>
            &nbsp;<input type="submit" name="insertbtn2" value="Insert"><br>
            <!-- Update admin info...-->
            <h3><em>Update an existing Admin...</em></h3>
            <label for="aupdatefield">Field to Edit:</label>&nbsp;
            <select name="aupdatefield" id="aupdatefield">
                <option value="username" selected>Username</option>
                <option value="password">Password</option>
            </select><br><br>
            <label for="aupdatetext1">Set Field Equal To:</label>&nbsp;
            (Text)<input type="text" name="aupdatetext1" id="aupdatetext1">&nbsp;<label for="aupdatetext1p">(Password) </label>&nbsp;<input type="password" name="aupdatetext1p" id="aupdatetext1p"><br><br>
            <label for="aupdatetext2">Where UserID is Equal To:</label>&nbsp;
            <input type="text" name="aupdatetext2" id="aupdatetext2">
            &nbsp;<input type="submit" name="updatebtn2" value="Update"><br>
            <!-- Delete user  -->
            <h3><em>Delete an existing Admin...</em></h3>
            <label for="adeletetext1">Delete Admin Where UserID is Equal To:</label>&nbsp;
            <input type="text" name="adeletetext1" id="adeletetext1">
            &nbsp;<input type="submit" name="deletebtn2" value="Delete">
        </fieldset><br>
    </form><br>
    <form class="sitefileform" method="post" action="admin_menu.php">
        <fieldset class="adminfield">
            <legend>SITE FILES</legend>
            <!-- Show/select current images in "images" folder -->
            <h3><em>View all site images...</em></h3>
            <label for="viewbtn">View Images: </label>
            &nbsp;<input type="submit" name="viewbtn" id="viewbtn" value="View"><br>
        </fieldset><br>
    </form><br><br><br><br>
<?php include './includes/footer.php'; ?>