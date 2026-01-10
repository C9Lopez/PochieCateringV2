<?php
require_once 'config/functions.php';
require_once 'includes/header.php';

$settings = getSettings($conn);
$termsContent = $settings['terms_of_use'] ?? 'No Terms of Use published yet.';
$lastUpdated = $conn->query("SELECT updated_at FROM terms_history WHERE type = 'terms_of_use' ORDER BY updated_at DESC LIMIT 1")->fetch_assoc();
$displayDate = $lastUpdated ? date('F j, Y', strtotime($lastUpdated['updated_at'])) : date('F j, Y');
?>

<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 card p-4 p-md-5 shadow-sm border-0" style="border-radius: 20px;">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #1e3a5f;">Terms of Use</h1>
                <div style="width: 80px; height: 4px; background: #f97316; margin: 20px auto; border-radius: 2px;"></div>
                <p class="text-muted">Last updated: <?= $displayDate ?></p>
            </div>
            
            <div class="terms-content" style="font-size: 16px; line-height: 1.8; color: #475569;">
                <?= nl2br($termsContent) ?>
                
                <div class="mt-5 text-center border-top pt-4">
                    <a href="<?= url('index.php') ?>" class="btn btn-orange px-5 py-3 rounded-pill fw-bold">
                        <i class="bi bi-house-door me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
