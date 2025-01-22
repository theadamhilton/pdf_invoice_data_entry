<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// Create connection
$link = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}

// Directory containing the extracted text files
$txtDir = './output/';

$files = scandir($txtDir);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
        $content = file_get_contents($txtDir . $file);
        
        // Parsing the content to extract specific fields
        // This is a basic example. You might need more complex logic based on the invoice format.
        preg_match('/Invoice Number:\s*(\S+)/', $content, $invoiceNumberMatches);
        preg_match('/Date:\s*([\d\/\-]+)/', $content, $dateMatches);
        preg_match('/Amount:\s*\$?([\d,.]+)/', $content, $amountMatches);
        preg_match('/Vendor:\s*(.+)/', $content, $vendorNameMatches);
        preg_match('/Items:\s*(.+)/', $content, $itemsMatches);

        $invoiceNumber = $invoiceNumberMatches[1] ?? 'Unknown';
        $date = $dateMatches[1] ?? 'Unknown';
        $amount = $amountMatches[1] ?? '0.00';
        $vendorName = $vendorNameMatches[1] ?? 'Unknown Vendor';
        $items = $itemsMatches[1] ?? 'No Items Listed';

        // Insert into database
        $stmt = $link->prepare("INSERT INTO handwritten_invoices (invoice_number, date, amount, vendor_name, items) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $invoiceNumber, $date, $amount, $vendorName, $items);
        $stmt->execute();
    }
}

echo "Data inserted successfully";

$link->close();
?>