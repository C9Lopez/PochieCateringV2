<?php
$pageTitle = "Catering Packages";
require_once 'includes/header.php';

// Handle Claimed Promotion
if (isset($_GET['claim_promo'])) {
    $promoId = (int)$_GET['claim_promo'];
    $promoQuery = $conn->query("SELECT * FROM promotions WHERE id = $promoId AND is_active = 1");
    if ($promoQuery && $promoQuery->num_rows > 0) {
        $promo = $promoQuery->fetch_assoc();
        $_SESSION['claimed_promo'] = [
            'id' => $promo['id'],
            'title' => $promo['title'],
            'discount' => $promo['discount_percentage']
        ];
    }
}

$claimedPromo = $_SESSION['claimed_promo'] ?? null;
$packages = $conn->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY base_price");
?>

<div class="container py-5">
    <?php if ($claimedPromo): ?>
    <div class="alert alert-success alert-dismissible fade show mb-5 rounded-4 border-0 shadow-sm d-flex align-items-center p-4">
        <div class="fs-1 me-4">🎁</div>
        <div>
            <h4 class="alert-heading mb-1">Promotion Applied!</h4>
            <p class="mb-0">You've claimed: <strong><?= htmlspecialchars($claimedPromo['title']) ?></strong>. 
            A <strong><?= $claimedPromo['discount'] ?>% discount</strong> will be automatically applied to your package.</p>
        </div>
        <a href="?remove_promo=1" class="btn-close" style="top: 20px; right: 20px;"></a>
    </div>
    <?php 
    endif;
    
    if (isset($_GET['remove_promo'])) {
        unset($_SESSION['claimed_promo']);
        header("Location: packages.php");
        exit;
    }
    ?>
    
    <div class="text-center mb-5">
        <h1 class="section-title">Catering Packages</h1>
        <p class="text-muted mt-4">Choose the perfect package for your celebration</p>
    </div>
    
    <div class="row g-4">
        <?php while($pkg = $packages->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="mb-1"><?= htmlspecialchars($pkg['name']) ?></h3>
                        <?php if ($claimedPromo && $claimedPromo['discount'] > 0): 
                            $originalPrice = $pkg['base_price'];
                            $discountAmount = ($originalPrice * $claimedPromo['discount']) / 100;
                            $discountedPrice = $originalPrice - $discountAmount;
                        ?>
                            <div class="mb-0">
                                <span class="text-decoration-line-through text-muted fs-5"><?= formatPrice($originalPrice) ?></span>
                                <p class="display-4 fw-bold mb-0" style="color: var(--primary);">
                                    <?= formatPrice($discountedPrice) ?>
                                </p>
                                <span class="badge bg-danger rounded-pill">-<?= $claimedPromo['discount'] ?>% OFF</span>
                            </div>
                        <?php else: ?>
                            <p class="display-4 fw-bold mb-0" style="color: var(--primary);">
                                <?= formatPrice($pkg['base_price']) ?>
                            </p>
                        <?php endif; ?>
                        <small class="text-muted">per head</small>
                    </div>
                    
                    <p class="text-muted mb-4"><?= htmlspecialchars($pkg['description']) ?></p>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Minimum Guests:</span>
                            <strong><?= $pkg['min_pax'] ?> pax</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Maximum Guests:</span>
                            <strong><?= $pkg['max_pax'] ?> pax</strong>
                        </div>
                    </div>
                    
                    <h6 class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>What's Included:</h6>
                    <ul class="list-unstyled mb-4">
                        <?php 
                        $inclusions = explode(',', $pkg['inclusions']);
                        foreach($inclusions as $inc): 
                        ?>
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-2"></i>
                            <?= htmlspecialchars(trim($inc)) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= url('book.php?package=' . $pkg['id']) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-calendar-plus me-2"></i>Select Package
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="mt-5 p-5 bg-white rounded-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4><i class="bi bi-lightbulb text-warning me-2"></i>Need a Custom Package?</h4>
                <p class="text-muted mb-0">Can't find what you're looking for? We can create a custom package tailored to your specific needs and budget. Contact us to discuss your requirements!</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= url('book.php') ?>" class="btn btn-secondary btn-lg">
                    <i class="bi bi-chat-dots me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
    
    <div class="mt-5">
        <h3 class="text-center mb-4">Frequently Asked Questions</h3>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How far in advance should I book?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        We recommend booking at least 3 days in advance for small events and 1-2 weeks for larger celebrations to ensure availability and proper preparation.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Can I customize the menu in a package?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes! All our packages allow you to choose from our wide selection of Filipino dishes. You can mix and match to suit your guests' preferences.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        What is the payment process?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        We require a 50% down payment to confirm your booking. The remaining balance is due on or before the event date.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                        Do you provide serving staff?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, all packages include professional wait staff. The number of staff depends on your guest count and package selected.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
