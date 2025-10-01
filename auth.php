diff --git a/auth.php b/auth.php
index 510b8cb56351457772a61b48aa81c789d4d7902b..a0648892bcc8fc74c641a59f49891deed3f141e2 100644
--- a/auth.php
+++ b/auth.php
@@ -1,132 +1,83 @@
 <?php
-include 'config.php';
+session_start();
+require 'config.php';
 
-if ($_SERVER["REQUEST_METHOD"] == "POST") {
-    $username = $_POST['username'];
-    $password = $_POST['password'];
+$error = '';
 
-    $sql = "SELECT * FROM users WHERE username=?"; 
-    $stmt = $conn->prepare($sql);
-    $stmt->bind_param("s", $username); 
-    $stmt->execute();
-    $result = $stmt->get_result();
+if ($_SERVER['REQUEST_METHOD'] === 'POST') {
+    $email = trim($_POST['email'] ?? '');
+    $password = $_POST['password'] ?? '';
 
-    if ($result->num_rows > 0) {
-        $user = $result->fetch_assoc();
-        if (password_verify($password, $user['password'])) {
-          $_SESSION['username'] = $username;
-          header('Location: hello.php');
-          exit;
-        } else {
-          $error="Invalid username or password";
+    if ($email === '' || $password === '') {
+        $error = 'Please provide both email and password.';
+    } else {
+        $sql = "SELECT a.AccountID, a.UserID, a.Password, u.Name, u.Email, u.PhoneNumber
+                FROM Account a
+                INNER JOIN User u ON u.UserID = a.UserID
+                WHERE u.Email = ?";
+        $stmt = $conn->prepare($sql);
+
+        if ($stmt) {
+            $stmt->bind_param('s', $email);
+            $stmt->execute();
+            $result = $stmt->get_result();
+            $account = $result->fetch_assoc();
+            $stmt->close();
+
+            if ($account && password_verify($password, $account['Password'])) {
+                $_SESSION['user_id'] = $account['UserID'];
+                $_SESSION['account_id'] = $account['AccountID'];
+                $_SESSION['name'] = $account['Name'];
+                $_SESSION['email'] = $account['Email'];
+                $_SESSION['phone'] = $account['PhoneNumber'];
+
+                header('Location: hello.php');
+                exit;
+            }
         }
-    } 
 
-    $stmt->close();
-    $conn->close();
+        if ($error === '') {
+            $error = 'Invalid login credentials. Please try again.';
+        }
+    }
 }
 ?>
 <!DOCTYPE html>
 <html lang="en">
 <head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
-  <title>Authentication Page</title>
-  <style>
-  
-    body {
-      font-family: 'Times New Roman', sans-serif;
-      background-image: linear-gradient(to right, #4776E6, #8E54E9);
-      display: flex;
-      justify-content: center;
-      align-items: center;
-      height: 100vh;
-      margin: 0;
-    }
-
-   .auth-container {
-      background-color: #90f9ed;
-      border-radius: 10px;
-      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
-      padding: 40px;
-      width: 400px;
-    }
-
-    h2 {
-      text-align: center;
-      margin-bottom: 30px;
-      color: #1447bd;
-    }
-   .form-group {
-      margin-bottom: 20px;
-    }
-    label {
-      display: block;
-      font-size: 14px;
-      color: #333;
-      margin-bottom: 5px;
-    }
-
-    input[type="text"],
-    input[type="password"] {
-      width: 100%;
-      padding: 12px 20px;
-      border: 1px solid #a075c6;
-      border-radius: 4px;
-      box-sizing: border-box;
-      font-size: 16px;
-    }
-
-    button {
-    
-    background-color: #4776E6;
-    color: #fff;
-    border: none;
-    border-radius: 2px;
-    padding:5px 100px;
-    font-size: 16px;
-    cursor: pointer;
-    width: 50%;
-    display: flex;
-  justify-content: center;
-  align-items: center;
-  padding: 10px; 
- 
-  }
-
-    button:hover {
-      background-color: #875cba;
-    }
-    .error{
-      border: 1px solid;
-      margin: 10px 0px;
-      padding: 15px 10px 15px 50px;
-      color: #d8000c;
-      background-color: #ffbaba;
-    }
-  </style>
+  <title>Servesoft Login</title>
+  <link rel="stylesheet" href="styles.css">
 </head>
 <body>
-  <div class="auth-container">
-    <h2>Authentication</h2>
-    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post"> 
-            <div class="form-group">
-        <label for="username">Username:</label>
-        <input type="text" id="username" name="username" placeholder="Enter your username" required>
-      </div>
-      <div class="form-group">
-        <label for="password">Password:</label>
-        <input type="password" id="password" name="password" placeholder="Enter your password" required>
-      </div>
-      <button type="submit">Login</button>
-    </form>
-    <?php if(isset($error)):?>
-   <div class="error">
-    <?php
-      echo @$error;
-    ?>
-   </div >
-   <?php endif; ?>
-  </div>
+  <section class="auth-shell">
+    <article class="card">
+      <header>
+        <h1>Welcome Back</h1>
+        <p>Sign in with your Servesoft email to continue.</p>
+      </header>
+      <?php if ($error): ?>
+        <div class="alert error"><?= htmlspecialchars($error) ?></div>
+      <?php endif; ?>
+      <form method="post" novalidate>
+        <label for="email">
+          Email
+          <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
+        </label>
+        <label for="password">
+          Password
+          <input type="password" id="password" name="password" placeholder="••••••••" required>
+        </label>
+        <div class="actions">
+          <button class="primary" type="submit">Login</button>
+          <a href="register.php"><button class="secondary" type="button">Need an account?</button></a>
+        </div>
+      </form>
+      <p class="helper-text">
+        Having trouble? Contact your Servesoft administrator for assistance.
+      </p>
+    </article>
+  </section>
 </body>
-</html>
+</html>
