diff --git a/views.php b/views.php
index 978e9a72a62935023560935d652eb63ce5fd7c21..9feecf2d6ef8af80a25273bdb034dd49a6b47d33 100644
--- a/views.php
+++ b/views.php
@@ -1,103 +1,92 @@
+<?php
+require 'config.php';
+
+$tables = [];
+$result = $conn->query('SHOW TABLES');
+while ($row = $result->fetch_array(MYSQLI_NUM)) {
+    $tables[] = $row[0];
+}
+$result->close();
+?>
 <!DOCTYPE html>
-<html>
+<html lang="en">
 <head>
-	<title>Database Tables View</title>
-	<style>
-		table {
-			border-collapse: collapse;
-			width: 100%;
-		}
-		th, td {
-			border: 1px solid #ddd;
-			padding: 8px;
-			text-align: left;
-		}
-		th {
-			background-color: #f0f0f0;
-		}
-	</style>
+  <meta charset="UTF-8">
+  <meta name="viewport" content="width=device-width, initial-scale=1.0">
+  <title>SERVESOFT Schema Overview</title>
+  <link rel="stylesheet" href="styles.css">
 </head>
 <body>
-	<h1>Database Tables View</h1>
-	<?php
-		
-		$servername = "localhost";
-		$username = "root";
-		$password = "";
-		$dbname ="user_auth";
-
-		$conn = new mysqli($servername, $username, $password, $dbname);
-
-		
-		if ($conn->connect_error) {
-			die("Connection failed: ". $conn->connect_error);
-		}
-
-		
-		$sql = "SHOW TABLES";
-		$result = $conn->query($sql);
-
-		if ($result->num_rows > 0) {
-			while($row = $result->fetch_assoc()) {
-				$table_name = $row["Tables_in_". $dbname];
-				echo "<h2>$table_name</h2>";
-				echo "<table>";
-				echo "<tr><th>Column Name</th><th>Data Type</th></tr>";
-
-				
-				$sql = "DESCRIBE $table_name";
-				$result2 = $conn->query($sql);
-
-				if ($result2->num_rows > 0) {
-					while($row2 = $result2->fetch_assoc()) {
-						echo "<tr>";
-						echo "<td>". $row2["Field"]. "</td>";
-						echo "<td>". $row2["Type"]. "</td>";
-						echo "</tr>";
-					}
-				}
-
-				echo "</table>";
-
-				// Display table content
-				displayTableContent($conn, $table_name);
-
-				echo "<br>";
-			}
-		} else {
-			echo "No tables found";
-		}
-
-		$conn->close();
-	?>
-
-	<?php
-		function displayTableContent($conn, $table_name) {
-			echo "<h3>Table Content:</h3>";
-			echo "<table>";
-			echo "<tr>";
-
-			$sql = "SHOW COLUMNS FROM $table_name";
-			$result = $conn->query($sql);
-			while($row = $result->fetch_assoc()) {
-				echo "<th>". $row["Field"]. "</th>";
-			}
-
-			echo "</tr>";
-
-			
-			$sql = "SELECT * FROM $table_name";
-			$result = $conn->query($sql);
-			while($row = $result->fetch_assoc()) {
-				echo "<tr>";
-				foreach($row as $value) {
-					echo "<td>". $value. "</td>";
-				}
-				echo "</tr>";
-			}
-
-			echo "</table>";
-		}
-	?>
+  <section class="auth-shell">
+    <article class="card">
+      <header>
+        <h1>SERVESOFT Database</h1>
+        <p>Schema and snapshot of stored data.</p>
+      </header>
+      <?php foreach ($tables as $table): ?>
+        <section class="summary">
+          <h2><?= htmlspecialchars($table) ?></h2>
+          <div class="table-wrapper">
+            <table>
+              <thead>
+                <tr>
+                  <th>Column</th>
+                  <th>Type</th>
+                </tr>
+              </thead>
+              <tbody>
+                <?php
+                  $describe = $conn->query("DESCRIBE `$table`");
+                  while ($column = $describe->fetch_assoc()):
+                ?>
+                  <tr>
+                    <td><?= htmlspecialchars($column['Field']) ?></td>
+                    <td><?= htmlspecialchars($column['Type']) ?></td>
+                  </tr>
+                <?php endwhile; ?>
+              </tbody>
+            </table>
+          </div>
+          <div class="table-wrapper">
+            <table>
+              <thead>
+                <tr>
+                  <?php
+                    $columns = [];
+                    $fields = $conn->query("SHOW COLUMNS FROM `$table`");
+                    while ($field = $fields->fetch_assoc()) {
+                        $columns[] = $field['Field'];
+                        echo '<th>' . htmlspecialchars($field['Field']) . '</th>';
+                    }
+                  ?>
+                </tr>
+              </thead>
+              <tbody>
+                <?php
+                  $rows = $conn->query("SELECT * FROM `$table`");
+                  if ($rows->num_rows === 0):
+                ?>
+                  <tr>
+                    <td colspan="<?= count($columns) ?>">No data recorded yet.</td>
+                  </tr>
+                <?php else:
+                    while ($data = $rows->fetch_assoc()): ?>
+                  <tr>
+                    <?php foreach ($columns as $columnName): ?>
+                      <td><?= htmlspecialchars((string)($data[$columnName] ?? '')) ?></td>
+                    <?php endforeach; ?>
+                  </tr>
+                <?php endwhile;
+                  endif; ?>
+              </tbody>
+            </table>
+          </div>
+        </section>
+      <?php endforeach; ?>
+      <footer>
+        Connected to SERVESOFT database using mysqli.
+      </footer>
+    </article>
+  </section>
 </body>
-</html>
+</html>
