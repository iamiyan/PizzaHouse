<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `user` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:users_accounts.php');
}

if(isset($_GET['update'])){
   $update_id = $_GET['update'];
   header('location:user_profile_update.php?id='.$update_id);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>user accounts</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href=" admin_style.css">

</head>
<body style="background-image: url('<?php echo './images/pizzabg2.jpg'; ?>'); background-size: cover; background-position: center;">

<?php include 'admin_header.php' ?>

<?php
   // check if the search form is submitted
   if(isset($_GET['submit'])) {
      // get the search term from the input field
      $search_term = $_GET['search'];

      // construct the SQL query to search for user data
      $select_accounts = $conn->prepare("SELECT * FROM `user` WHERE name LIKE '%$search_term%' OR email LIKE '%$search_term%'");
      $select_accounts->execute();
   } else {
      // construct the SQL query to fetch all user data
      $select_accounts = $conn->prepare("SELECT * FROM `user`");
      $select_accounts->execute();
   }
?>


<section class="accounts">

   <h1 class="heading">user accounts</h1>

   <div class="box-container">

      <div class="search-box">
         <h1>SEARCH FOR USERS</h1>
         <form method="get" action="users_accounts.php">
            <input type="text" name="search" placeholder="Search user..." value="<?= isset($search_term) ? $search_term : ''; ?>">
            <button type="submit" name="submit"><i class="fas fa-search"></i></button>
         </form>
      </div>

   <?php
      if($select_accounts->rowCount() > 0){
         while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> user id : <span><?= $fetch_accounts['id']; ?></span> </p>
      <p> username : <span><?= $fetch_accounts['name']; ?></span> </p>
      <p> email : <span><?= $fetch_accounts['email']; ?></span> </p>
      <div class="flex-btn">
         <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('delete this account?')" class="delete-btn">delete</a>
         <?php
            if($fetch_accounts['id']){
               echo '<a href="user_profile_update.php?id=' . $fetch_accounts['id'] . '" class="option-btn">update</a>';
            }
         ?>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no accounts available!</p>';
      }
   ?>

   </div>

</section>



<script src="admin_script.js"></script>

</body>
</html>