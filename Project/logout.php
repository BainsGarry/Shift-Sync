<?php

@include 'config.php';

session_start();
session_unset(); 
session_destroy(); 

header('location:login_form.php'); // Redirect the user to the login form page

?>