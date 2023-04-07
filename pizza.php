<?php

include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM  user  WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'username or email already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO  user (name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'registered successfully, login now please!';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE  cart  SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cart quantity updated!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM  cart  WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:pizza.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:pizza.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'please login first!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM  cart  WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'already added to cart';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO  cart (user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'added to cart!';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'Please Login First!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'flat no.'.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM  cart  WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO  orders (user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM  cart  WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'order placed successfully!';
      }else{
         $message[] = 'your cart empty!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <link rel="icon" href="/images/logo2.png">
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pizza House</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="styles.css">

</head>
<body>


<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>
   
<!-- header section starts  -->

<header class="header">

   <section class="flex">

      <a href="home.php" class="logo"><img src="images/logo2.png"</a>

      <nav class="navbar">
         <a href="home.php">Home</a>
         <a href="menu.php">Menu</a>
         <a href="order-online.php">Order Online</a>      
         <a href="about.php">About Us</a>
         <a href="contact.php">Contact Us</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM  cart  WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
      </div>

   </section>

</header>
<!-- header section ends -->

<div class="user-account">

   <section>
        <div id="close-account"><span>Close</span></div>

        <div class="user">
        <?php
            $select_user = $conn->prepare("SELECT * FROM  user  WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
                while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                    echo '<p>welcome ! <span>'.$fetch_user['name'].'</span></p>';
                    echo '<a href="pizza.php?logout" class="btn">logout</a>';
                }
            }else{
                echo '<p><span>you are not logged in now!</span></p>';
            }
        ?>
        </div>

        <div class="display-orders">
        <?php
            $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
                while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                    echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
                }
            }else{
                echo '<p><span>your cart is empty!</span></p>';
            }
        ?>
        </div>
 
       <div class="flex">
 
          <form action="user_login.php" method="post">
             <h3>login now</h3>
             <input type="email" name="email" required class="box" placeholder="Enter your email" maxlength="50">
             <input type="password" name="pass" required class="box" placeholder="Enter your password" maxlength="20">
             <input type="submit" value="login now" name="login" class="btn">
          </form>
 
          <form action="" method="post">
             <h3>register now</h3>
             <input type="text" name="name" required class="box" placeholder="Enter your name" maxlength="20">
             <input type="email" name="email" required class="box" placeholder="Enter your email" maxlength="50">
             <input type="password" name="pass" required class="box" placeholder="Enter your password" maxlength="20">
             <input type="password" name="cpass" required class="box" placeholder="Confirm your password" maxlength="20">
             <input type="submit" value="register now" name="register" class="btn">
          </form>
 
       </div>
 
    </section>
 
 </div>
 
 <div class="my-orders">

<section>

   <div id="close-orders"><span>close</span></div>

   <h3 class="title"> MY ORDERS </h3>
   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
      $select_orders->execute([$user_id]);
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
      <p> total_orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
      <p> total price : <span>RM<?= $fetch_orders['total_price']; ?></span> </p>
      <p> payment status : <span style="color:<?php if($fetch_orders['payment_status'] == 'pending'){ echo 'red'; }else{ echo 'green'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">nothing ordered yet!</p>';
   }
   ?>

</section>

</div>
        
<div class="shopping-cart">
        
            <section>
        
            <div id="close-cart"><span>Close</span></div>

        <?php
        $grand_total = 0;
        $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $select_cart->execute([$user_id]);
        if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                $grand_total += $sub_total; 
        ?>
        <div class="box">
        <a href="pizza.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
        <img src=" images/images/<?= $fetch_cart['image']; ?>" alt="">
        <div class="content">
            <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
            <form action="" method="post">
            <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
                <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </form>
        </div>
        </div>
        <?php
        }
        }else{
        echo '<p class="empty"><span>Your Cart Is Empty!</span></p>';
        }
        ?>

        <div class="cart-total"><h2>Grand Total : <span>RM<?= $grand_total; ?></span></h2></div>

        <a href="checkout.php" class="btn">order now</a>
 
    </section>
 
 </div>

<!-- menu section starts  -->
<section id="menu" class="menu">

    <h1 class="heading">PIZZA</h1>
 
    <div class="box-container">
 
    <?php
         $select_products = $conn->prepare("SELECT * FROM products");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price"><p>RM<?= $fetch_products['price'] ?> </p></div>
         <img src=" images/images/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><h3><?= $fetch_products['name'] ?></h3></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add_to_cart" value="add to cart">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
    </div>
    <br>
    <br>
 
    <a href="menu.php" class="btn">BACK TO MAIN MENU</a>
 </section>

 <section class="footer">

   
    <div class="box-container">
 
       <div class="box">
          <i class="fas fa-phone"></i>
          <h3>Phone number</h3>
          <p>Tel. : 03-934 3429</p>
          <p>Fax. : 03-131 9324</p>
       </div>
 
       <div class="box">
          <i class="fas fa-map-marker-alt"></i>
          <h3>our address</h3>
          <p>Kuala Lumpur, Malaysia, 56100</p>
       </div>
 
       <div class="box">
          <i class="fas fa-clock"></i>
          <h3>opening hours</h3>
          <p>09:00am to 10:00pm</p>
       </div>
 
       <div class="box">
          <i class="fas fa-envelope"></i>
          <h3>email address</h3>
          <p>pizzahousehq@gmail.com</p>
       </div>
 
    </div>
 
    <div class="credit">
       <p>Copyright © 2023 Pizza House Malaysia. MY Pizza House SDN. BHD. 
       All Rights Reserved.</p>
       </div>
 
 </section>
 
 <!-- footer section ends -->
 
 <!-- custom js file link  -->
 <script src="script.js"></script>
 
 </body>
 </html>
