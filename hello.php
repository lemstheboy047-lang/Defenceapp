diff --git a/hello.php b/hello.php
index fc8027fa65c3048fbc679ccf906dd4e7b21eb316..03c845241ccceaaf261928103e6af5818dd253c3 100644
--- a/hello.php
+++ b/hello.php
@@ -1,46 +1,87 @@
-<!DOCTYPE html>
-<html lang="en">
-<head>
-    <meta charset="UTF-8">
-    <meta name="viewport" content="width=device-width, initial-scale=1.0">
-    <title>WELCOME Page</title>
-    <style>
+<?php
+session_start();
+require 'config.php';
 
-    h1 {
-      font-family: 'Times New Roman', sans-serif;
-      background-image: linear-gradient(to right, #4776E6, #8E54E9);
-      display: flex;
-      justify-content: center;
-      align-items: center;
-      height: 100vh;
-      margin: 0;
-    }
-    button{
-        justify-content: center;
-      align-items: center;
-    }
-    
-#top-right-button {
-  position: absolute;
-  top: 0;
-  right: 0;
-  margin: 10px;
-  background-color: #4CAF50;
-  color: #ffffff;
-  border: none;
-  padding: 10px 20px;
-  font-size: 16px;
-  cursor: pointer;
+if (!isset($_SESSION['user_id'])) {
+    header('Location: auth.php');
+    exit;
 }
 
-#top-right-button:hover {
-  background-color: #3e8e41;
+$userId = $_SESSION['user_id'];
+
+$summary = [
+    'reservations' => 0,
+    'orders' => 0,
+    'deliveries' => 0,
+];
+
+try {
+    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM Reservation WHERE CustomerID = (SELECT CustomerID FROM Customer WHERE UserID = ?)');
+    $stmt->bind_param('i', $userId);
+    $stmt->execute();
+    $result = $stmt->get_result()->fetch_assoc();
+    $summary['reservations'] = (int)($result['total'] ?? 0);
+    $stmt->close();
+
+    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM CustomerOrder WHERE CustomerID = (SELECT CustomerID FROM Customer WHERE UserID = ?)');
+    $stmt->bind_param('i', $userId);
+    $stmt->execute();
+    $result = $stmt->get_result()->fetch_assoc();
+    $summary['orders'] = (int)($result['total'] ?? 0);
+    $stmt->close();
+
+    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM Delivery d INNER JOIN CustomerOrder o ON d.OrderID = o.OrderID WHERE o.CustomerID = (SELECT CustomerID FROM Customer WHERE UserID = ?)');
+    $stmt->bind_param('i', $userId);
+    $stmt->execute();
+    $result = $stmt->get_result()->fetch_assoc();
+    $summary['deliveries'] = (int)($result['total'] ?? 0);
+    $stmt->close();
+} catch (mysqli_sql_exception $e) {
+    // Keep dashboard available even if optional tables are empty/missing
 }
-</style>
+?>
+<!DOCTYPE html>
+<html lang="en">
+<head>
+  <meta charset="UTF-8">
+  <meta name="viewport" content="width=device-width, initial-scale=1.0">
+  <title>Servesoft Dashboard</title>
+  <link rel="stylesheet" href="styles.css">
 </head>
 <body>
-    <h1>WELCOME</h1>
-    <button id="top-right-button"><a href="logout.php">Logout</a></button>
-    
+  <section class="dashboard-shell">
+    <article class="card">
+      <header>
+        <h1>Hi <?= htmlspecialchars($_SESSION['name'] ?? 'Servesoft User') ?>!</h1>
+        <p>Here is a quick snapshot of your Servesoft activity.</p>
+      </header>
+      <section class="summary">
+        <div class="summary-row">
+          <div class="summary-pill">
+            <h3>Reservations</h3>
+            <p><?= $summary['reservations'] ?></p>
+          </div>
+          <div class="summary-pill">
+            <h3>Orders</h3>
+            <p><?= $summary['orders'] ?></p>
+          </div>
+          <div class="summary-pill">
+            <h3>Deliveries</h3>
+            <p><?= $summary['deliveries'] ?></p>
+          </div>
+        </div>
+      </section>
+      <section class="summary">
+        <h2>Your Profile</h2>
+        <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email'] ?? '') ?></p>
+        <p><strong>Phone:</strong> <?= htmlspecialchars($_SESSION['phone'] ?? 'Not provided') ?></p>
+      </section>
+      <div class="actions">
+        <form action="logout.php" method="post">
+          <button class="secondary" type="submit" name="logout" value="1">Logout</button>
+        </form>
+      </div>
+    </article>
+  </section>
 </body>
-</html>
+</html>
