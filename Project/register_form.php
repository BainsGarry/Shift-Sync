<?php

@include 'config.php';

// Check if the form has been submitted
if(isset($_POST['submit'])){

   // Sanitize and store the input data
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = $_POST['password'];
   $cpass = $_POST['cpassword'];
   $user_type = $_POST['user_type'];

   // Check if the user already exists in the database
   $select = "SELECT * FROM user_form WHERE email = '$email'";
   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){
      $error[] = 'user already exist!';
   }else{
      // Check if the passwords match
      if($pass != $cpass){
         $error[] = 'password not matched!';
      }else{
         // Hash the password and insert the new user into the database
         $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
         $insert = "INSERT INTO user_form(name, email, password, user_type) VALUES(?,?,?,?)";
         $stmt = $conn->prepare($insert);
         $stmt->bind_param("ssss", $name, $email, $hashedPass, $user_type);
         $stmt->execute();
         $stmt->close();
         // Redirect to login page after successful registration
         header('location:login_form.php');
      }
   }
};
;


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shift Sync Register</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<div class="diamonds-background">
     <!-- Generate 20 divs for the diamonds -->
   <!-- Use inline styles to assign a unique index to each diamond -->
   <div style="--i:1; --random:0.2;"></div>
   <div style="--i:2; --random:0.7;"></div>
   <div style="--i:3; --random:0.1;"></div>
   <div style="--i:4; --random:0.9;"></div>
   <div style="--i:5; --random:0.1;"></div>
   <div style="--i:6; --random:0.2;"></div>
   <div style="--i:7; --random:0.9;"></div>
   <div style="--i:8; --random:0.2;"></div>
   <div style="--i:9; --random:0.2;"></div>
   <div style="--i:10; --random:0.2;"></div>
   <div style="--i:11; --random:0.1;"></div>
   <div style="--i:12; --random:0.7;"></div>
   <div style="--i:13; --random:0.2;"></div>
   <div style="--i:14; --random:0.1;"></div>
   <div style="--i:15; --random:0.7;"></div>
   <div style="--i:16; --random:0.9;"></div>
   <div style="--i:17; --random:0.9;"></div>
   <div style="--i:18; --random:0.7;"></div>
   <div style="--i:19; --random:0.2;"></div>
   <!-- ... add more divs up to 20 -->
   <div style="--i:20; --random:0.7;"></div>
</div>
   
<div class="form-container">

   <form action="" method="post">
      <h3>register now</h3>
      <?php
      if(isset($error)){
         foreach($error as $error){
            echo '<span class="error-msg">'.$error.'</span>';
         };
      };
      ?>
      <input type="text" name="name" required placeholder="enter your name">
      <input type="email" name="email" required placeholder="enter your email">
      <input type="password" name="password" required placeholder="enter your password">
      <input type="password" name="cpassword" required placeholder="confirm your password">
      <select name="user_type">
         <option value="user">user</option>
         <option value="admin">admin</option>
      </select>
      <input type="submit" name="submit" value="register now" class="form-btn">
      <p>already have an account? <a href="login_form.php">login now</a></p>
   </form>

</div>

</body>
</html>