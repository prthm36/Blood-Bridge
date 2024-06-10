<?php
session_start();
include "connection.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$conn = newConnection();

$email = $_SESSION["email"];

$stmt = $conn->prepare("SELECT name, email, phone, bloodgroup, gender, birthdate, weight, state, zipcode, district, area, password, donations, received FROM registered_users WHERE email = ?");
if (!$stmt) {
    die("Error: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $bloodgroup, $gender, $birthdate, $weight, $state, $zipcode, $district, $area, $password, $donations, $received);
$stmt->fetch();
$stmt->close();

/* // Calculate the number of donations from the "donations" table
$donations_count = 0;
if ($conn->query("SELECT COUNT(*) as total FROM donations WHERE user_email = '$email'")) {
    $result = $conn->query("SELECT COUNT(*) as total FROM donations WHERE user_email = '$email'");
    $data = $result->fetch_assoc();
    $donations_count = $data['total'];
} */

// Update user information in the database
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["change_password"])) {
        // Password change form is submitted
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $confirm_new_password = $_POST["confirm_new_password"];

        // Validate the current password (You might want to check if it matches the one in the database)
        if (!password_verify($current_password, $password)) {
            // Incorrect current password
            echo "<script>alert('Old password is incorrect. Please try again.');</script>";
        } elseif (strlen($new_password) < 6) {
            // Validate the new password (You can add more validation rules if needed)
            echo "<script>alert('Password must be at least 6 characters long.');</script>";
        } elseif ($new_password !== $confirm_new_password) {
            // New password and Confirm new password do not match
            echo "<script>alert('New password and Confirm new password do not match.');</script>";
        } else {
            // Hash the new password before updating in the database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $stmt = $conn->prepare("UPDATE registered_users SET password=? WHERE email=?");
            if (!$stmt) {
                die("Error: " . $conn->error);
            }
            $stmt->bind_param("ss", $hashed_password, $email);
            $stmt->execute();
            $stmt->close();

            // Show success message
            echo "<script>alert('Password updated successfully.');</script>";
        }
    } else {
        // Other credentials form is submitted
        $name = $_POST["name"];
        $phone = $_POST["phone"];
        $bloodgroup = $_POST["bloodgroup"];
        $gender = $_POST["gender"];
        $birthdate = $_POST["birthdate"];
        $weight = $_POST["weight"];
        $state = $_POST["state"];
        $zipcode = $_POST["zipcode"];
        $district = $_POST["district"];
        $area = $_POST["area"];

        $stmt = $conn->prepare("UPDATE registered_users SET name=?, phone=?, bloodgroup=?, gender=?, birthdate=?, weight=?, state=?, zipcode=?, district=?, area=? WHERE email=?");
        if (!$stmt) {
            die("Error: " . $conn->error);
        }
        $stmt->bind_param("sssssdssssss", $name, $phone, $bloodgroup, $gender, $birthdate, $weight, $state, $zipcode, $district, $area, $email);
        $stmt->execute();
        $stmt->close();

        // Show success message
        echo "<script>alert('Information updated successfully.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bridge - Dashboard</title>

    <!-- favicon-->
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">

    <!-- CSS Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    /* New CSS for Dashboard Section */
    hr {
      border: none; /* Remove the default border */
      height: 1px; /* Set the height to control the thickness */
      background-color: #c5c7c9; /* Set the desired color */
      margin: 20px 0; /* Add some margin to separate form sections */
    }
    .dashboard-section {
      padding: 60px 0;
    }

    .dashboard-container {
      display: flex;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
    }

    .dashboard-form-container {
      flex-basis: 65%;
      margin-top: 6%;
    }

    .dashboard-form {
      background-color: #f4f4f4;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }

    .dashboard-fields {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      grid-gap: 20px;
    }

    .dashboard-field {
      margin-bottom: 20px;
    }

    .donation-password-section {
      flex-basis: 30%;
      margin-top: 6%;
    }

    .donation-received-box {
      background-color: #f4f4f4;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .donation-received-box label {
      font-weight: 600;
    }

    .count {
      font-size: 24px;
      font-weight: 700;
      color: #6c63ff;
    }

    .password-change {
      background-color: #f4f4f4;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .password-change h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .save-changes-btn,
    .logout-btn {
      display: block;
      width: 100%;
      max-width: 200px;
      margin: 0 auto;
      text-align: center;
      background-color: #6c63ff;
      color: #fff;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }

    .save-changes-btn:hover,
    .logout-btn:hover {
      background-color: #524dff;
    }

        /*css for the date and weight fields of form*/
    .dashboard-field input[type="number"],
  .dashboard-field input[type="date"] {
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 4px;
    transition: border-color 0.3s ease;
  }

  .dashboard-field input[type="number"]:focus,
  .dashboard-field input[type="date"]:focus {
    border-color: #6c63ff;
  }

  /* CSS for the password change fields */
  .password-change input[type="password"] {
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 4px;
    transition: border-color 0.3s ease;
  }

  .password-change input[type="password"]:focus {
    border-color: #6c63ff;
  }
  </style>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
<header class="header">
    <div class="header-top">
      <div class="container">
        <ul class="contact-list">
          <li class="contact-item">
            <ion-icon name="mail-outline"></ion-icon>
            <a href="mailto:amitbob060@gmail.com" class="contact-link">amitbob060@gmail.com</a>
          </li>
          <li class="contact-item">
            <ion-icon name="call-outline"></ion-icon>
            <a href="tel:8091727833" class="contact-link">8091727833</a>
          </li>
        </ul>
        <ul class="social-list">
          <li>
            <a href="https://www.facebook.com/andro.pool.54?mibextid=ZbWKwL" class="social-link">
              <ion-icon name="logo-facebook"></ion-icon>
            </a>
          </li>
          <li>
            <a href="https://www.instagram.com/_vladimir_putin.___/" class="social-link">
              <ion-icon name="logo-instagram"></ion-icon>
            </a>
          </li>
          <li>
            <a href="https://twitter.com/Annabel07785340" class="social-link">
              <ion-icon name="logo-twitter"></ion-icon>
            </a>
          </li>
          <li>
            <a href="https://youtu.be/Af0gk_kiGac" class="social-link">
              <ion-icon name="logo-youtube"></ion-icon>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="header-bottom" data-header>
      <div class="container">
        <a href="#" class="logo">Blood Bridge</a>
        <nav class="navbar container" data-navbar>
          <ul class="navbar-list">
            <li>
              <a href="../index.html" class="navbar-link" data-nav-link>Home</a>
            </li>
            <li>
              <a href="#service" class="navbar-link" data-nav-link>Find donor</a>
            </li>
            <li>
              <a href="../about.html" class="navbar-link" data-nav-link>About Us</a>
            </li>
            <li>
              <a href="#blog" class="navbar-link" data-nav-link>Blog</a>
            </li>
            <li>
              <a href="contact.php" class="navbar-link" data-nav-link>Contact</a>
            </li>
          </ul>
        </nav>
        <a href="register.php" class="btn">Login / Register</a>
        <button class="nav-toggle-btn" aria-label="Toggle menu" data-nav-toggler>
          <ion-icon name="menu-sharp" aria-hidden="true" class="menu-icon"></ion-icon>
          <ion-icon name="close-sharp" aria-hidden="true" class="close-icon"></ion-icon>
        </button>
      </div>
    </div>
  </header>

  <section class="section dashboard-section" id="dashboard">
    <div class="container">
      <div class="dashboard-container">
        <!-- User Information Form Section -->
        <div class="dashboard-form-container">
          <form class="dashboard-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="dashboard-title">Dashboard  |  Welcome, <?php echo $name; ?> ! </div>
            <hr>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="dashboard-fields">
                    <div class="dashboard-field">
                        <label>Name:</label>
                        <input type="text" name="name" value="<?php echo $name; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Email:</label>
                        <input type="text" name="email" value="<?php echo $email; ?>" disabled>
                    </div>
                    <div class="dashboard-field">
                        <label>Phone:</label>
                        <input type="text" name="phone" value="<?php echo $phone; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Blood Group:</label>
                        <input type="text" name="bloodgroup" value="<?php echo $bloodgroup; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Gender:</label>
                        <input type="text" name="gender" value="<?php echo $gender; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Birthdate:</label>
                        <input type="date" name="birthdate" value="<?php echo $birthdate; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Weight (kg):</label>
                        <input type="number" name="weight" value="<?php echo $weight; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>State:</label>
                        <input type="text" name="state" value="<?php echo $state; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Zipcode:</label>
                        <input type="text" name="zipcode" value="<?php echo $zipcode; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>District:</label>
                        <input type="text" name="district" value="<?php echo $district; ?>">
                    </div>
                    <div class="dashboard-field">
                        <label>Area:</label>
                        <input type="text" name="area" value="<?php echo $area; ?>">
                    </div>
                </div>

                <button type="submit" class="btn save-changes-btn">Update your details</button>
          </form>
        </div>

        <!-- Donation/Received and Password Change Section -->
        <div class="donation-password-section">
          <!-- Donation and Received Counts Box -->
          <div class="donation-received-box">
            <div class="donation-count">
              <label>Donations:</label>
              <div class="count"><?php echo $donations; ?></div>
            </div>
            <div class="received-count">
              <label>Received:</label>
              <div class="count"><?php echo $received; ?></div>
            </div>
          </div>

          <!-- Password Change Section -->
<section class="password-change">
    <h2>Password Change</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="dashboard-field">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="dashboard-field">
            <label>New Password:</label>
            <input type="password" name="new_password" required>
        </div>
        <div class="dashboard-field">
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_new_password" required>
        </div>
        <button type="submit" name="change_password" class="btn">Change Password</button>
    </form>
</section>

          <br>
          <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
      </div>
    </div>
  </section>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Blood Bridge - connect the donors</title>

		<link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml" />

		<link rel="stylesheet" href="./assets/css/style.css" />

		<link rel="preconnect" href="https://fonts.googleapis.com" />
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
		<link
			href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Roboto:wght@400;500;600&display=swap"
			rel="stylesheet" />
		<style>
			.popup {
				display: flex;
				align-items: center;
				justify-content: center;
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				padding: 20px;
				background: linear-gradient(135deg, #ffffff, #a3d2ee);
				color: #0e254e;
				font-size: 16px;
				z-index: 9999;
				border-radius: 5px;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
			}
		</style>
		<script>
			//  popup message
			function showPopup(message) {
				const popup = document.createElement("div");
				popup.className = "popup";
				popup.textContent = message;
				document.body.appendChild(popup);

				//  close the popup auto
				setTimeout(function () {
					popup.remove();
				}, 3000);
			}
		</script>
	</head>

				<section
					class="section service"
					id="service"
					aria-label="service">
					<div class="container">
						<p class="section-subtitle text-center">
							Find the best Donor For You
						</p>
						<h2 class="h2 section-title text-center">FIND DONOR</h2>
						
						<!-- Replace content with your form -->
						<form class="donor-form">
							<div class="form-group">
								<label for="name">Name:</label>
								<input
									type="text"
									id="name"
									name="name"
									required />
							</div>

							<div class="form-group">
								<label for="phone">Phone:</label>
								<input
									type="tel"
									id="phone"
									name="phone"
									required />
							</div>

							<div class="form-group">
								<label for="email">Email:</label>
								<input
									type="email"
									id="email"
									name="email"
									required />
							</div>

							<div class="form-group">
								<label for="blood-type">Blood Type:</label>
								<select id="blood-type" name="blood-type">
									<option value="A+">A+</option>
									<option value="A-">A-</option>
									<option value="B+">B+</option>
									<option value="B-">B-</option>
									<option value="AB+">AB+</option>
									<option value="AB-">AB-</option>
									<option value="O+">O+</option>
									<option value="O-">O-</option>
								</select>
							</div>

							<div class="form-group">
								<label for="city">City:</label>
								<input
									type="text"
									id="city"
									name="city"
									required />
							</div>

							<div class="form-group">
								<label for="state">State:</label>
								<input
									type="text"
									id="state"
									name="state"
									required />
							</div>	
							<button type="submit" class="btn" >
								Find Donor
							</button>
						</form>
					</div>
				</section>

				<!--about page-->
				<section class="section about" id="about" aria-label="about">
					<div class="container">
						<figure class="about-banner">
							<img
								src="../assets/images/about-banner.png"
								width="470" 
								height="538"
								loading="lazy"
								alt="about banner"
								class="w-100" />
						</figure>
						<div class="about-content">
							<p class="section-subtitle">About Us</p>
							<h2 class="h2 section-title">
								We Care For Your Loved Ones ꨄ︎
							</h2>
							<p class="section-text section-text-1">
								At Blood Bridge, we are passionate about
								connecting blood donors with recipients and
								bridging the gap in the healthcare industry. Our
								mission is to provide a seamless and efficient
								experience for both donors and recipients,
								ensuring timely access to life-saving blood
								transfusions.
							</p>
							<p class="section-text">
								We strive to create a community that fosters
								empathy, support, and solidarity among
								individuals who are committed to making a
								difference. Whether you're a potential donor or
								someone in need of blood, we are here to assist
								you every step of the way.
							</p>
							<a href="about.html" class="btn"
								>Read more About Us</a
							>
						</div>
					</div>
				</section>

				<!--services page-->

				<section class="section doctor" aria-label="doctor">
					<div class="container">
						<p class="section-subtitle text-center">Emergency !</p>
						<h2 class="h2 section-title text-center">
							Our other services
						</h2>
						<ul class="has-scrollbar">
							<li class="scrollbar-item">
								<div class="doctor-card">
									<div
										class="card-banner img-holder"
										style="--width: 460; --height: 500">
										<img
											src="../assets/images/doctor-1.png"
											width="460"
											height="500"
											loading="lazy"
											alt="PREBOOK"
											class="img-cover" />
									</div>
									<h3 class="h3">
										<a href="#" class="card-title"
											>Pre Book Blood</a
										>
									</h3>
									<p class="card-subtitle">
										Book Blood For An upcoming Date
									</p>
								</div>
							</li>
							<li class="scrollbar-item">
								<div class="doctor-card">
									<div
										class="card-banner img-holder"
										style="--width: 460; --height: 500">
										<img
											src="../assets/images/doctor-2.png"
											width="460"
											height="500"
											loading="lazy"
											alt="AMBULANCE"
											class="img-cover" />
									</div>
									<h3 class="h3">
										<a href="#" class="card-title"
											>Call Ambulance</a
										>
									</h3>
									<p class="card-subtitle">
										Get our ambulance service
									</p>
								</div>
							</li>
							<li class="scrollbar-item">
								<div class="doctor-card">
									<div
										class="card-banner img-holder"
										style="--width: 460; --height: 500">
										<img
											src="../assets/images/doctor-3.png"
											width="460"
											height="500"
											loading="lazy"
											alt="WHY DONATE ?"
											class="img-cover" />
									</div>
									<h3 class="h3">
										<a href="#" class="card-title"
											>Why Donate ?</a
										>
									</h3>
									<p class="card-subtitle">
										Why donate blood ?
									</p>
								</div>
							</li>
							<li class="scrollbar-item">
								<div class="doctor-card">
									<div
										class="card-banner img-holder"
										style="--width: 460; --height: 500">
										<img
											src="../assets/images/doctor-4.png"
											width="460"
											height="500"
											loading="lazy"
											alt="CAN YOU DONATE"
											class="img-cover" />
									</div>
									<h3 class="h3">
										<a
											href="../canyoudonate.html"
											class="card-title"
											>Can You Donate ?</a
										>
									</h3>
									<p class="card-subtitle">
										Check that can you donate blood
									</p>
								</div>
							</li>
						</ul>
					</div>
				</section>

				<!--Latest Updates-->
				<section class="section blog" id="blog" aria-label="blog">
					<div class="container">
						<p class="section-subtitle text-center">Our Blog</p>
						<h2 class="h2 section-title text-center">
							Latest Updates & News
						</h2>
						<ul class="blog-list">
							<li> 	
								<div class="blog-card">
									<figure
										class="card-banner img-holder"
										style="--width: 1180; --height: 800">
										<img
											src="../assets/images/update1.jpg"
											width="1180"
											height="800"
											loading="lazy"
											alt="Cras accumsan nulla nec lacus ultricies placerat."
											class="img-cover" />
										<div class="card-badge">
											<ion-icon
												name="calendar-outline"></ion-icon>
											<time
												class="time"
												datetime="2023-06-14"
												>14th June 2023</time
											>
										</div>
									</figure>
									<div class="card-content">
										<h3 class="h3">
											<a href="https://economictimes.indiatimes.com/news/how-to/blood-donation-who-can-donate-who-cant-and-what-are-the-rules-for-blood-donation/articleshow/100983529.cms" class="card-title"
												>Blood Donation: Who can donate, who can't, and what are the rules for blood donation?
												</a
											>
										</h3>
										<p class="card-text">
											Blood donation is a noble act that saves countless lives every day. By donating..
										</p>
										<a href="https://economictimes.indiatimes.com/news/how-to/blood-donation-who-can-donate-who-cant-and-what-are-the-rules-for-blood-donation/articleshow/100983529.cms" class="card-link"
											>Read More</a
										>
									</div>
								</div>
							</li>
							<li>
								<div class="blog-card">
									<figure
										class="card-banner img-holder"
										style="--width: 1180; --height: 800">
										<img
											src="../assets/images/update2.jpeg"
											width="1180"
											height="800"
											loading="lazy"
											alt="Dras accumsan nulla nec lacus ultricies placerat."
											class="img-cover" />
										<div class="card-badge">
											<ion-icon
												name="calendar-outline"></ion-icon>
											<time
												class="time"
												datetime="2023-06-29"
												>29th June 2023</time
											>
										</div>
									</figure>
									<div class="card-content">
										<h3 class="h3">
											<a href="https://health.economictimes.indiatimes.com/news/industry/ways-to-address-indias-blood-donation-and-supply-crisis/101351499" class="card-title"
												>Ways to address India’s blood donation and supply crisis</a>
										</h3>
										<p class="card-text">

											India experiences up to 12,000 patient deaths each day as a result of delayed ac ..

										</p>
										<a href="https://health.economictimes.indiatimes.com/news/industry/ways-to-address-indias-blood-donation-and-supply-crisis/101351499" class="card-link"
											>Read More</a
										>
									</div>
								</div>
							</li>
							<li>
								<div class="blog-card">
									<figure
										class="card-banner img-holder"
										style="--width: 1180; --height: 800">
										<img
											src="../assets/images/update 3.jpg"
											width="1180"
											height="800"
											loading="lazy"
											alt="Seas accumsan nulla nec lacus ultricies placerat."
											class="img-cover" />
										<div class="card-badge">
											<ion-icon
												name="calendar-outline"></ion-icon>
											<time
												class="time"
												datetime="2022-09-12"
												>13th Sept 2022</time
											>
										</div>
									</figure>
									<div class="card-content">
										<h3 class="h3">
											<a href="https://www.hindustantimes.com/india-news/mega-blood-donation-drive-to-celebrate-pm-modi-s-birthday-101663008086217.html" class="card-title"
												>Mega blood donation drive to celebrate PM Modi’s birthday</a
											>
										</h3>
										<p class="card-text">
											On the occasion of Prime Minister Narendra Modi’s birthday on September 17, Union health ministry will start a mega drive for voluntary.. 
										</p>
										<a href="https://www.hindustantimes.com/india-news/mega-blood-donation-drive-to-celebrate-pm-modi-s-birthday-101663008086217.html" class="card-link"
											>Read More</a
										>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</section>
			</article>
		</main>


		


     
</body>
</html>