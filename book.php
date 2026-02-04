<?php
$pageTitle = "Book Now";
require_once 'includes/header.php';
requireLogin();

$packages = $conn->query("SELECT * FROM packages WHERE is_active = 1");
$categories = $conn->query("SELECT * FROM menu_categories WHERE is_active = 1");
$menuItems = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id = c.id WHERE m.is_available = 1 ORDER BY c.name, m.name");

$selectedPackageId = isset($_GET['package']) ? (int)$_GET['package'] : null;
    $selectedPackage = null;
    if ($selectedPackageId) {
        $pkgStmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
        $pkgStmt->bind_param("i", $selectedPackageId);
        $pkgStmt->execute();
        $selectedPackage = $pkgStmt->get_result()->fetch_assoc();
    }
    
    $claimedPromo = $_SESSION['claimed_promo'] ?? null;

// Get occupied dates (bookings with reserved/active status) - case insensitive
$occupiedDates = [];
$occupiedQuery = $conn->query("SELECT DISTINCT DATE_FORMAT(event_date, '%Y-%m-%d') as event_date FROM bookings WHERE LOWER(status) IN ('confirmed', 'approved', 'paid', 'preparing', 'ongoing', 'reserved') AND event_date >= CURDATE()");
while ($row = $occupiedQuery->fetch_assoc()) {
    $occupiedDates[] = $row['event_date'];
}
$occupiedDatesJson = json_encode($occupiedDates);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $packageId = (int)$_POST['package_id'];
    $eventType = $conn->real_escape_string($_POST['event_type']);
    $eventDate = $conn->real_escape_string($_POST['event_date']);
    $eventTime = $conn->real_escape_string($_POST['event_time']);
    $venueAddress = $conn->real_escape_string($_POST['venue_address']);
    $numberOfGuests = (int)$_POST['number_of_guests'];
    $specialRequests = $conn->real_escape_string($_POST['special_requests']);
    $selectedItems = $_POST['menu_items'] ?? [];
    $itemQuantities = $_POST['item_quantities'] ?? [];
    
    $minDate = date('Y-m-d', strtotime('+3 days'));
    if ($eventDate < $minDate) {
        $error = 'Event date must be at least 3 days from now';
    } elseif (in_array($eventDate, $occupiedDates)) {
        $error = 'This date is already occupied. Please choose another date.';
    } else {
        $bookingNumber = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -4));
        
        $pkgStmt = $conn->prepare("SELECT base_price FROM packages WHERE id = ?");
        $pkgStmt->bind_param("i", $packageId);
        $pkgStmt->execute();
        $pkg = $pkgStmt->get_result()->fetch_assoc();
        $packageTotal = $pkg['base_price'] * $numberOfGuests;
        
        $menuTotal = 0;
        foreach ($selectedItems as $itemId) {
            $itemId = (int)$itemId;
            $itemStmt = $conn->prepare("SELECT price FROM menu_items WHERE id = ?");
            $itemStmt->bind_param("i", $itemId);
            $itemStmt->execute();
            $item = $itemStmt->get_result()->fetch_assoc();
            $qty = isset($itemQuantities[$itemId]) ? (int)$itemQuantities[$itemId] : 1;
            $menuTotal += $item['price'] * $qty;
        }
        
        $totalAmount = $packageTotal + $menuTotal;
        
        // Apply Promotion Discount
        $discountAmount = 0;
        $claimedPromo = $_SESSION['claimed_promo'] ?? null;
        if ($claimedPromo && $claimedPromo['discount'] > 0) {
            $discountAmount = ($totalAmount * $claimedPromo['discount']) / 100;
            $totalAmount -= $discountAmount;
        }
        
        $stmt = $conn->prepare("INSERT INTO bookings (booking_number, customer_id, package_id, event_type, event_date, event_time, venue_address, number_of_guests, special_requests, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')");
        $stmt->bind_param("siissssisd", $bookingNumber, $_SESSION['user_id'], $packageId, $eventType, $eventDate, $eventTime, $venueAddress, $numberOfGuests, $specialRequests, $totalAmount);
        
        if ($stmt->execute()) {
            // Clear claimed promo after successful booking
            unset($_SESSION['claimed_promo']);
            $bookingId = $conn->insert_id;
            
            foreach ($selectedItems as $itemId) {
                $itemId = (int)$itemId;
                $itemPriceStmt = $conn->prepare("SELECT price FROM menu_items WHERE id = ?");
                $itemPriceStmt->bind_param("i", $itemId);
                $itemPriceStmt->execute();
                $item = $itemPriceStmt->get_result()->fetch_assoc();
                $qty = isset($itemQuantities[$itemId]) ? (int)$itemQuantities[$itemId] : 1;
                $menuStmt = $conn->prepare("INSERT INTO booking_menu_items (booking_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                $menuStmt->bind_param("iiid", $bookingId, $itemId, $qty, $item['price']);
                $menuStmt->execute();
            }
            
            addNotification($conn, $_SESSION['user_id'], 'Booking Submitted', "Your booking #$bookingNumber has been submitted. We'll review it shortly.", 'success', url("booking-details.php?id=$bookingId"));
            
            $admins = $conn->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
            while ($admin = $admins->fetch_assoc()) {
                addNotification($conn, $admin['id'], 'New Booking', "New booking #$bookingNumber received", 'info', adminUrl("booking-details.php?id=$bookingId"));
            }
            
            logActivity($conn, $_SESSION['user_id'], 'booking_created', "Created booking #$bookingNumber");
            
            header("Location: " . url("booking-details.php?id=$bookingId&success=1"));
            exit();
        } else {
            $error = 'Failed to create booking. Please try again.';
        }
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_orange.css">
<style>
.flatpickr-day.occupied {
    background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%) !important;
    color: #dc2626 !important;
    text-decoration: line-through !important;
    cursor: not-allowed !important;
    border-color: #f87171 !important;
    font-weight: 600;
}
.flatpickr-day.occupied:hover {
    background: #fca5a5 !important;
}
.date-legend {
    display: flex;
    gap: 20px;
    margin-top: 10px;
    font-size: 13px;
}
.date-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.legend-box {
    width: 18px;
    height: 18px;
    border-radius: 4px;
}
.legend-available {
    background: #22c55e;
}
    .legend-occupied {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        text-decoration: line-through;
        color: #dc2626;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    /* Time Picker Enhancements */
    .time-picker-wrapper {
        position: relative;
    }
    .time-picker-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: none;
        max-height: 250px;
        overflow-y: auto;
    }
    .time-picker-dropdown.show {
        display: block;
    }
    .time-option {
        padding: 10px 15px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f8f9fa;
        font-weight: 500;
    }
    .time-option:last-child {
        border-bottom: none;
    }
    .time-option:hover {
        background: #fff7ed;
        color: var(--primary);
    }
    .time-option.selected {
        background: var(--primary);
        color: white;
    }
    /* Custom Scrollbar */
    .time-picker-dropdown::-webkit-scrollbar {
        width: 8px;
    }
    .time-picker-dropdown::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 0 8px 8px 0;
    }
    .time-picker-dropdown::-webkit-scrollbar-thumb {
        background: #ea580c;
        border-radius: 10px;
    }
    .time-picker-dropdown::-webkit-scrollbar-thumb:hover {
        background: #c2410c;
    }
    @media (max-width: 768px) {
        .time-picker-dropdown {
            max-height: 200px;
        }
        .time-option {
            padding: 12px 20px;
            font-size: 1.1rem;
        }
    }
    </style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="section-title">Book Your Event</h1>
        <p class="text-muted mt-4">Fill out the form below to start your catering booking</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" id="bookingForm">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        <h5 class="mb-0"><i class="bi bi-1-circle me-2"></i>Event Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Package *</label>
                                <select name="package_id" id="packageSelect" class="form-select" required>
                                    <option value="">Choose a package...</option>
                                    <?php while($pkg = $packages->fetch_assoc()): ?>
                                        <option value="<?= $pkg['id'] ?>" 
                                                data-price="<?= $pkg['base_price'] ?>"
                                                data-min="<?= $pkg['min_pax'] ?>"
                                                data-max="<?= $pkg['max_pax'] ?>"
                                                <?= $selectedPackageId == $pkg['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pkg['name']) ?> - <?= formatPrice($pkg['base_price']) ?>/head
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Type *</label>
                                <select name="event_type" class="form-select" required>
                                    <option value="">Select event type...</option>
                                    <option value="Birthday">Birthday</option>
                                    <option value="Wedding">Wedding</option>
                                    <option value="Debut">Debut</option>
                                    <option value="Baptism">Baptism</option>
                                    <option value="Corporate">Corporate Event</option>
                                    <option value="Fiesta">Fiesta</option>
                                    <option value="Anniversary">Anniversary</option>
                                    <option value="Reunion">Family Reunion</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date * <small class="text-muted">(Click to open calendar)</small></label>
                                <input type="text" name="event_date" id="eventDate" class="form-control" required 
                                       placeholder="Select date..." readonly>
                                <div class="date-legend">
                                    <div class="date-legend-item">
                                        <div class="legend-box legend-available"></div>
                                        <span>Available</span>
                                    </div>
                                    <div class="date-legend-item">
                                        <div class="legend-box legend-occupied">X</div>
                                        <span>Occupied</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Time * <small class="text-muted">(Click to select time)</small></label>
                                <div class="time-picker-wrapper">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-clock" style="color: var(--primary);"></i></span>
                                        <input type="text" name="event_time" id="eventTime" class="form-control border-start-0" required 
                                               placeholder="Select time..." readonly style="background-color: #fff; cursor: pointer;">
                                    </div>
                                    <div class="time-picker-dropdown" id="timeDropdown">
                                        <!-- Time options will be generated by JS -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Number of Guests *</label>
                                <input type="number" name="number_of_guests" id="guestCount" class="form-control" required min="20">
                                <small class="text-muted" id="paxInfo"></small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Venue Address *</label>
                            <textarea name="venue_address" class="form-control" rows="2" required placeholder="Complete address of the event venue"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Special Requests</label>
                            <textarea name="special_requests" class="form-control" rows="3" placeholder="Any dietary restrictions, special arrangements, or additional notes..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        <h5 class="mb-0"><i class="bi bi-2-circle me-2"></i>Select Additional Menu Items</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Add extra dishes to your package (price per tray will be added to total)</p>
                        
                        <?php 
                        $menuItems->data_seek(0);
                        $currentCat = '';
                        while($item = $menuItems->fetch_assoc()): 
                            if ($currentCat !== $item['category_name']):
                                if ($currentCat !== '') echo '</div>';
                                $currentCat = $item['category_name'];
                        ?>
                        <h6 class="mt-4 mb-3 border-bottom pb-2" style="color: var(--primary);">
                            <?= htmlspecialchars($currentCat) ?>
                        </h6>
                        <div class="row">
                        <?php endif; ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="form-check border rounded-3 p-3 h-100 menu-item-card" data-item-id="<?= $item['id'] ?>">
                                    <input class="form-check-input menu-item" type="checkbox" 
                                           name="menu_items[]" value="<?= $item['id'] ?>" 
                                           id="item<?= $item['id'] ?>"
                                           data-price="<?= $item['price'] ?>"
                                           data-name="<?= htmlspecialchars($item['name']) ?>">
                                    <label class="form-check-label w-100" for="item<?= $item['id'] ?>">
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <br><small class="text-muted"><?= formatPrice($item['price']) ?>/tray <span class="text-danger small">(Max 20)</span></small>
                                    </label>
                                    <div class="quantity-control mt-2" style="display: none;">
                                        <div class="input-group input-group-sm">
                                            <button type="button" class="btn btn-outline-secondary qty-minus">-</button>
                                            <input type="number" name="item_quantities[<?= $item['id'] ?>]" 
                                                   class="form-control text-center item-qty" value="1" min="1" max="20"
                                                   data-item-id="<?= $item['id'] ?>">
                                            <button type="button" class="btn btn-outline-secondary qty-plus">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3a5f, #0d1b2a); color: white;">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Quotation Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="summaryContent">
                            <p class="text-muted text-center">Select a package to see summary</p>
                        </div>
                        <div id="menuItemsSummary" style="display: none;">
                            <hr>
                            <h6 class="mb-3"><i class="bi bi-list-check me-2"></i>Selected Menu Items:</h6>
                            <div id="selectedItemsList"></div>
                            <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                                <span>Menu Items Subtotal:</span>
                                <strong id="menuSubtotal">₱0.00</strong>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total Quotation:</strong>
                            <strong class="fs-4" style="color: var(--primary);" id="totalAmount">₱0.00</strong>
                        </div>
                        <small class="text-muted d-block mb-3">*Final price may vary based on negotiations via chat</small>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-send me-2"></i>Submit Booking
                        </button>
                        <p class="text-muted small text-center mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>After submitting, you can negotiate via chat with our team.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('packageSelect');
    const guestCount = document.getElementById('guestCount');
    const paxInfo = document.getElementById('paxInfo');
    const summaryContent = document.getElementById('summaryContent');
    const totalAmount = document.getElementById('totalAmount');
    const menuItemsSummary = document.getElementById('menuItemsSummary');
    const selectedItemsList = document.getElementById('selectedItemsList');
    const menuSubtotal = document.getElementById('menuSubtotal');
    
    // Promotion Data from PHP
    const claimedPromo = <?= json_encode($claimedPromo) ?>;
    
    const occupiedDates = <?= $occupiedDatesJson ?>;
    
    let packageTotal = 0;
    let menuTotal = 0;
    
    // Initialize Flatpickr with occupied dates disabled
    const fp = flatpickr("#eventDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(3),
        disable: [
            function(date) {
                // Format date as YYYY-MM-DD in local timezone
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const dateStr = `${year}-${month}-${day}`;
                return occupiedDates.includes(dateStr);
            }
        ],
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            // Format date as YYYY-MM-DD in local timezone
            const year = dayElem.dateObj.getFullYear();
            const month = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dayElem.dateObj.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;
            
            if (occupiedDates.includes(dateStr)) {
                dayElem.classList.add('occupied');
                dayElem.innerHTML = '<span style="text-decoration: line-through;">' + dayElem.innerHTML + '</span>';
                dayElem.title = 'This date is occupied - Event in progress';
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            // Double-check on selection - if somehow an occupied date is selected, clear it
            if (occupiedDates.includes(dateStr)) {
                instance.clear();
                alert('This date is already occupied. Please choose another date.');
            }
        },
        locale: {
            firstDayOfWeek: 0
        }
    });
    
    // Custom Scrollable Time Picker
    const timeInput = document.getElementById('eventTime');
    const timeDropdown = document.getElementById('timeDropdown');
    
    // Generate time slots from 7:00 AM to 10:00 PM with 30-min intervals
    function generateTimeSlots() {
        const slots = [];
        for (let hour = 7; hour <= 22; hour++) {
            for (let min of ['00', '30']) {
                if (hour === 22 && min === '30') break;
                
                let period = hour >= 12 ? 'PM' : 'AM';
                let displayHour = hour % 12;
                displayHour = displayHour === 0 ? 12 : displayHour;
                
                const timeStr = `${displayHour}:${min} ${period}`;
                const valueStr = `${hour.toString().padStart(2, '0')}:${min}`;
                slots.push({ display: timeStr, value: valueStr });
            }
        }
        return slots;
    }

    const timeSlots = generateTimeSlots();
    timeSlots.forEach(slot => {
        const div = document.createElement('div');
        div.className = 'time-option';
        div.textContent = slot.display;
        div.dataset.value = slot.value;
        div.addEventListener('click', function() {
            timeInput.value = slot.display;
            document.querySelectorAll('.time-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            timeDropdown.classList.remove('show');
        });
        timeDropdown.appendChild(div);
    });

    timeInput.addEventListener('click', function(e) {
        e.stopPropagation();
        timeDropdown.classList.toggle('show');
    });

    document.addEventListener('click', function(e) {
        if (!timeDropdown.contains(e.target) && e.target !== timeInput) {
            timeDropdown.classList.remove('show');
        }
    });

    // Also prevent form submission if occupied date somehow selected
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const selectedDate = document.getElementById('eventDate').value;
        if (occupiedDates.includes(selectedDate)) {
            e.preventDefault();
            alert('This date is already occupied. Please choose another date.');
            return false;
        }
    });
    
    function updateSummary() {
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        if (!selectedOption.value) {
            summaryContent.innerHTML = '<p class="text-muted text-center">Select a package to see summary</p>';
            totalAmount.textContent = '₱0.00';
            packageTotal = 0;
            updateTotal();
            return;
        }
        
        const price = parseFloat(selectedOption.dataset.price);
        const minPax = parseInt(selectedOption.dataset.min);
        const maxPax = parseInt(selectedOption.dataset.max);
        const guests = parseInt(guestCount.value) || minPax;
        
        paxInfo.textContent = `Min: ${minPax} | Max: ${maxPax} guests`;
        guestCount.min = minPax;
        guestCount.max = maxPax;
        
        if (!guestCount.value) guestCount.value = minPax;
        
        packageTotal = price * guests;
        
        summaryContent.innerHTML = `
            <div class="mb-2 d-flex justify-content-between">
                <span>Package:</span>
                <strong>${selectedOption.text.split(' - ')[0]}</strong>
            </div>
            <div class="mb-2 d-flex justify-content-between">
                <span>Price per head:</span>
                <span>₱${price.toFixed(2)}</span>
            </div>
            <div class="mb-2 d-flex justify-content-between">
                <span>Number of guests:</span>
                <span>${guests}</span>
            </div>
            <div class="d-flex justify-content-between pt-2 border-top">
                <span>Package Subtotal:</span>
                <strong>₱${packageTotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</strong>
            </div>
        `;
        
        updateTotal();
    }
    
    function updateMenuItemsSummary() {
        const checkedItems = document.querySelectorAll('.menu-item:checked');
        menuTotal = 0;
        let itemsHtml = '';
        
        if (checkedItems.length > 0) {
            menuItemsSummary.style.display = 'block';
            checkedItems.forEach(item => {
                const price = parseFloat(item.dataset.price);
                const name = item.dataset.name;
                const qtyInput = document.querySelector(`input.item-qty[data-item-id="${item.value}"]`);
                const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                const subtotal = price * qty;
                menuTotal += subtotal;
                itemsHtml += `<div class="d-flex justify-content-between small mb-1">
                    <span>${name} x${qty}</span>
                    <span>₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                </div>`;
            });
            selectedItemsList.innerHTML = itemsHtml;
            menuSubtotal.textContent = '₱' + menuTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
        } else {
            menuItemsSummary.style.display = 'none';
        }
        
        updateTotal();
    }
    
    function updateTotal() {
        const subtotal = packageTotal + menuTotal;
        let finalTotal = subtotal;
        
        // Clear previous promo summary if exists
        const oldPromoSummary = document.getElementById('promoSummary');
        if (oldPromoSummary) oldPromoSummary.remove();
        
        if (claimedPromo && claimedPromo.discount > 0) {
            const discountAmount = (subtotal * claimedPromo.discount) / 100;
            finalTotal = subtotal - discountAmount;
            
            const promoDiv = document.createElement('div');
            promoDiv.id = 'promoSummary';
            promoDiv.innerHTML = `
                <div class="d-flex justify-content-between text-success mt-2 pt-2 border-top">
                    <span>Promotion (${claimedPromo.title}):</span>
                    <strong>-${claimedPromo.discount}%</strong>
                </div>
                <div class="d-flex justify-content-between text-muted small">
                    <span>Discount Amount:</span>
                    <span>-₱${discountAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                </div>
            `;
            totalAmount.parentElement.before(promoDiv);
        }
        
        totalAmount.textContent = '₱' + finalTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    }
    
    packageSelect.addEventListener('change', updateSummary);
    guestCount.addEventListener('input', updateSummary);
    
    document.querySelectorAll('.menu-item').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.menu-item-card');
            const qtyControl = card.querySelector('.quantity-control');
            qtyControl.style.display = this.checked ? 'block' : 'none';
            updateMenuItemsSummary();
        });
    });
    
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
                updateMenuItemsSummary();
            }
        });
    });
    
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            if (input.value < 20) {
                input.value = parseInt(input.value) + 1;
                updateMenuItemsSummary();
            }
        });
    });
    
    document.querySelectorAll('.item-qty').forEach(input => {
        input.addEventListener('change', function() {
            if (parseInt(this.value) > 20) {
                this.value = 20;
                alert('Maximum of 20 trays per item allowed.');
            }
            if (parseInt(this.value) < 1) {
                this.value = 1;
            }
            updateMenuItemsSummary();
        });
    });
    
    if (packageSelect.value) updateSummary();
});
</script>

<?php require_once 'includes/footer.php'; ?>