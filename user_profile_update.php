<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['update'])){

   $id = $_GET['id'];
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $update_user_data = $conn->prepare("UPDATE `user` SET name = ?, email = ? WHERE id = ?");
   $update_user_data->execute([$name, $email, $_POST['id']]);

   $message[] = 'User data updated successfully!';

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>admin update user data</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href=" admin_style.css">

</head>
<body style="background-image: url('<?php echo './images/pizzabg2.jpg'; ?>'); background-size: cover; background-position: center;">

<?php include 'admin_header.php' ?>

<section class="form-container">

   <form action="" method="post">
      <h3>update user data</h3>
      <?php
         if(isset($_GET['id'])){
            $id = $_GET['id'];
            $fetch_user_data = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $fetch_user_data->execute([$id]);
            $user_data = $fetch_user_data->fetch();
      ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="text" name="name" value="<?= $user_data['name'] ?>" required placeholder="enter user's name" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="email" name="email" value="<?= $user_data['email'] ?>" required placeholder="enter user's email" maxlength="50"  class="box">
            <input type="password" name="old_pass" placeholder="enter old password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="new_pass" placeholder="enter new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="confirm_pass" placeholder="confirm new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="update now" class="btn" name="update">
      <?php
         }else{
            echo '<p>No user selected!</p>';
         }
      ?>
   </form>

</section>


<script src="  admin_script.js"></script>

</body>
</html>