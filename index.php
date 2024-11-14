<?php
	// Initialize session
	session_start();

	if (!isset($_SESSION['loggedin']) && $_SESSION['loggedin'] !== false) {
		header('location: auth/login.php');
		exit;
	}

	include 'views/layouts/main_layout.php';