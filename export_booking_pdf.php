<?php
require_once 'config/functions.php';
requireLogin();

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$settings = getSettings($conn);
$siteName = $settings['site_name'] ?? 'Pochie Catering Services';

// Fetch detailed booking data
$stmt = $conn->prepare("SELECT b.*, p.name as package_name, p.base_price, p.description as package_description, p.inclusions,
                        u.first_name, u.last_name, u.email, u.phone 
                        FROM bookings b 
                        LEFT JOIN packages p ON b.package_id = p.id 
                        LEFT JOIN users u ON b.customer_id = u.id 
                        WHERE b.id = ? AND b.customer_id = ?");
$stmt->bind_param("ii", $bookingId, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die("Booking not found or access denied.");
}

// Fetch menu items
$menuItems = $conn->query("SELECT bm.*, m.name FROM booking_menu_items bm 
                           LEFT JOIN menu_items m ON bm.menu_item_id = m.id 
                           WHERE bm.booking_id = $bookingId");
$menuData = [];
while($item = $menuItems->fetch_assoc()) {
    $menuData[] = [
        $item['name'],
        formatPrice($item['price']),
        $item['quantity'],
        formatPrice($item['price'] * $item['quantity'])
    ];
}

// Fetch payments
$payments = $conn->query("SELECT * FROM payments WHERE booking_id = $bookingId ORDER BY created_at DESC");
$paymentData = [];
while($pay = $payments->fetch_assoc()) {
    $paymentData[] = [
        date('M d, Y', strtotime($pay['created_at'])),
        $pay['payment_method'],
        $pay['reference_number'],
        formatPrice($pay['amount']),
        ucfirst($pay['status'])
    ];
}

$packageTotal = ($booking['base_price'] ?? 0) * $booking['number_of_guests'];

// Calculate menu items total
$menuItemsTotal = 0;
foreach ($menuData as $item) {
    // Item format: [name, price, qty, subtotal] - need to parse subtotal
}
$menuTotalResult = $conn->query("SELECT SUM(price * quantity) as total FROM booking_menu_items WHERE booking_id = $bookingId");
$menuItemsTotal = (float)($menuTotalResult->fetch_assoc()['total'] ?? 0);

// Calculate discount (if any)
$subtotal = $packageTotal + $menuItemsTotal;
$discountAmount = $subtotal - $booking['total_amount'];
$hasDiscount = $discountAmount > 1;
$discountPercent = $hasDiscount ? round(($discountAmount / $subtotal) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Downloading Booking PDF...</title>
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
        <h4>Preparing Your Booking PDF</h4>
        <p class="text-muted">Booking #<?= $booking['booking_number'] ?></p>
    </div>

    <script>
        const { jsPDF } = window.jspdf;
        
        function generatePDF() {
            const doc = new jsPDF('p', 'mm', 'a4');
            const pageWidth = doc.internal.pageSize.getWidth();
            
            // Header
            doc.setFontSize(22);
            doc.setTextColor(220, 53, 69);
            doc.text('<?= addslashes($siteName) ?>', pageWidth / 2, 20, { align: 'center' });
            
            doc.setFontSize(16);
            doc.setTextColor(0);
            doc.text('BOOKING CONFIRMATION', pageWidth / 2, 30, { align: 'center' });
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Booking No: <?= $booking['booking_number'] ?>', pageWidth / 2, 36, { align: 'center' });
            doc.text('Generated on: ' + new Date().toLocaleString(), pageWidth / 2, 42, { align: 'center' });

            // Event Details
            doc.setFontSize(14);
            doc.setTextColor(0);
            doc.text('Event Information', 14, 55);
            
            const eventInfo = [
                ['Customer Name', '<?= addslashes($booking['first_name'] . ' ' . $booking['last_name']) ?>'],
                ['Event Type', '<?= addslashes($booking['event_type']) ?>'],
                ['Event Date', '<?= formatDate($booking['event_date']) ?>'],
                ['Event Time', '<?= date('g:i A', strtotime($booking['event_time'])) ?>'],
                ['No. of Guests', '<?= $booking['number_of_guests'] ?> pax'],
                ['Venue Address', '<?= addslashes(str_replace(["\r", "\n"], ' ', $booking['venue_address'])) ?>'],
                ['Booking Status', '<?= ucfirst($booking['status']) ?>'],
                ['Payment Status', '<?= ucfirst($booking['payment_status']) ?>']
            ];

            doc.autoTable({
                startY: 60,
                body: eventInfo,
                theme: 'plain',
                styles: { fontSize: 10, cellPadding: 2 },
                columnStyles: { 0: { fontStyle: 'bold', width: 40 } }
            });

            // Package Details
            doc.text('Selected Package', 14, doc.lastAutoTable.finalY + 15);
            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 20,
                head: [['Package Name', 'Price/Pax', 'Guests', 'Subtotal']],
                body: [[
                    '<?= addslashes($booking['package_name']) ?>',
                    '<?= formatPrice($booking['base_price']) ?>',
                    '<?= $booking['number_of_guests'] ?>',
                    '<?= formatPrice($packageTotal) ?>'
                ]],
                theme: 'grid',
                headStyles: { fillColor: [220, 53, 69] }
            });

            // Menu Items (if any)
            <?php if (!empty($menuData)): ?>
            doc.text('Additional Menu Items', 14, doc.lastAutoTable.finalY + 15);
            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 20,
                head: [['Item Name', 'Price/Tray', 'Qty', 'Subtotal']],
                body: <?= json_encode($menuData) ?>,
                theme: 'striped',
                headStyles: { fillColor: [40, 167, 69] }
            });
            <?php endif; ?>

            // Financial Summary
            doc.setFontSize(14);
            doc.text('Payment Summary', 14, doc.lastAutoTable.finalY + 15);
            
            const summary = [
                ['Package Subtotal', '<?= formatPrice($packageTotal) ?>'],
                <?php if ($menuItemsTotal > 0): ?>
                ['Menu Items', '<?= formatPrice($menuItemsTotal) ?>'],
                <?php endif; ?>
                <?php if ($hasDiscount): ?>
                ['Promo Discount (<?= $discountPercent ?>%)', '-<?= formatPrice($discountAmount) ?>'],
                <?php endif; ?>
                ['Total Amount Due', '<?= formatPrice($booking['total_amount']) ?>']
            ];

            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 20,
                body: summary,
                theme: 'grid',
                styles: { fontSize: 11 },
                columnStyles: { 0: { fontStyle: 'bold' } },
                headStyles: { fillColor: [52, 58, 64] }
            });

            // Payments Table (if any)
            <?php if (!empty($paymentData)): ?>
            doc.setFontSize(12);
            doc.text('Payment History', 14, doc.lastAutoTable.finalY + 15);
            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 20,
                head: [['Date', 'Method', 'Ref #', 'Amount', 'Status']],
                body: <?= json_encode($paymentData) ?>,
                theme: 'grid',
                headStyles: { fillColor: [0, 123, 255] }
            });
            <?php endif; ?>

            // Footer
            const totalPages = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPages; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(150);
                doc.text(`Thank you for choosing <?= addslashes($siteName) ?>! | Page ${i} of ${totalPages}`, pageWidth / 2, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
            }

            // Save PDF
            const fileName = `Booking_<?= $booking['booking_number'] ?>.pdf`;
            doc.save(fileName);
            
            // Go back to details page
            setTimeout(() => {
                window.location.href = 'booking-details.php?id=<?= $bookingId ?>';
            }, 2000);
        }

        window.onload = generatePDF;
    </script>
</body>
</html>