<?php require_once './includes/secure_conn.php';
require_once ('../pdo_config.php'); // Connect to the db.
session_start();
require './includes/header.php';
    //Author: Maxwell Crawford
	$missing = array(); //for new users
	$errors = array();
	$missing_old = array(); //for old/returning users
	$errors_old = array();

	//Submission check for NEW users:
	if (isset($_POST['newuser'])) {
		//Names, need to trim
		if (empty(trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING)))) 
			$missing[] = 'firstname';
		else $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING));

		if (empty(trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING)))) 
			$missing[] = 'lastname';
		else $lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING));

		//Username, need to trim
		if (empty(trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING)))) 
			$missing[] = 'username';
		else $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));

		//Email, need to trim and check filter return value (if false)
		if (empty(trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))))
			$missing[] = 'email';
		if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) == false) 
			$errors[] = 'email';
		else $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));

		//Password
		if(empty(trim($_POST['password1'])))
			$missing[] = 'password';
		$password1 = trim($_POST['password1']);
		$password2 = trim($_POST['password2']);
		//if passwords don't match!!
		if (strcmp($password1, $password2) !== 0)
		{
			$errors[] = 'password';
		}
		else
		{
			$password = $password1;
		}

		//Phone number
		if(empty(trim($_POST['phonenum'])))
			$missing[] = 'phonenum';
		$phonenum = trim($_POST['phonenum']);

		//No errors/missing
		if ((empty($missing)) && (empty($errors)))
		{
			//HASH THE PASSWORD!
			$password = password_hash($password, PASSWORD_DEFAULT);

            //Check if email is taken/unique:
            $email_valid = true;
            try {
                //Select stmt to get user table
                $sql = "SELECT email FROM SS_users";
                foreach($conn->query($sql) as $row){
                    //NOTE: If email is found -> not unique/already taken!
                    if (strcmp($row['email'], $email) == 0){
                        $email_valid = false;
                    }
                }
            } catch (PDOException $e) {
                echo '<h3>We are unable to process your request at this time.</h3>';
                include './includes/footer.php';
                exit();
            }

            //If invalid, add to errors list
            if ($email_valid == false)
                $errors[] = 'email';

			//Insert data into DB if new user AND email isn't taken!
            if ($email_valid == true)
            {
                //Display confirmation form:
                ?><main>
                    <h3>Thank you for registering!</h3>
                    <h4>We have received the following information:</h4>
                    <p>Name: <?php echo "$firstname $lastname"; ?></p>
                    <p>Email: <?php echo $email; ?></p>
                    <p>Phone No: <?php echo $phonenum; ?></p><br>
                    <p style="text-align: center"><em>Page will be redirected in 2 seconds...</em></p>
                </main>
            <?php
                //Insert new registration data
              try {
                //Prepared stmt:
                   $stmt = $conn->prepare("INSERT INTO SS_users (username, firstname, 
                            lastname, email, password, phone_number)
                            VALUES (:username, :firstname, :lastname, :email, :password, :phonenum)");
                   $stmt->bindValue(':username', $username);
                   $stmt->bindValue(':firstname', $firstname);
                   $stmt->bindValue(':lastname', $lastname);
                   $stmt->bindValue(':email', $email);
                   $stmt->bindValue(':password', $password);
                   $stmt->bindValue(':phonenum', $phonenum);
                   $stmt->execute();
                } catch (PDOException $e) {
                  echo '<h3>We are unable to process your request at this time.</h3>';
                    include './includes/footer.php';
                    exit();
                } //end DB try

                //Create user directory in SS_uploads:
                $dirPath = "../SS_uploads/".$username."/";
                mkdir($dirPath);
                chmod($dirPath,0777);

                //All done
                $_SESSION['username'] = $username;
                include './includes/footer.php';
				//Redirect to upload //signed in home
                $url = 'upload.php';
                echo '<meta http-equiv="refresh" content="2;url='. $url .'">';
                exit();
            }
            
		} //end error/missing check
	} //end submission check (new user)

    //Submission check for OLD/returning users:
    if (isset($_POST['olduser'])) {
        //Username, need to trim
        if (empty(trim(filter_input(INPUT_POST, 'username2', FILTER_SANITIZE_STRING))))
            $missing_old[] = 'username2';
        else $username_old = trim(filter_input(INPUT_POST, 'username2', FILTER_SANITIZE_STRING));

        //Password
        if(empty(trim($_POST['password3'])))
            $missing_old[] = 'password3';
        else {
            $password_old = trim($_POST['password3']);
        }

        //No errors/missing
        if (empty($missing_old)) {
            //Set vars for validity flags:
            $username_valid = false;
            $password_valid = false;

            //Set vars for ADMIN flags:
            $admin_username_valid = false;
            $admin_password_valid = false;

            //Check for secret admin login FIRST!
            try {
                //Select stmt to get admin table
                $sql = "SELECT username, password FROM SS_admins";
                foreach($conn->query($sql) as $row){
                    //NOTE: Need to match username AND password on same row!!
                    if (strcmp($row['username'], $username_old) == 0){
                        $admin_username_valid = true;
                        if (password_verify($password_old, $row['password'])) {
                            $admin_password_valid = true;
                        }
                    }
                }
            } catch (PDOException $e) {
                echo '<h3>We are unable to process your request at this time.</h3>';
                include './includes/footer.php';
                exit();
            }

            //Check DB for user and password
            if (($admin_username_valid == false) || ($admin_password_valid == false)) {
                try {
                    //Select stmt to get user table
                    $sql = "SELECT username, password FROM SS_users";
                    foreach($conn->query($sql) as $row){
                        //NOTE: Need to match username AND password on same row!!
                        if (strcmp($row['username'], $username_old) == 0){
                            $username_valid = true;
                            if (password_verify($password_old, $row['password'])) {
                                $password_valid = true;
                            }
                        }
                    }
                } catch (PDOException $e) {
                    echo '<h3>We are unable to process your request at this time.</h3>';
                    include './includes/footer.php';
                    exit();
                }
            }

            //If invalid, add to errors list
            if ($username_valid == false)
                $errors_old[] = 'username2';
            if ($password_valid == false)
                $errors_old[] = 'password3';

            //Display confirmation form for signin IF SUCCESSFUL:
            // if not valid -> load orig form with error msgs...
            if (($admin_username_valid) && ($admin_password_valid)) {
                ?>
                <main>
                    <h4>You've unlocked the admin menu!</h4><br><br>
                    <p style="text-align: center"><em>Page will be redirected in 2 seconds...</em></p>
                </main>
                <?php
                //All done
                $_SESSION['username'] = $username_old;
                $current_hash = password_hash($password_old, PASSWORD_DEFAULT);
                $_SESSION['pass'] = $current_hash;
                $_SESSION['admin'] = $username_old;
                // ^ NOTE: will need to use password_verify('string', $_SESSION['pass']);
                include './includes/footer.php';
                //Redirect to ADMIN MENU
                $url = 'admin_menu.php';
                echo '<meta http-equiv="refresh" content="2;url='. $url .'">'; //$url defined in secure_conn.php
                exit();
            }
            if (($username_valid == true) && ($password_valid == true)) {
                ?>
                <main>
                <h3>Successfully signed in!</h3>
                <h4>You are signed in as:</h4>
                <p>Username: <?php echo "$username_old"; ?></p><br>
                <p style="text-align: center"><em>Page will be redirected in 2 seconds...</em></p>
                </main>
                <?php
                //All done
                $_SESSION['username'] = $username_old;
                include './includes/footer.php';
				//Redirect to upload //signed in home
                $url = 'upload.php';
                echo '<meta http-equiv="refresh" content="2;url='. $url .'">';
                exit();
            } //end login error check
        } //end error/missing check2
    } //end submission check (old user)
