diff --git a//dev/null b/bootstrap.php
index 0000000000000000000000000000000000000000..484a27b627e392b4b9c24e6c4066942dee28faed 100644
--- a//dev/null
+++ b/bootstrap.php
@@ -0,0 +1,62 @@
+<?php
+require 'config.php';
+
+header('Content-Type: application/json');
+
+$response = [
+    'restaurants' => [],
+    'tables' => [],
+    'menuItems' => []
+];
+
+try {
+    $restaurantQuery = $conn->query('SELECT RestaurantID, RestaurantName, Status, Location, PhoneNumber, Address FROM Restaurant');
+    while ($row = $restaurantQuery->fetch_assoc()) {
+        $response['restaurants'][] = [
+            'id' => 'r' . $row['RestaurantID'],
+            'name' => $row['RestaurantName'],
+            'status' => $row['Status'] ?? 'ACTIVE',
+            'location' => $row['Location'] ?? '',
+            'phone' => $row['PhoneNumber'] ?? '',
+            'address' => $row['Address'] ?? '',
+            'hours' => null,
+            'serviceRules' => null
+        ];
+    }
+    $restaurantQuery->close();
+
+    $tableQuery = $conn->query('SELECT TableID, RestaurantID, TableNumber, Capacity, Status FROM RestaurantTable');
+    while ($row = $tableQuery->fetch_assoc()) {
+        $response['tables'][] = [
+            'id' => 't' . $row['TableID'],
+            'restaurantId' => 'r' . $row['RestaurantID'],
+            'label' => 'Table ' . $row['TableNumber'],
+            'capacity' => (int) $row['Capacity'],
+            'state' => $row['Status'] ?? 'FREE'
+        ];
+    }
+    $tableQuery->close();
+
+    $menuQuery = $conn->query('SELECT MenuID, RestaurantID, ItemName, ItemDescription, Category, Availability, Price FROM MenuItem');
+    while ($row = $menuQuery->fetch_assoc()) {
+        $response['menuItems'][] = [
+            'id' => 'm' . $row['MenuID'],
+            'restaurantId' => 'r' . $row['RestaurantID'],
+            'name' => $row['ItemName'],
+            'description' => $row['ItemDescription'] ?? '',
+            'category' => $row['Category'] ?? 'General',
+            'available' => (bool) $row['Availability'],
+            'price' => (float) $row['Price'],
+            'modifiers' => []
+        ];
+    }
+    $menuQuery->close();
+
+    echo json_encode($response);
+} catch (Throwable $error) {
+    http_response_code(500);
+    echo json_encode([
+        'error' => 'Failed to read SERVESOFT data',
+        'details' => $error->getMessage()
+    ]);
+}
