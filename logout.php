diff --git a/logout.php b/logout.php
index 8e86624b8363e7375a6e304e1789487ecad2a9d4..c9d1dd63f63234a1651c138b9b45b5db04e59ca6 100644
--- a/logout.php
+++ b/logout.php
@@ -1,19 +1,13 @@
 <?php
 session_start();
 
-if (isset($_SESSION['username'])) {
-    echo '<form action="logout.php" method="get">
-            <input type="hidden" name="confirm" value="true">
-            <input type="submit" value="Log out">
-          </form>';
-} else {
-    header('Location: register.php');
-    exit;
-}
-
-if (isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
+if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
+    session_unset();
     session_destroy();
-    header('Location: register.php');
+    header('Location: auth.php');
     exit;
 }
-?>
+
+header('Location: hello.php');
+exit;
+?>