?>

<main>
    <br>
    <!-- ORIGINAL FORM 1: REGISTRATION/NEW USER-->
    <fieldset>
        <legend><h4>Please register or sign in to continue</h4></legend>
        <div class="formleft">
            <form method="POST" action="sign_in.php">
                <h4>Registration (New Users):</h4>
                <!-- First and last name-->
                <p>
                <label for="firstname">First Name:
                <?php if ($missing && in_array('firstname', $missing)) { ?>
                    <br><span class="warning"> Please enter your first name</span>
                <?php } ?></label><br>
                <input type="text" name="firstname" id="firstname"
                <?php if (isset($firstname)) {
                    echo 'value="' . htmlspecialchars($firstname) . '"';
                } ?>
                >
                <br>
                <label for="lastname">Last Name:
                <?php if ($missing && in_array('lastname', $missing)) { ?>
                    <br><span class="warning"> Please enter your last name</span>
                <?php } ?></label><br>
                <input type="text" name="lastname" id="lastname"
                <?php if (isset($lastname)) {
                    echo 'value="' . htmlspecialchars($lastname) . '"';
                } ?>
                >
                </p>
                <!-- Username -->
                <p>
                <label for="username">Your New Username:
                <?php if ($missing && in_array('username', $missing)) { ?>
                    <br><span class="warning"> Please enter your new username</span>
                <?php } ?></label><br>
                <input type="text" name="username" id="username"
                <?php if (isset($username)) {
                    echo 'value="' . htmlspecialchars($username) . '"';
                } ?>
                >
                </p>
                <!-- Email-->
                <p>
                <label for="email">Email:
                <?php if ($errors && in_array('email', $errors)) { ?>
                    <br><span class="warning"> Please enter a valid or unique email</span>
                <?php }
                elseif ($missing && in_array('email', $missing)) { ?>
                    <br><span class="warning"> Please enter an email</span>
                <?php } ?></label><br>
                <input type="text" name="email" id="email"
                <?php if (isset($email)) {
                    echo 'value="' . htmlspecialchars($email) . '"';
                } ?>
                >
                </p>
                <!-- Password -> twice, both boxes must match
                if they don't match, wipe both-->
                <p>
                <label for="password1">Password:
                <?php if ($missing && in_array('password', $missing)) { ?>
                    <br><span class="warning"> Please enter a password</span>
                <?php } ?>
                <?php if ($errors && in_array('password', $errors)) { ?>
                    <br><span class="warning"> Passwords don't match!</span>
                <?php } ?></label><br>
                <input type="password" name="password1" id="password1"
                <?php if (isset($password)) {
                    echo 'value="' . $password . '"';
                } ?>
                >
                <br>
                <label for="password2">Confirm Password: </label><br>
                <input type="password" name="password2" id="password2"
                <?php if (isset($password)) {
                    echo 'value="' . $password . '"';
                } ?>
                >
                </p>
                <!-- Phone no-->
                <p>
                <label for="phonenum">Phone Number (use format: xxx-xxx-xxxx):
                <?php if ($missing && in_array('phonenum', $missing)) { ?>
                    <br><span class="warning"> Please enter your phone number</span>
                <?php } ?></label><br/>
                <input id="phonenum" name="phonenum" type="tel" pattern="^\d{3}-\d{3}-\d{4}$" placeholder="___-___-____"
                <?php if (isset($phonenum)) {
                    echo 'value="' . $phonenum . '"';
                } ?>
                >
                </p>
                <!-- Form submission btn for REGISTER-->
                <br>
                <input type="submit" name="newuser" value="Register">
            </form>
        </div>
    <!-- ORIGINAL FORM 2: SIGN-IN/OLD USER-->
        <div class="formright">
            <form method="POST" action="sign_in.php">
                <h4>Sign In (Returning Users):</h4>
                <!-- Username -->
                <p>
                    <label for="username2">Username:
                        <?php if ($missing_old && in_array('username2', $missing_old)) { ?>
                            <br><span class="warning"> Please enter a username</span>
                        <?php } ?>
                        <?php if ($errors_old && in_array('username2', $errors_old)) { ?>
                            <br><span class="warning"> Username is not valid!</span>
                        <?php } ?></label><br>
                    <input type="text" name="username2" id="username2"
                        <?php if (isset($username_old)) {
                            echo 'value="' . htmlspecialchars($username_old) . '"';
                        } ?>
                    >
                </p>
                <!-- Password -> don't autofill this!-->
                <!-- NOTE: If username is invalid, no need to show invalid password error... -->
                <p>
                    <label for="password3">Password:
                        <?php if ($missing_old && in_array('password3', $missing_old)) { ?>
                            <br><span class="warning"> Please enter a password</span>
                        <?php } ?>
                        <?php if (($errors_old && in_array('password3', $errors_old)) &&
                            (!in_array('username2', $errors_old))) { ?>
                            <br><span class="warning"> Password is invalid!</span>
                        <?php } ?></label><br>
                    <input type="password" name="password3" id="password3">
                </p>
                <!-- Form submission btn for SIGNIN-->
                <br>
                <input type="submit" name="olduser" value="Sign In">
            </form>
        </div>
    </fieldset>
</main>
<?php include './includes/footer.php'; ?>