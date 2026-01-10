<?php
require_once 'config/functions.php';

$settings = getSettings($conn);
$siteName = $settings['site_name'] ?? 'Filipino Catering';
$siteEmail = $settings['site_email'] ?? 'info@filipinocatering.com';
$sitePhone = $settings['site_phone'] ?? '+63 912 345 6789';
$siteAddress = $settings['site_address'] ?? 'Metro Manila, Philippines';

$packages = $conn->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY base_price LIMIT 3");
$menuItems = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN menu_categories c ON m.category_id = c.id WHERE m.is_featured = 1 AND m.is_available = 1 LIMIT 6");
$promotions = $conn->query("SELECT * FROM promotions WHERE is_active = 1 ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?> - Authentic Filipino Food Catering Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f97316;
            --primary-dark: #ea580c;
            --secondary: #1e3a5f;
            --accent: #22c55e;
            --cream: #fffbeb;
            --dark: #1e293b;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
        
        .top-bar {
            background: #0f172a;
            color: white;
            padding: 8px 0;
            font-size: 13px;
            font-weight: 500;
        }
        .top-bar i { color: var(--primary); }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary) !important;
        }
        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            margin: 0 10px;
            transition: all 0.3s;
        }
        .nav-link:hover { color: var(--primary) !important; transform: translateY(-2px); }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            font-weight: 500;
            border-radius: 10px;
            padding: 10px 25px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #c2410c 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
        }
        .btn-orange {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-orange:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
            color: white;
        }
        
        .hero {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.9), rgba(234, 88, 12, 0.9)), url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1920') center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
        }
        .hero h1 { font-size: 56px; font-weight: 700; margin-bottom: 20px; }
        .hero p { font-size: 20px; opacity: 0.9; margin-bottom: 30px; max-width: 600px; }
        
        section { padding: 80px 0; }
        .section-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        .section-subtitle {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 50px;
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 36px;
            color: white;
        }
        .feature-card h4 { font-weight: 600; margin-bottom: 15px; }
        .feature-card p { color: #64748b; }
        
        .package-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
        }
        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }
        .package-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 30px;
            color: white;
            text-align: center;
        }
        .package-header h4 { font-weight: 600; margin-bottom: 10px; }
        .package-price { font-size: 36px; font-weight: 700; }
        .package-price span { font-size: 16px; font-weight: 400; opacity: 0.9; }
        .package-body { padding: 30px; }
        .package-body ul { list-style: none; padding: 0; }
        .package-body li {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }
        .package-body li i { color: var(--accent); margin-right: 10px; }
        
        .menu-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }
        .menu-img {
            height: 180px;
            background: linear-gradient(135deg, #fed7aa, #fdba74);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            overflow: hidden;
        }
        .menu-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .menu-body { padding: 20px; }
        .menu-body h5 { font-weight: 600; margin-bottom: 5px; }
        .menu-body small { color: #64748b; }
        .menu-price { color: var(--primary); font-weight: 700; font-size: 18px; }
        
        .promo-section { 
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); 
            position: relative;
            overflow: hidden;
        }
        .promo-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: rgba(249, 115, 22, 0.05);
            border-radius: 50%;
        }
        .promo-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(249, 115, 22, 0.1);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            border: 1px solid rgba(249, 115, 22, 0.05);
        }
        .promo-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 60px rgba(249, 115, 22, 0.2);
        }
        .promo-img {
            height: 240px;
            background: linear-gradient(135deg, #fde68a, #fbbf24);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            overflow: hidden;
            position: relative;
        }
        .promo-img img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.6s ease;
        }
        .promo-card:hover .promo-img img {
            transform: scale(1.1);
        }
        .promo-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            color: var(--primary);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 2;
        }
        .promo-badge.sale {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        .promo-body { padding: 30px; }
        .promo-body h4 { 
            font-weight: 800; 
            margin-bottom: 12px; 
            color: var(--dark);
            font-size: 22px;
        }
        .promo-body p { 
            color: #64748b; 
            line-height: 1.8; 
            margin-bottom: 20px;
            font-size: 15px;
        }
        .promo-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .promo-date { 
            font-size: 13px; 
            color: #94a3b8; 
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .promo-date i { color: var(--primary); font-size: 16px; }
        
        .carousel-indicators [data-bs-target] {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--primary);
            margin: 0 5px;
        }
        .carousel-control-prev, .carousel-control-next {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            opacity: 0;
            transition: all 0.3s;
        }
        .carousel-control-prev { left: -25px; }
        .carousel-control-next { right: -25px; }
        .promo-section:hover .carousel-control-prev,
        .promo-section:hover .carousel-control-next {
            opacity: 1;
        }
        .carousel-control-prev-icon, .carousel-control-next-icon {
            filter: invert(1) sepia(100%) saturate(500%) hue-rotate(0deg);
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--secondary), #0d1b2a);
            color: white;
        }
        .cta-section h2 { font-size: 42px; font-weight: 700; margin-bottom: 20px; }
        
        footer {
            background: #0f172a;
            color: white;
            padding: 50px 0 30px;
        }
        footer h5 { font-weight: 600; margin-bottom: 20px; font-family: 'Playfair Display', serif; }
        footer a { color: rgba(255, 255, 255, 0.7); text-decoration: none; }
        footer a:hover { color: var(--primary); }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 10px;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.2s;
        }
        .dropdown-item:hover {
            background: #fff7ed;
            color: var(--primary);
        }
    </style>
