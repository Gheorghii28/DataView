<?php
	// Include config file
	require_once '../config/config.php';


	// Define variables and initialize with empty values
	$username = $password = $confirm_password = "";

	$username_err = $password_err = $confirm_password_err = "";

	// Process submitted form data
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		// Check if username is empty
		if (empty(trim($_POST['username']))) {
			$username_err = "Please enter a username.";

			// Check if username already exist
		} else {

			// Prepare a select statement
			$sql = 'SELECT id FROM users WHERE username = ?';

			if ($stmt = $mysql_db->prepare($sql)) {
				// Set parmater
				$param_username = trim($_POST['username']);

				// Bind param variable to prepares statement
				$stmt->bind_param('s', $param_username);

				// Attempt to execute statement
				if ($stmt->execute()) {
					
					// Store executed result
					$stmt->store_result();

					if ($stmt->num_rows == 1) {
						$username_err = 'This username is already taken.';
					} else {
						$username = trim($_POST['username']);
					}
				} else {
					echo "Oops! {$username}, something went wrong. Please try again later.";
				}

				// Close statement
				$stmt->close();
			} else {

				// Close db connction
				$mysql_db->close();
			}
		}

		// Validate password
	    if(empty(trim($_POST["password"]))){
	        $password_err = "Please enter a password.";     
	    } elseif(strlen(trim($_POST["password"])) < 6){
	        $password_err = "Password must have atleast 6 characters.";
	    } else{
	        $password = trim($_POST["password"]);
	    }
    
	    // Validate confirm password
	    if(empty(trim($_POST["confirm_password"]))){
	        $confirm_password_err = "Please confirm password.";     
	    } else{
	        $confirm_password = trim($_POST["confirm_password"]);
	        if(empty($password_err) && ($password != $confirm_password)){
	            $confirm_password_err = "Password did not match.";
	        }
	    }

	    // Check input error before inserting into database

	    if (empty($username_err) && empty($password_err) && empty($confirm_err)) {

	    	// Prepare insert statement
			$sql = 'INSERT INTO users (username, password) VALUES (?,?)';

			if ($stmt = $mysql_db->prepare($sql)) {

				// Set parmater
				$param_username = $username;
				$param_password = password_hash($password, PASSWORD_DEFAULT); // Created a password

				// Bind param variable to prepares statement
				$stmt->bind_param('ss', $param_username, $param_password);

				// Attempt to execute
				if ($stmt->execute()) {
					// Redirect to login page
					header('location: ./login.php');
					// echo "Will  redirect to login page";
				} else {
					echo "Something went wrong. Try signing in again.";
				}

				// Close statement
				$stmt->close();	
			}

			// Close connection
			$mysql_db->close();
	    }
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Sign in</title>
	<!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/img/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="MyWebSite" />
    <link rel="manifest" href="/assets/img/favicon/site.webmanifest" />
	<!-- Tailwind and Flowbite -->
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
	<script src="../assets/css/tailwind.config.js"></script>
</head>
<body>
	<main>
		<section class="bg-gray-50 dark:bg-gray-900">
		  <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
		      <a href="/" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
		          <img class="w-8 h-8 mr-2" src="../assets/img/svg/data-management.svg" alt="data-management">
		          DataView    
		      </a>
		      <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
		          <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
		              <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
		                  Create an account
		              </h1>
		              <form class="space-y-4 md:space-y-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
		                  <div>
		                      <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
		                      <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $username ?>" placeholder="">
							  <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-medium"><?php echo $username_err;?></p>
		                  </div>
		                  <div>
		                      <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
		                      <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  value="<?php echo $password ?>">
							  <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-medium"><?php echo $password_err;?></p>
		                  </div>
		                  <div>
		                      <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm password</label>
		                      <input type="confirm_password" name="confirm_password" id="confirm_password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $confirm_password; ?>">
							  <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span class="font-medium"><?php echo $confirm_password_err;?></p>
		                  </div>
		                  <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Create an account</button>
		                  <p class="text-sm font-light text-gray-500 dark:text-gray-400">
		                      Already have an account? <a href="login.php" class="font-medium text-primary-600 hover:underline dark:text-primary-500">Login here</a>
		                  </p>
		              </form>
		          </div>
		      </div>
		  </div>
		</section>
	</main>
	<!-- Flowbite JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>