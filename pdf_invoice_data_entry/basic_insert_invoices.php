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
        
        // Here you would parse the content to extract specific fields
        // Example: Extracting dummy data for illustration
        $invoiceNumber = '12345'; // Extract invoice number from content
        $date = '2023-01-01'; // Extract date from content
        $amount = 100.50; // Extract amount from content
        $vendorName = 'Example Vendor'; // Extract vendor name from content
        $items = 'Item1, Item2'; // Extract items from content

        // Insert into database
        $stmt = $link->prepare("INSERT INTO handwritten_invoices (invoice_number, date, amount, vendor_name, items) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $invoiceNumber, $date, $amount, $vendorName, $items);
        $stmt->execute();
    }
}

echo "Data inserted successfully";

$link->close();
?>