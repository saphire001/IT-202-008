<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
	//this will redirect to login and kill the rest of this script (prevent it from executing)
	flash("You must be logged in to access this page");
	die(header("Location: login.php"));
}

$db = getDB();
//save data if we submitted the form
if (isset($_POST["saved"])) {
	$isValid = true;
	//check if our email changed
	$newEmail = get_email();
	if (get_email() != $_POST["email"]) {
		//TODO we'll need to check if the email is available
		$email = $_POST["email"];
		$stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
		$stmt->execute([":email" => $email]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$inUse = 1;//default it to a failure scenario
		if ($result && isset($result["InUse"])) {
			try {
				$inUse = intval($result["InUse"]);
			}
			catch (Exception $e) {

			}
		}
		if ($inUse > 0) {
			flash("Email already in use");
			//for now we can just stop the rest of the update
			$isValid = false;
		}
		else {
			$newEmail = $email;
		}
	}
	$newUsername = get_username();
	if (get_username() != $_POST["username"]) {
		$username = $_POST["username"];
		$stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
		$stmt->execute([":username" => $username]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$inUse = 1;//default it to a failure scenario
		if ($result && isset($result["InUse"])) {
			try {
				$inUse = intval($result["InUse"]);
			}
			catch (Exception $e) {

			}
		}
		if ($inUse > 0) {
			flash("Username already in use");
			//for now we can just stop the rest of the update
			$isValid = false;
		}
		else {
			$newUsername = $username;
		}
	}
	if ($isValid) {
		$stmt = $db->prepare("UPDATE Users set email = :email, username= :username, first_name = :first_name, last_name = :last_name privacy = :privacy 
		where id = :id");
		$r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":id" => get_user_id(), ":first_name" => $_POST["first_name"],
		":last_name" => $_POST["last_name"],":privacy" => $_POST["privacy"]]);
		if ($r) {
			flash("Updated profile");
		}
		else {
			flash("Error updating profile");
		}
		//password is optional, so check if it's even set
		//if so, then check if it's a valid reset request
		if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
			if ($_POST["password"] == $_POST["confirm"]) {
				$password = $_POST["password"];
				$hash = password_hash($password, PASSWORD_BCRYPT);
				//this one we'll do separate
				$stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
				$r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
				if ($r) {
					flash("Reset Password");
				}
				else {
					flash("Error resetting password");
				}
			}
		}
//fetch/select fresh data in case anything changed
		$stmt = $db->prepare("SELECT email, username, first_name, last_name, privacy from Users WHERE id = :id LIMIT 1");
		$stmt->execute([":id" => get_user_id()]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result) {
			$email = $result["email"];
			$username = $result["username"];
			//let's update our session too
			$_SESSION["user"]["email"] = $email;
			$_SESSION["user"]["username"] = $username;
			$_SESSION["user"]["first_name"] = $result["first_name"];
			$_SESSION["user"]["last_name"] = $result["last_name"];
			$_SESSION["user"]["privacy"] = $result["privacy"];
		}
	}
	else {
		//else for $isValid, though don't need to put anything here since the specific failure will output the message
	}
}


?>

	<form method="POST">
		<label for="email">Email</label>
		<input type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
		<label for="username">Username</label>
		<input type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
		<label for="first_name">First Name</label>
		<input type="text" name="first_name" maxlength="60" value="<?php safer_echo(get_first_name()); ?>">
		<label for="last_name">Last Name</label> 
		<input type="text" name="last_name" maxlength="60" value="<?php safer_echo(get_last_name()); ?>">
		<label for="privacy">Privacy</lable>
		<select id="'privacy" name= "privacy"> 
			<option value="private" <?php echo get_privacy() == "private" ? "selected": ""; ?>>Private</option>
			<option value="public" <?php echo get_privacy() == "public" ? "selected": ""; ?>>Public</option>
		</select>
		<!-- DO NOT PRELOAD PASSWORD-->
		<label for="pw">Password</label>
		<input type="password" name="password"/>
		<label for="cpw">Confirm Password</label>
		<input type="password" name="confirm"/>
		<input type="submit" name="saved" value="Save Profile"/>
	</form>
<?php require(__DIR__ . "/partials/flash.php");