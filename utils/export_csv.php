<?php
require_once '../includes/auth.php';
checkAuth();
require_once '../includes/db.php';

$type = $_GET['type'] ?? 'products';
$filename = "export_" . $type . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($type == 'products') {
    fputcsv($output, ['ID', 'Name', 'Category', 'Cost Price', 'Selling Price', 'Quantity', 'Supplier']);
    $stmt = $pdo->query("SELECT p.*, s.name as supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['id'], $row['name'], $row['category'], $row['cost_price'], $row['selling_price'], $row['quantity'], $row['supplier_name']]);
    }
} elseif ($type == 'purchases') {
    fputcsv($output, ['ID', 'Date', 'Product', 'Supplier', 'Quantity', 'Price', 'Total']);
    $stmt = $pdo->query("SELECT pur.*, p.name as product_name, s.name as supplier_name FROM purchases pur JOIN products p ON pur.product_id = p.id JOIN suppliers s ON pur.supplier_id = s.id");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['id'], $row['purchase_date'], $row['product_name'], $row['supplier_name'], $row['quantity'], $row['purchase_price'], $row['total_amount']]);
    }
} elseif ($type == 'sales') {
    fputcsv($output, ['ID', 'Date', 'Product', 'Quantity', 'Price', 'Total']);
    $stmt = $pdo->query("SELECT s.*, p.name as product_name FROM sales s JOIN products p ON s.product_id = p.id");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['id'], $row['sale_date'], $row['product_name'], $row['quantity'], $row['selling_price'], $row['total_amount']]);
    }
}

fclose($output);
exit();
?>
