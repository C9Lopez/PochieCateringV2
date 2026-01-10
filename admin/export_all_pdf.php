<?php
require_once '../config/functions.php';
requireRole(['admin', 'super_admin']);

$settings = getSettings($conn);
$siteName = $settings['site_name'] ?? 'Filipino Catering';

// Fetch all data for backup/export
// 1. Stats
$stats = [
    'Total Bookings' => $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0],
    'Completed Events' => $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetch_row()[0],
    'Total Revenue' => formatPrice($conn->query("SELECT SUM(total_amount) FROM bookings WHERE status = 'completed' OR payment_status = 'paid'")->fetch_row()[0] ?? 0),
    'Total Customers' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetch_row()[0],
    'Total Menu Items' => $conn->query("SELECT COUNT(*) FROM menu_items")->fetch_row()[0],
    'Total Packages' => $conn->query("SELECT COUNT(*) FROM packages")->fetch_row()[0]
];

// 2. Bookings
$bookings = $conn->query("SELECT b.booking_number, u.first_name, u.last_name, b.event_type, b.event_date, b.number_of_guests, b.total_amount, b.status, b.payment_status 
                          FROM bookings b 
                          LEFT JOIN users u ON b.customer_id = u.id 
                          ORDER BY b.created_at DESC");
$bookingData = [];
while($row = $bookings->fetch_assoc()) {
    $bookingData[] = [
        $row['booking_number'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['event_type'],
        date('M d, Y', strtotime($row['event_date'])),
        $row['number_of_guests'],
        number_format($row['total_amount'], 2),
        ucfirst($row['status']),
        ucfirst($row['payment_status'])
    ];
}

// 3. Customers
$customers = $conn->query("SELECT first_name, last_name, email, phone, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC");
$customerData = [];
while($row = $customers->fetch_assoc()) {
    $customerData[] = [
        $row['first_name'] . ' ' . $row['last_name'],
        $row['email'],
        $row['phone'],
        date('M d, Y', strtotime($row['created_at']))
    ];
}

// 4. Menu Items
$menuItems = $conn->query("SELECT name, category, price, status FROM menu_items ORDER BY category, name ASC");
$menuData = [];
while($row = $menuItems->fetch_assoc()) {
    $menuData[] = [
        $row['name'],
        ucfirst($row['category']),
        number_format($row['price'], 2),
        ucfirst($row['status'])
    ];
}

// 5. Packages
$packages = $conn->query("SELECT name, min_guests, price_per_pax, status FROM packages ORDER BY name ASC");
$packageData = [];
while($row = $packages->fetch_assoc()) {
    $packageData[] = [
        $row['name'],
        $row['min_guests'],
        number_format($row['price_per_pax'], 2),
        ucfirst($row['status'])
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exporting Data...</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f8f9fa; }
        .loader-container { text-align: center; }
        .loader { border: 5px solid #f3f3f3; border-top: 5px solid #dc3545; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <h4>Generating System Data Backup (PDF)</h4>
        <p class="text-muted">Please wait, this may take a few seconds...</p>
    </div>

    <script>
        const { jsPDF } = window.jspdf;
        
        function generatePDF() {
            const doc = new jsPDF('l', 'mm', 'a4'); // Landscape for better table fit
            const pageWidth = doc.internal.pageSize.getWidth();
            const date = new Date().toLocaleString();
            
            // Header
            doc.setFontSize(22);
            doc.setTextColor(220, 53, 69); // Red color
            doc.text('<?= addslashes($siteName) ?>', pageWidth / 2, 20, { align: 'center' });
            
            doc.setFontSize(16);
            doc.setTextColor(100);
            doc.text('Full System Data Backup', pageWidth / 2, 30, { align: 'center' });
            
            doc.setFontSize(10);
            doc.text(`Generated on: ${date}`, pageWidth / 2, 38, { align: 'center' });
            
            // 1. Statistics Summary
            doc.setFontSize(14);
            doc.setTextColor(0);
            doc.text('1. System Summary', 14, 50);
            
            const stats = [
                <?php foreach($stats as $key => $val): ?>
                ['<?= addslashes($key) ?>', '<?= addslashes($val) ?>'],
                <?php endforeach; ?>
            ];
            
            doc.autoTable({
                startY: 55,
                head: [['Metric', 'Value']],
                body: stats,
                theme: 'striped',
                headStyles: { fillColor: [220, 53, 69] }
            });

            // 2. Bookings
            doc.addPage();
            doc.text('2. All Bookings', 14, 20);
            const bookings = <?= json_encode($bookingData) ?>;
            doc.autoTable({
                startY: 25,
                head: [['Booking #', 'Customer', 'Event Type', 'Date', 'Guests', 'Total (PHP)', 'Status', 'Payment']],
                body: bookings,
                theme: 'grid',
                headStyles: { fillColor: [52, 58, 64] },
                styles: { fontSize: 8 }
            });

            // 3. Customers
            doc.addPage();
            doc.text('3. Registered Customers', 14, 20);
            const customers = <?= json_encode($customerData) ?>;
            doc.autoTable({
                startY: 25,
                head: [['Name', 'Email', 'Phone', 'Registered Date']],
                body: customers,
                theme: 'grid',
                headStyles: { fillColor: [13, 110, 253] }
            });

            // 4. Menu Items & Packages
            doc.addPage();
            doc.text('4. Menu Items', 14, 20);
            const menu = <?= json_encode($menuData) ?>;
            doc.autoTable({
                startY: 25,
                head: [['Name', 'Category', 'Price (PHP)', 'Status']],
                body: menu,
                theme: 'grid',
                headStyles: { fillColor: [25, 135, 84] }
            });

            doc.text('5. Service Packages', 14, doc.lastAutoTable.finalY + 15);
            const packages = <?= json_encode($packageData) ?>;
            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 20,
                head: [['Package Name', 'Min Guests', 'Price/Pax (PHP)', 'Status']],
                body: packages,
                theme: 'grid',
                headStyles: { fillColor: [255, 193, 7] },
                alternateRowStyles: { fillColor: [255, 252, 240] }
            });

            // Footer for all pages
            const totalPages = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPages; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(150);
                doc.text(`Page ${i} of ${totalPages} - Data Backup Confidential - <?= addslashes($siteName) ?>`, pageWidth / 2, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
            }

            // Save PDF
            const fileName = `Backup_${new Date().toISOString().slice(0,10)}_${new Date().getTime()}.pdf`;
            doc.save(fileName);
            
            // Redirect back after a short delay
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
        }

        window.onload = generatePDF;
    </script>
</body>
</html>