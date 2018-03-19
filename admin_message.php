<?php require_once './includes/secure_conn.php';
session_start();
//Check for admin user in session, else boot back to index
$email_admin = false;
$pass_admin = false;
if (isset($_SESSION['username']) && (isset($_SESSION['admin']))){
    if (strcmp($_SESSION['username'], $_SESSION['admin']) == 0){
        $email_admin = true;
        $pass_admin = true;
    }
}

//Check for success/fail:
$success = false;
if (isset($_SESSION['admin_message'])){
    $admin_message = $_SESSION['admin_message'];
    if (strcmp($admin_message, 'success') == 0){
        $success = true;
    }
    else {
        $success = false;
    }
}

//Check for select result or rowcount (insert/update/delete):
$select = false;
if (isset($_SESSION['select_user']) || isset($_SESSION['select_admin'])){
    $select = true; //flag for display format later!
}

//Check for view site images result:
$view = false;
if (isset($_SESSION['view_site'])){
    if ($_SESSION['view_site'] == 1){
        $view = true;
    }
}

require './includes/header.php';

//If not admin:
if (!$email_admin){
    include './includes/footer.php';
    //Redirect to home
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
?>
    <br>
    <section>
        <h2><b>   Admin Menu...</b></h2>
    </section>
        <fieldset class="adminfield">
            <legend>Output Message</legend>
            <!-- Output success/fail message-->
            <h4>Output was a <?php if ($success){echo 'success!';} else {echo 'failure.';}?></h4>
            <!-- Show results (rowcount affected, or items if select) -->
            <?php
            //Select results...
            if ($select){
                //Show select results in table:
                echo '<table>';
                if (isset($_SESSION['select_user'])) {
                    $select_user = $_SESSION['select_user'];
                    echo '<tr>';
                    echo '<th>UserID</th>';
                    echo '<th>username</th>';
                    echo '<th>firstname</th>';
                    echo '<th>lastname</th>';
                    echo '<th>email</th>';
                    echo '<th>password (hash)</th>';
                    echo '<th>phone_number</th>';
                    echo '</tr>';

                    foreach ($select_user as $srow){
                        //Echo results
                        echo '<tr>';
                        echo '<td>' . $srow['UserID'] . '</td>';
                        echo '<td>' . $srow['username'] . '</td>';
                        echo '<td>' . $srow['firstname'] . '</td>';
                        echo '<td>' . $srow['lastname'] . '</td>';
                        echo '<td>' . $srow['email'] . '</td>';
                        echo '<td>' . $srow['password'] . '</td>';
                        echo '<td>' . $srow['phone_number'] . '</td>';
                        echo '</tr>';
                    }
                }
                elseif (isset($_SESSION['select_admin'])) {
                    $select_user = $_SESSION['select_admin'];
                    echo '<tr>';
                    echo '<th>UserID</th>';
                    echo '<th>username</th>';
                    echo '<th>password (hash)</th>';
                    echo '</tr>';

                    foreach ($select_user as $srow){
                        //Echo results
                        echo '<tr>';
                        echo '<td>' . $srow['UserID'] . '</td>';
                        echo '<td>' . $srow['username'] . '</td>';
                        echo '<td>' . $srow['password'] . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
                unset($_SESSION['select_user']); //manually clear!
                unset($_SESSION['select_admin']); //manually clear!
            } //end if select
            //Show # of rows affected/returned:
            else {
                echo '<h4>';
                if (isset($_SESSION['insert_user'])){
                    echo $_SESSION['insert_user'];
                    echo ' user(s) were inserted.';
                    unset($_SESSION['insert_user']);//manually clear!
                }
                elseif (isset($_SESSION['update_user'])){
                    echo $_SESSION['update_user'];
                    echo ' user(s) were updated.';
                    unset($_SESSION['update_user']);//manually clear!
                }
                elseif (isset($_SESSION['delete_user'])){
                    echo $_SESSION['delete_user'];
                    echo ' user(s) were deleted.';
                    unset($_SESSION['delete_user']);//manually clear!
                }
                elseif (isset($_SESSION['insert_admin'])){
                    echo $_SESSION['insert_admin'];
                    echo ' admin(s) were inserted.';
                    unset($_SESSION['insert_admin']);//manually clear!
                }
                elseif (isset($_SESSION['update_admin'])){
                    echo $_SESSION['update_admin'];
                    echo ' admin(s) were updated.';
                    unset($_SESSION['update_admin']);//manually clear!
                }
                elseif (isset($_SESSION['delete_admin'])){
                    echo $_SESSION['delete_admin'];
                    echo ' admin(s) were deleted.';
                    unset($_SESSION['delete_admin']);//manually clear!
                }
                echo '</h4>';
            }
            ?>
        </fieldset>
        <?php
        //View site images...
        if ($view){
            $dirPath = "images/"; //site files loc.
            //Get items in path
            $diriterator = new DirectoryIterator($dirPath);
            echo '<div style="overflow-x: scroll; overflow-y: hidden; white-space: nowrap; width: 100%" >';
            foreach ($diriterator as $item) {
                //Display each item as img
                if ($item->isFile() && !$item->isDot()){
                    $pathname = $item->getPathname();
                    echo '<img src="' . $pathname . '" />   ';
                }
            } //end file loop
            echo '</div>';
            unset($_SESSION['view_site']); //manually clear!
        }
        ?>
    <br><br><br>
<?php include './includes/footer.php'; ?>