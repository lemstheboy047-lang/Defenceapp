diff --git a/config.php b/config.php
index 27abed4daffb375b69637c78e305fb5060da6141..c8184bb16b0bdfd52b7f6f4fd21772014c18c859 100644
--- a/config.php
+++ b/config.php
@@ -1,14 +1,10 @@
 <?php
+mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
 $servername = "localhost";
 $username = "root";
 $password = "";
-$database = "user_auth";
-
+$database = "SERVESOFT";
 
 $conn = new mysqli($servername, $username, $password, $database);
-
-if ($conn->connect_error) {
-    die("Connection failed: " . $conn->connect_error);
-}
-
-?>
+$conn->set_charset('utf8mb4');
+?>
