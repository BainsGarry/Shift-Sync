<?php

@include 'config.php';

session_start();

// login_form.php
if(isset($_POST['submit'])){
    
    $email = "";
    $pass = "";
    
    if (isset($_POST['email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
    }
 
    if (isset($_POST['password'])) {
        $pass = $_POST['password'];
    }
    
    $stmt = $conn->prepare("SELECT * FROM user_form WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
 
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
 
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
 
            if($row['user_type'] == 'admin'){
                $_SESSION['admin_name'] = $row['name'];
                header('location:admin_user_page.php');
            } elseif($row['user_type'] == 'user'){
                $_SESSION['user_name'] = $row['name'];
                header('location:user_page.php');
            }
        } else {
            $error[] = 'incorrect password!';
        }
    } else {
        $error[] = 'incorrect email or does not exist!';
    }
    $stmt->close();
 }
 

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shift Sync Login</title>
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

<div class="logo-container">
   <img src="ShiftSync Logo.png" alt="Shift Sync Logo" class="logo-image">
</div>

<div class="form-container">
   <form action="" method="post">
      <h3>Shift Sync login</h3>
      <?php
      if(isset($error)){
         foreach($error as $error){
            echo '<span class="error-msg">'.$error.'</span>';
         };
      };
      ?>
      <input type="email" name="email" required placeholder="enter your email">
      <input type="password" name="password" required placeholder="enter your password">
      <input type="submit" name="submit" value="login now" class="form-btn">
      <p>don't have an account? <a href="register_form.php">register now</a></p>
   </form>

</div>

</body>
</html>
