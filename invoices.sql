CREATE TABLE handwritten_invoices (
    id INT AUTO_INCREMENT,
    invoice_number VARCHAR(255),
    date DATE,
    amount DECIMAL(10, 2),
    vendor_name VARCHAR(255),
    items TEXT,
    PRIMARY KEY (id)
);