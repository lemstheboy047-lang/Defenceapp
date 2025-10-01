diff --git a/register.php b/register.php
index 8849c706cac851c20c54997bfbf582acf1232de7..6615079b596afcfad22c39a4e5ec7e9f998ccdda 100644
--- a/register.php
+++ b/register.php
@@ -1,205 +1,110 @@
 <?php
-include 'config.php';
-
-if ($_SERVER["REQUEST_METHOD"] == "POST") {
-    $username = $_POST['username'];
-    $email = $_POST['email'];
-    $password = $_POST['password'];
-    $confirm_password = $_POST['confirm_password'];
-
-    $password_length = strlen($password);
-    $password_has_uppercase = preg_match('/[A-Z]/', $password);
-    $password_has_lowercase = preg_match('/[a-z]/', $password);
-    $password_has_number = preg_match('/\d/', $password);
-    $password_has_special_char = preg_match('/[^A-Za-z0-9]/', $password);
-
-    if (!$password_has_uppercase || !$password_has_lowercase || !$password_has_number || !$password_has_special_char || $password_length < 8) {
-        $error = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character";
-    } elseif ($password != $confirm_password) {
-        $error = "Passwords do not match";
+session_start();
+require 'config.php';
+
+$error = '';
+$success = '';
+
+if ($_SERVER['REQUEST_METHOD'] === 'POST') {
+    $name = trim($_POST['name'] ?? '');
+    $phone = trim($_POST['phone'] ?? '');
+    $email = trim($_POST['email'] ?? '');
+    $password = $_POST['password'] ?? '';
+    $confirm = $_POST['confirm_password'] ?? '';
+
+    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
+        $error = 'All required fields must be completed.';
+    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
+        $error = 'Please provide a valid email address.';
+    } elseif ($password !== $confirm) {
+        $error = 'Passwords do not match.';
+    } elseif (strlen($password) < 8 ||
+        !preg_match('/[A-Z]/', $password) ||
+        !preg_match('/[a-z]/', $password) ||
+        !preg_match('/\d/', $password) ||
+        !preg_match('/[^A-Za-z0-9]/', $password)) {
+        $error = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
     } else {
-        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
+        $conn->begin_transaction();
+        try {
+            $stmt = $conn->prepare('INSERT INTO User (Name, PhoneNumber, Email) VALUES (?, ?, ?)');
+            $stmt->bind_param('sss', $name, $phone, $email);
+            $stmt->execute();
+            $userId = $stmt->insert_id;
+            $stmt->close();
 
-        $sql = "SELECT * FROM users WHERE email = ?";
-        $stmt = $conn->prepare($sql);
-        $stmt->bind_param("s", $email);
-        $stmt->execute();
-        $result = $stmt->get_result();
-        if ($result->num_rows > 0) {
-            $error = "Email is already in use";
-        } else {
-            $sql = "INSERT INTO users (username, email, password) VALUES (?,?,?)";
-            $stmt = $conn->prepare($sql);
-            $stmt->bind_param("sss", $username, $email, $hashed_password);
+            $hashed = password_hash($password, PASSWORD_DEFAULT);
+            $stmt = $conn->prepare('INSERT INTO Account (UserID, PhoneNumber, Password) VALUES (?, ?, ?)');
+            $stmt->bind_param('iss', $userId, $phone, $hashed);
             $stmt->execute();
+            $stmt->close();
 
-            if ($stmt->affected_rows > 0) {
-                header('Location: auth.php');
-                exit;
+            $stmt = $conn->prepare('INSERT INTO Customer (UserID) VALUES (?)');
+            $stmt->bind_param('i', $userId);
+            $stmt->execute();
+            $stmt->close();
+
+            $conn->commit();
+            $success = 'Account created! You can now sign in.';
+        } catch (mysqli_sql_exception $e) {
+            $conn->rollback();
+            if ($e->getCode() === 1062) {
+                $error = 'An account with that email already exists.';
             } else {
-                $error = "Please enter proper credentials";
+                $error = 'Registration failed. Please try again.';
             }
         }
     }
 }
-
 ?>
 <!DOCTYPE html>
 <html lang="en">
 <head>
-    <meta charset="UTF-8">
-    <meta name="viewport" content="width=device-width, initial-scale=1.0">
-    <title>Register</title>
-
-    <style>
-
-   .or-button {
-      background-color: transparent;
-      border: none;
-      color: #666;
-      font-size: 16px;
-      font-weight: bold;
-      cursor: default; 
-      margin: 10px;
-  
-    }
-
-   .or-button:hover {
-      background-color: transparent;
-    }
-
-    
-    body {
-      font-family: 'Times New Roman', sans-serif;
-      background-image: linear-gradient(to right, #4776E6, #8E54E9);
-      display: flex;
-      justify-content: center;
-      align-items: center;
-      height: 100vh;
-
-    }
-
-    .reg-container {
-      background-color: #90f9ed;
-      border-radius: 10px;
-      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
-      padding: 40px;
-      width: 400px;
-      margin: 40px;
-    }
-
-    h2 {
-      text-align: center;
-      margin-bottom: 20px;
-    }
-
-    input[type="text"],
-    input[type="email"],
-    input[type="password"] {
-      width: 100%;
-      padding: 10px;
-      margin: 10px 0;
-      border: 1px solid #ccc;
-      border-radius: 3px;
-    }
-    #form-group {
-      margin-bottom: 10px;
-    }
-    label {
-      display: block;
-      font-size: 14px;
-      color: #333;
-      margin-bottom: 5px;
-
-    }
-
-    input[type="text"]{
-      border: none;
-      border-radius: 4px;
-      padding: 12px 20px;
-      font-size: 16px;
-      width: 100%;
-      box-sizing: border-box;
-
-    }
-    input[type="password"] {
-      border: none;
-      border-radius: 4px;
-      padding: 12px 20px;
-      font-size: 16px;
-      width: 100%;
-      box-sizing: border-box;
-    }
-
-  .hey{
-     margin: 10px; 
-  padding: 10px 20px;
-  border: none;
-  border-radius: 10px;
-  background-color: #4CAF50;
-  color: #fff;
-  cursor: pointer;
-  }
-    #button-container{
-        text-align: center; 
-  margin-top: 20px;
-    }
-  
-    button {
-  display: flex; 
-  margin: 0px; 
-  padding: 10px 20px;
-  border: none;
-  border-radius: 10px;
-  background-color: #4CAF50;
-  color: #fff;
-  cursor: pointer;
-  text-align: center; 
-}
-
-    button:hover {
-      background-color: #875cba;
-    }
-    </style>
+  <meta charset="UTF-8">
+  <meta name="viewport" content="width=device-width, initial-scale=1.0">
+  <title>Create Servesoft Account</title>
+  <link rel="stylesheet" href="styles.css">
 </head>
 <body>
-
-    <div class="reg-container">
-        <h2>Registration</h2>
-        <form  method="post">
-            <div class="form-group">
-                <label for="username"><b>Username</b></label>
-                <input type="text" id="username" name="username" placeholder="Enter the username" value="<?php echo isset($username)? $username : '';?>" required>
-            </div>
-            <div class="form-group">
-                <label for="email"><b>Email</b></label>
-                <input type="text" id="email" name="email" placeholder="Enter the email" value="<?php echo isset($email)? $email : '';?>" required>
-            </div>
-            <div class="form-group">
-                <label for="password"><b>Password</b></label>
-                <input type="password" id="password" name="password" placeholder="Enter the password" value="" required>
-            </div>
-            <div class="form-group">
-                <label for="confirm_password"><b>Confirm Password</b></label>
-                <input type="password" id="confirm_password" name="confirm_password" placeholder="confirm your password" value="" required>
-                
-            </div>
-           <div class="button-container">
-             <button type="submit"><b>Register</b></button>
-            <button class="or-button">OR</button>
-        
-            
-</div>
-<div>
-
-</div>
-        </form>
-        <button id="hey"><a href="auth.php"><b>Login</b></a></button>  
-        <?php if (isset($error)):?>
-            <div>
-                <?php echo $error;?>
-            </div>
-        <?php endif;?>
-    </div>
+  <section class="auth-shell">
+    <article class="card">
+      <header>
+        <h1>Create Your Account</h1>
+        <p>Join Servesoft to manage your reservations and orders.</p>
+      </header>
+      <?php if ($error): ?>
+        <div class="alert error"><?= htmlspecialchars($error) ?></div>
+      <?php elseif ($success): ?>
+        <div class="alert success"><?= htmlspecialchars($success) ?></div>
+      <?php endif; ?>
+      <form method="post" novalidate>
+        <label for="name">
+          Full Name
+          <input type="text" id="name" name="name" placeholder="Jane Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
+        </label>
+        <label for="phone">
+          Phone Number
+          <input type="tel" id="phone" name="phone" placeholder="Optional" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
+        </label>
+        <label for="email">
+          Email
+          <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
+        </label>
+        <label for="password">
+          Password
+          <input type="password" id="password" name="password" placeholder="Create a strong password" required>
+        </label>
+        <label for="confirm_password">
+          Confirm Password
+          <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
+        </label>
+        <div class="actions">
+          <button class="secondary" type="submit">Register</button>
+          <a href="auth.php"><button class="primary" type="button">Back to login</button></a>
+        </div>
+      </form>
+      <p class="helper-text">Passwords require 8+ characters with uppercase, lowercase, number, and symbol.</p>
+    </article>
+  </section>
 </body>
-</html>
+</html>
