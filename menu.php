<?php
$pageTitle = "Our Menu";
require_once 'includes/header.php';

$categories = $conn->query("SELECT * FROM menu_categories WHERE is_active = 1 ORDER BY name");
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;

if ($selectedCategory) {
    $items = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id = c.id WHERE m.is_available = 1 AND m.category_id = $selectedCategory ORDER BY m.name");
} else {
    $items = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id = c.id WHERE m.is_available = 1 ORDER BY c.name, m.name");
}
?>

<style>
/* Mobile responsive styles for menu page */
@media (max-width: 575.98px) {
    /* Category buttons */
    .d-flex.flex-wrap.gap-2 {
        gap: 8px !important;
    }
    
    .d-flex.flex-wrap.gap-2 .btn {
        font-size: 12px;
        padding: 6px 12px;
    }
    
    /* Menu cards - 2 columns on mobile */
    .col-md-4.col-lg-3 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 6px;
    }
    
    .card .card-img-top,
    .card .card-img-top + div {
        height: 120px !important;
    }
    
    .card-body {
        padding: 12px;
    }
    
    .card-body .badge {
        font-size: 9px;
        padding: 3px 6px;
        margin-bottom: 5px !important;
    }
    
    .card-title {
        font-size: 13px !important;
        margin-bottom: 5px;
    }
    
    .card-text {
        font-size: 11px !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .card-footer {
        padding: 10px 12px;
    }
    
    .card-footer .fw-bold {
        font-size: 14px !important;
    }
    
    .card-footer small {
        font-size: 10px;
    }
    
    /* Category headers */
    .col-12.mt-4 h3 {
        font-size: 16px !important;
    }
    
    /* CTA section */
    .text-center.mt-5.p-5 {
        padding: 25px !important;
    }
    
    .text-center.mt-5 h4 {
        font-size: 16px !important;
    }
    
    .text-center.mt-5 p {
        font-size: 13px;
    }
}

@media (max-width: 374px) {
    .col-md-4.col-lg-3 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .card .card-img-top,
    .card .card-img-top + div {
        height: 150px !important;
    }
}
</style>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="section-title">Our Menu</h1>
        <p class="text-muted mt-4">Discover the authentic taste of Filipino cuisine</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= url('menu.php') ?>" class="btn <?= !$selectedCategory ? 'btn-primary' : 'btn-outline-primary' ?>">All Categories</a>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <a href="<?= url('menu.php?category=' . $cat['id']) ?>" class="btn <?= $selectedCategory == $cat['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <?php 
        $currentCategory = '';
        while($item = $items->fetch_assoc()): 
            if (!$selectedCategory && $currentCategory !== $item['category_name']):
                $currentCategory = $item['category_name'];
        ?>
            <div class="col-12 mt-4">
                <h3 class="border-bottom pb-2" style="color: var(--primary);">
                    <i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i><?= htmlspecialchars($currentCategory) ?>
                </h3>
            </div>
        <?php endif; ?>
        
        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <?php if ($item['image']): ?>
                    <img src="<?= url('uploads/menu/' . $item['image']) ?>" 
                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center" 
                         style="height: 200px; background: linear-gradient(135deg, #fed7aa, #fdba74); font-size: 4rem;">
                        🍲
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="badge bg-secondary mb-2"><?= htmlspecialchars($item['category_name']) ?></span>
                    <?php if($item['is_featured']): ?>
                        <span class="badge bg-warning text-dark mb-2"><i class="bi bi-star-fill"></i> Featured</span>
                    <?php endif; ?>
                    <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                    <p class="card-text small text-muted"><?= htmlspecialchars($item['description']) ?></p>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5" style="color: var(--primary);"><?= formatPrice($item['price']) ?></span>
                    <small class="text-muted">per tray</small>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="text-center mt-5 p-5 bg-white rounded-4 shadow-sm">
        <h4>Want to try our delicious dishes?</h4>
        <p class="text-muted">Book now and customize your menu for your special event!</p>
        <a href="<?= url('book.php') ?>" class="btn btn-primary btn-lg px-5"><i class="bi bi-calendar-plus me-2"></i>Book Now</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>