</head>
  <body>
    <header class="sticky-top shadow-sm">
        <div class="top-bar">
            <div class="container d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-clock me-2"></i>
                    <span id="ph-time-display">Loading time...</span>
                </div>
                <div class="d-none d-md-block">
                    <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($siteAddress) ?>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="<?= url('index.php') ?>">🍲 <?= htmlspecialchars($siteName) ?></a>
                
                <div class="d-flex align-items-center order-lg-last">
                    <?php if (isLoggedIn()): ?>
                        <?php $currentUser = getCurrentUser($conn); ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 me-1"></i>
                                <span class="d-none d-sm-inline"><?= htmlspecialchars($currentUser['first_name'] ?? 'User') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= url('my-bookings.php') ?>"><i class="bi bi-calendar-check me-2"></i>My Bookings</a></li>
                                <li><a class="dropdown-item" href="<?= url('profile.php') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <?php if (in_array(getUserRole(), ['admin', 'super_admin'])): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= adminUrl('dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                <?php elseif (getUserRole() === 'staff'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= staffUrl('dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Staff Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons d-flex align-items-center">
                            <a href="<?= url('login.php') ?>" class="nav-link d-none d-sm-block me-2">Login</a>
                            <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm px-3">Sign Up</a>
                        </div>
                    <?php endif; ?>
                    
                    <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="<?= url('index.php') ?>"><i class="bi bi-house me-1"></i>Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= url('menu.php') ?>"><i class="bi bi-menu-button-wide me-1"></i>Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= url('packages.php') ?>"><i class="bi bi-gift me-1"></i>Packages</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= url('book.php') ?>"><i class="bi bi-calendar-plus me-1"></i>Book Now</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <section class="hero">
        <div class="container">
            <h1>Authentic Filipino<br>Catering Services</h1>
            <p>Experience the rich flavors of Filipino cuisine at your next event. From traditional favorites to modern Filipino fusion, we bring the taste of the Philippines to your celebrations.</p>
            <a href="<?= url('book.php') ?>" class="btn btn-orange btn-lg me-3">
                <i class="bi bi-calendar-check me-2"></i>Book Now
            </a>
            <a href="<?= url('menu.php') ?>" class="btn btn-outline-light btn-lg">
                <i class="bi bi-list-ul me-2"></i>View Menu
            </a>
        </div>
    </section>
    
    <section class="bg-light">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Why Choose Us?</h2>
                <p class="section-subtitle">We deliver excellence in every dish we serve</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-star-fill"></i></div>
                        <h4>Authentic Filipino Taste</h4>
                        <p>Traditional recipes passed down through generations, prepared with love and the finest ingredients.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                        <h4>Professional Service</h4>
                        <p>Our experienced team ensures smooth, worry-free catering for events of any size.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-currency-dollar"></i></div>
                        <h4>Affordable Packages</h4>
                        <p>Quality catering that fits your budget with flexible packages to choose from.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php if ($packages && $packages->num_rows > 0): ?>
    <section>
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Our Packages</h2>
                <p class="section-subtitle">Choose the perfect package for your event</p>
            </div>
            <div class="row g-4">
                <?php while($pkg = $packages->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="package-card">
                        <div class="package-header">
                            <h4><?= htmlspecialchars($pkg['name']) ?></h4>
                            <div class="package-price"><?= formatPrice($pkg['base_price']) ?> <span>/ head</span></div>
                        </div>
                        <div class="package-body">
                            <p class="text-muted mb-3"><?= htmlspecialchars($pkg['description'] ?? '') ?></p>
                            <ul>
                                <li><i class="bi bi-check-circle-fill"></i> <?= $pkg['min_pax'] ?> - <?= $pkg['max_pax'] ?> guests</li>
                                <?php 
                                $inclusions = explode(',', $pkg['inclusions'] ?? '');
                                foreach(array_slice($inclusions, 0, 4) as $inc): 
                                    if(trim($inc)):
                                ?>
                                <li><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars(trim($inc)) ?></li>
                                <?php endif; endforeach; ?>
                            </ul>
                            <a href="<?= url('book.php?package=' . $pkg['id']) ?>" class="btn btn-orange w-100 mt-3">Select Package</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?= url('packages.php') ?>" class="btn btn-outline-primary btn-lg">View All Packages</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if ($menuItems && $menuItems->num_rows > 0): ?>
    <section class="bg-light">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Featured Menu Items</h2>
                <p class="section-subtitle">Taste our most popular Filipino dishes</p>
            </div>
            <div class="row g-4">
                <?php while($item = $menuItems->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-2">
                    <div class="menu-card">
                        <div class="menu-img">
                            <?php if ($item['image']): ?>
                                <img src="<?= url('uploads/menu/' . $item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                                🍲
                            <?php endif; ?>
                        </div>
                        <div class="menu-body">
                            <h5><?= htmlspecialchars($item['name']) ?></h5>
                            <small><?= htmlspecialchars($item['category_name'] ?? '') ?></small>
                            <div class="menu-price mt-2"><?= formatPrice($item['price']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?= url('menu.php') ?>" class="btn btn-outline-primary btn-lg">View Full Menu</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if ($promotions && $promotions->num_rows > 0): ?>
    <section class="promo-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title"><i class="bi bi-megaphone me-2"></i>Special Offers</h2>
                <p class="section-subtitle">Exclusively prepared for your special occasions</p>
            </div>
            
            <?php if ($promotions->num_rows > 3): ?>
            <!-- Carousel for many promotions -->
            <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php 
                    $total = $promotions->num_rows;
                    for($i = 0; $i < ceil($total/3); $i++): 
                    ?>
                    <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></button>
                    <?php endfor; ?>
                </div>
                <div class="carousel-inner p-4">
                    <?php 
                    $count = 0;
                    mysqli_data_seek($promotions, 0);
                    while($promo = $promotions->fetch_assoc()): 
                        if ($count % 3 === 0):
                    ?>
                    <div class="carousel-item <?= $count === 0 ? 'active' : '' ?>">
                        <div class="row g-4">
                    <?php endif; ?>
                            <div class="col-md-4">
                                <div class="promo-card">
                                    <div class="promo-img">
                                        <?php if ($promo['image']): ?>
                                            <img src="<?= url('uploads/promotions/' . $promo['image']) ?>" alt="<?= htmlspecialchars($promo['title']) ?>">
                                        <?php else: ?>
                                            🎉
                                        <?php endif; ?>
                                        <?php if ($promo['discount_percentage'] > 0): ?>
                                            <span class="promo-badge sale"><?= $promo['discount_percentage'] ?>% OFF</span>
                                        <?php else: ?>
                                            <span class="promo-badge">Limited Offer</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="promo-body">
                                        <h4><?= htmlspecialchars($promo['title']) ?></h4>
                                        <p><?= nl2br(htmlspecialchars($promo['description'] ?? '')) ?></p>
                                        <div class="promo-footer">
                                            <?php if ($promo['end_date']): ?>
                                                <div class="promo-date">
                                                    <i class="bi bi-calendar-event"></i> Until <?= date('M d, Y', strtotime($promo['end_date'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="promo-date">
                                                    <i class="bi bi-check-circle"></i> Available Now
                                                </div>
                                            <?php endif; ?>
                                            <a href="<?= url('packages.php?claim_promo=' . $promo['id']) ?>" class="btn btn-sm btn-orange rounded-pill px-4">Claim Now</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php 
                        $count++;
                        if ($count % 3 === 0 || $count === $total):
                    ?>
                        </div>
                    </div>
                    <?php endif; endwhile; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
            <?php else: ?>
            <!-- Grid for few promotions -->
            <div class="row g-4 justify-content-center">
                <?php 
                mysqli_data_seek($promotions, 0);
                while($promo = $promotions->fetch_assoc()): 
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="promo-card">
                        <div class="promo-img">
                            <?php if ($promo['image']): ?>
                                <img src="<?= url('uploads/promotions/' . $promo['image']) ?>" alt="<?= htmlspecialchars($promo['title']) ?>">
                            <?php else: ?>
                                <div class="fs-1">🎁</div>
                            <?php endif; ?>
                            <?php if ($promo['discount_percentage'] > 0): ?>
                                <span class="promo-badge sale"><?= $promo['discount_percentage'] ?>% OFF</span>
                            <?php else: ?>
                                <span class="promo-badge">Limited Offer</span>
                            <?php endif; ?>
                        </div>
                        <div class="promo-body">
                            <h4><?= htmlspecialchars($promo['title']) ?></h4>
                            <p><?= nl2br(htmlspecialchars($promo['description'] ?? '')) ?></p>
                            <div class="promo-footer">
                                <?php if ($promo['end_date']): ?>
                                    <div class="promo-date">
                                        <i class="bi bi-calendar-event"></i> Until <?= date('M d, Y', strtotime($promo['end_date'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="promo-date">
                                        <i class="bi bi-check-circle"></i> Available Now
                                    </div>
                                <?php endif; ?>
                                <a href="<?= url('packages.php?claim_promo=' . $promo['id']) ?>" class="btn btn-sm btn-orange rounded-pill px-4">Claim Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <section class="cta-section text-center">
        <div class="container">
            <h2>Ready to Plan Your Event?</h2>
            <p class="mb-4 opacity-75">Let us make your celebration unforgettable with authentic Filipino cuisine</p>
            <a href="<?= url('book.php') ?>" class="btn btn-orange btn-lg">
                <i class="bi bi-calendar-check me-2"></i>Start Booking Now
            </a>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>🍲 <?= htmlspecialchars($siteName) ?></h5>
                    <p class="text-white-50">Bringing authentic Filipino flavors to your special occasions since 2020.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url('menu.php') ?>">Menu</a></li>
                        <li class="mb-2"><a href="<?= url('packages.php') ?>">Packages</a></li>
                        <li class="mb-2"><a href="<?= url('book.php') ?>">Book Now</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($siteEmail) ?></li>
                        <li class="mb-2"><i class="bi bi-phone me-2"></i><?= htmlspecialchars($sitePhone) ?></li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($siteAddress) ?></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom text-center text-white-50">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <div id="cookieConsent" class="cookie-banner">
        <div class="cookie-content">
            <div class="cookie-text">
                <strong>Gumagamit ang website na ito ng cookies</strong>
                <p>Sa pag-click ng "Tanggapin lahat", sumasang-ayon ka sa pag-iimbak ng cookies sa iyong device upang mapabuti ang pag-navigate sa site, suriin ang paggamit ng site, at tumulong sa aming mga pagsisikap sa marketing. <a href="#" class="cookie-link" data-bs-toggle="modal" data-bs-target="#cookieNoticeModal">Cookie Notice</a></p>
            </div>
            <div class="cookie-buttons">
                <button type="button" class="cookie-btn cookie-settings" data-bs-toggle="modal" data-bs-target="#cookieSettingsModal">Cookie Settings</button>
                <button type="button" class="cookie-btn cookie-reject" onclick="rejectCookies()">Tanggihan lahat</button>
                <button type="button" class="cookie-btn cookie-accept" onclick="acceptCookies()">Tanggapin lahat</button>
            </div>
            <button type="button" class="cookie-close" onclick="closeCookieBanner()">&times;</button>
        </div>
    </div>
    
    <div class="modal fade" id="cookieSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Cookie Settings</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size: 14px;">
                    <p class="text-muted mb-4">I-manage ang iyong cookie preferences. Ang ilang cookies ay kinakailangan para gumana nang maayos ang website.</p>
                    
                    <div class="cookie-setting-item mb-3 p-3" style="background: #f8fafc; border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-shield-check text-success me-2"></i>Kinakailangang Cookies</strong>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Mga essential cookies para sa pag-function ng website.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked disabled style="width: 50px; height: 26px;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="cookie-setting-item mb-3 p-3" style="background: #f8fafc; border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-bar-chart text-primary me-2"></i>Analytics Cookies</strong>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Tumutulong sa pag-intindi kung paano ginagamit ng mga bisita ang website.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input cookie-analytics" type="checkbox" id="analyticsCookies" style="width: 50px; height: 26px;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="cookie-setting-item mb-3 p-3" style="background: #f8fafc; border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-megaphone text-warning me-2"></i>Marketing Cookies</strong>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Ginagamit para magpakita ng mga relevant na advertisement.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input cookie-marketing" type="checkbox" id="marketingCookies" style="width: 50px; height: 26px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Isara</button>
                    <button type="button" class="btn btn-primary" onclick="saveCookieSettings()">
                        <i class="bi bi-check2 me-1"></i>I-save ang Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="cookieNoticeModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%); color: white;">
                    <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Cookie Notice / Abiso sa Cookies</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size: 14px; line-height: 1.8;">
                    <h6 class="fw-bold text-uppercase" style="color: #f97316;">Ano ang Cookies?</h6>
                    <p>Ang cookies ay maliliit na text files na iniimbak sa iyong device (computer, tablet, o mobile phone) kapag bumibisita ka sa aming website. Ang mga ito ay malawakang ginagamit upang gumana nang maayos ang mga website at upang magbigay ng impormasyon sa mga may-ari ng website.</p>
                    
                    <h6 class="fw-bold mt-4">Paano Namin Ginagamit ang Cookies?</h6>
                    <p>Gumagamit kami ng cookies para sa mga sumusunod na layunin:</p>
                    <ul>
                        <li><strong>Kinakailangang Cookies:</strong> Ang mga ito ay mahalaga para gumana ang aming website nang maayos. Kasama dito ang cookies na nagpapaalala sa iyong session at mga preference.</li>
                        <li><strong>Analytics Cookies:</strong> Ang mga ito ay nagbibigay-daan sa amin na suriin kung paano ginagamit ng mga bisita ang aming website, na tumutulong sa amin na mapabuti ito.</li>
                        <li><strong>Marketing Cookies:</strong> Ang mga ito ay ginagamit upang ipakita ang mga advertisement na mas relevant sa iyo at sa iyong mga interes.</li>
                    </ul>
                    
                    <h6 class="fw-bold mt-4">Paano Ko Makokontrol ang Cookies?</h6>
                    <p>Maaari mong i-manage ang iyong cookie preferences sa pamamagitan ng pag-click sa "Cookie Settings" button sa aming cookie banner. Maaari mo ring i-configure ang iyong browser na tanggihan ang lahat ng cookies o alertuhan ka kapag may cookie na inilalagay.</p>
                    
                    <h6 class="fw-bold mt-4">Alinsunod sa Data Privacy Act of 2012 (RA 10173)</h6>
                    <p>Alinsunod sa Republic Act No. 10173 o ang Data Privacy Act of 2012, iginagalang namin ang iyong karapatan sa privacy. Ang iyong personal na impormasyon na nakolekta sa pamamagitan ng cookies ay mapoprotektahan at gagamitin lamang para sa mga layuning nabanggit sa itaas.</p>
                    
                    <div class="alert alert-warning mt-4" style="border-radius: 12px;">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Paalala:</strong> Kung patuloy mong gamitin ang aming website nang hindi binabago ang iyong cookie settings, itinuturing naming tinatanggap mo ang paggamit ng cookies.
                    </div>
                    
                    <h6 class="fw-bold mt-4">Makipag-ugnayan</h6>
                    <p>Kung mayroon kang mga katanungan tungkol sa aming paggamit ng cookies, mangyaring makipag-ugnayan sa amin sa pamamagitan ng aming contact page.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Isara</button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #e2e8f0;
            z-index: 9999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
            display: none;
        }
        .cookie-banner.show {
            display: block;
            animation: slideUp 0.3s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        .cookie-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 50px 16px 30px;
            max-width: 1600px;
            margin: 0 auto;
            gap: 30px;
        }
        .cookie-text {
            flex: 1;
        }
        .cookie-text strong {
            font-size: 15px;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }
        .cookie-text p {
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            color: #94a3b8;
        }
        .cookie-link {
            color: #f97316;
            text-decoration: underline;
        }
        .cookie-link:hover {
            color: #fb923c;
        }
        .cookie-buttons {
            display: flex;
            gap: 12px;
            flex-shrink: 0;
        }
        .cookie-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .cookie-settings {
            background: transparent;
            border: 1px solid #64748b;
            color: #e2e8f0;
        }
        .cookie-settings:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #f97316;
            color: #f97316;
        }
        .cookie-reject {
            background: #475569;
            border: none;
            color: white;
        }
        .cookie-reject:hover {
            background: #64748b;
        }
        .cookie-accept {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border: none;
            color: white;
        }
        .cookie-accept:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }
        .cookie-close {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            line-height: 1;
        }
        .cookie-close:hover {
            color: #f97316;
        }
        @media (max-width: 991px) {
            .cookie-content {
                flex-direction: column;
                text-align: center;
                padding: 20px 40px 20px 20px;
                gap: 15px;
            }
            .cookie-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        @media (max-width: 576px) {
            .cookie-buttons {
                flex-direction: column;
                width: 100%;
            }
            .cookie-btn {
                width: 100%;
            }
        }
        
        .form-check-input:checked {
            background-color: #f97316;
            border-color: #f97316;
        }
        .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.2);
            border-color: #f97316;
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkCookieConsent() {
            const consent = localStorage.getItem('cookieConsent');
            if (!consent) {
                document.getElementById('cookieConsent').classList.add('show');
            } else {
                loadCookiePreferences();
            }
        }
        
        function acceptCookies() {
            const preferences = {
                necessary: true,
                analytics: true,
                marketing: true,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('cookieConsent', 'accepted');
            localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
            document.getElementById('cookieConsent').classList.remove('show');
        }
        
        function rejectCookies() {
            const preferences = {
                necessary: true,
                analytics: false,
                marketing: false,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('cookieConsent', 'rejected');
            localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
            document.getElementById('cookieConsent').classList.remove('show');
        }
        
        function closeCookieBanner() {
            document.getElementById('cookieConsent').classList.remove('show');
        }
        
        function saveCookieSettings() {
            const analytics = document.getElementById('analyticsCookies').checked;
            const marketing = document.getElementById('marketingCookies').checked;
            
            const preferences = {
                necessary: true,
                analytics: analytics,
                marketing: marketing,
                timestamp: new Date().toISOString()
            };
            
            localStorage.setItem('cookieConsent', 'custom');
            localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
            document.getElementById('cookieConsent').classList.remove('show');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('cookieSettingsModal'));
            modal.hide();
            
            showToast('Matagumpay na na-save ang iyong cookie preferences!');
        }
        
        function loadCookiePreferences() {
            const prefs = localStorage.getItem('cookiePreferences');
            if (prefs) {
                const preferences = JSON.parse(prefs);
                document.getElementById('analyticsCookies').checked = preferences.analytics || false;
                document.getElementById('marketingCookies').checked = preferences.marketing || false;
            }
        }
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '10000';
            toast.innerHTML = `
                <div class="toast show" role="alert" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border-radius: 12px;">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        ${message}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
          document.addEventListener('DOMContentLoaded', function() {
              checkCookieConsent();
              loadCookiePreferences();
              
              function updateTime() {
                  const now = new Date();
                  const options = { 
                      timeZone: 'Asia/Manila',
                      weekday: 'long',
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                      second: '2-digit',
                      hour12: true
                  };
                  const formatter = new Intl.DateTimeFormat('en-PH', options);
                  const display = document.getElementById('ph-time-display');
                  if (display) {
                      display.textContent = formatter.format(now) + ' (PHT)';
                  }
              }
              setInterval(updateTime, 1000);
              updateTime();
          });

    </script>
</body>
</html>