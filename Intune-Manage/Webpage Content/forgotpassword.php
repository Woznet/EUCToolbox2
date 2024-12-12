<?php
/**
 * This file is part of a GPL-licensed project.
 *
 * Copyright (C) 2024 Andrew Taylor (andrew.taylor@andrewstaylor.com)
 * A special thanks to David at Codeshack.io for the basis of the login system!
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://github.com/andrew-s-taylor/public/blob/main/LICENSE>.
 */
?>
<?php
include 'main.php';
// Output message
$msg = '';
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (isset($_POST['email'])) {
    // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
    $stmt = $con->prepare('SELECT email FROM accounts WHERE email = ?');
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $stmt->store_result();
    // Check if the email exists...
    if ($stmt->num_rows > 0) {
		// Bind results
		$stmt->bind_result($username);
		$stmt->fetch();
    	$stmt->close();
        // Email exist, the $msg variable will be used to show the output message (on the HTML form)
        // Update the reset code in the database
    	$uniqid = uniqid();
        $stmt = $con->prepare('UPDATE accounts SET reset = ? WHERE email = ?');
        $stmt->bind_param('ss', $uniqid, $_POST['email']);
        $stmt->execute();
        $stmt->close();
        // Change the link below from "yourdomain.com" to your own domain name where the PHP login system is hosted
        $reset_link = 'https://manage.euctoolbox.com/app/resetpassword.php?email=' . $_POST['email'] . '&code=' . $uniqid;
		// Email subject
		$subject = 'Password Reset';
		// Email headers
		$headers = 'From: ' . mail_from . "\r\n" . 'Reply-To: ' . mail_from . "\r\n" . 'Return-Path: ' . mail_from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
		// Email template
		$email_template = str_replace(['%link%', '%username%'], [$reset_link, $username], file_get_contents('resetpass-email-template.html'));
		// Send email to captured email address
		mail($_POST['email'], $subject, $email_template, $headers, mail_from);
		// Output success message
        $msg = 'Reset password link has been sent to your email!';
    } else {
		// Output error message
        $msg = 'We do not have an account with that email!';
    }
}
?>
<?php
$sitename = "Intune Manager from EUC Toolbox";
$pagetitle = "Intune Manager";
include "header1.php";
?>
		<div class="login">
			<h1>Forgot Password</h1>
			<form action="" method="post">
				<table class="styled-table"><tr><td>
                <label for="email">
					<i class="fas fa-envelope"></i>
				</label>
				<input type="email" name="email" placeholder="Your Email" id="email" required>
				</td><td>

				<input type="submit" value="Submit">
				</td></tr></table>
			</form>
			<div class="msg"><?=$msg?></div>
		</div>
		<?php
include "footer.php";
?>