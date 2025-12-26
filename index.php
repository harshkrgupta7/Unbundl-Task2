<?php


require_once 'backend.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarsDekho - Find Your Dream Car</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="/logo.png">
</head>
<body>

    <div class="modal" id="authModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Welcome to CarsDekho</h2>
                <button class="modal-close" onclick="closeAuthModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="authAlert" style="display: none;"></div>
                <div class="auth-tabs">
                    <button class="auth-tab active" onclick="switchAuthTab('login')">Sign In</button>
                    <button class="auth-tab" onclick="switchAuthTab('register')">Register</button>
                </div>
                <form id="loginForm" class="auth-form active" onsubmit="handleLogin(event)">
                    <div class="form-group"><label class="form-label">Email Address</label><input type="email" class="form-input" name="email" required></div>
                    <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-input" name="password" required></div>
                    <button type="submit" class="form-btn">Sign In</button>
                </form>
                <form id="registerForm" class="auth-form" onsubmit="handleRegister(event)">
                    <div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-input" name="full_name" required></div>
                    <div class="form-group"><label class="form-label">Username</label><input type="text" class="form-input" name="username" required></div>
                    <div class="form-group"><label class="form-label">Email Address</label><input type="email" class="form-input" name="email" required></div>
                    <div class="form-group">
                        <label class="form-label">Select Role</label>
                        <div class="role-options">
                            <div class="role-option">
                                <input type="radio" id="role_user" name="role" value="user" checked>
                                <label for="role_user" class="role-label"><i class="fas fa-user"></i><div class="role-name">User</div></label>
                            </div>
                            <div class="role-option">
                                <input type="radio" id="role_admin" name="role" value="admin">
                                <label for="role_admin" class="role-label"><i class="fas fa-user-shield"></i><div class="role-name">Admin</div></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-input" name="password" required minlength="6"></div>
                    <div class="form-group"><label class="form-label">Confirm Password</label><input type="password" class="form-input" name="confirm_password" required></div>
                    <button type="submit" class="form-btn">Create Account</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Profile</h2>
                <button class="modal-close" onclick="closeEditProfileModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="profileAlert" style="display: none;"></div>
                <form id="editProfileForm" onsubmit="handleUpdateProfile(event)" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Profile Picture</label>
                        <div class="profile-upload-container">
                            <div class="profile-image-preview" id="profileImagePreview">
                                <?php if ($userProfileImage && file_exists('uploads/profiles/' . $userProfileImage)): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($userProfileImage); ?>" alt="Profile" id="previewImage">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&size=120&background=f97316&color=fff" alt="Profile" id="previewImage">
                                <?php endif; ?>
                            </div>
                            <div class="profile-upload-controls">
                                <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="display: none;">
                                <button type="button" class="upload-btn" onclick="document.getElementById('profileImageInput').click()"><i class="fas fa-camera"></i> Choose Photo</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-input" name="full_name" value="<?php echo htmlspecialchars($userName); ?>" required></div>
                    <div class="form-group"><label class="form-label">Email Address</label><input type="email" class="form-input" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required></div>
                    <div class="password-change-section">
                        <h3 class="section-subtitle">Change Password (Optional)</h3>
                        <div class="form-group"><label class="form-label">Current Password</label><input type="password" class="form-input" name="current_password" placeholder="Enter current password"></div>
                        <div class="form-group"><label class="form-label">New Password</label><input type="password" class="form-input" name="new_password" placeholder="Enter new password"></div>
                        <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" class="form-input" name="confirm_password" placeholder="Confirm new password"></div>
                    </div>
                    <button type="submit" class="form-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="manageDataModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 class="modal-title">Manage Website Data</h2>
                <button class="modal-close" onclick="closeManageDataModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="manageAlert" style="display: none;"></div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion('bannerSection')">
                        <span><i class="fas fa-images"></i> Banners</span><i class="fas fa-chevron-down arrow"></i>
                    </button>
                    <div id="bannerSection" class="accordion-content">
                        <div class="manage-actions">
                            <button class="add-btn" onclick="showAddForm('banner')"><i class="fas fa-plus"></i> Add New Banner</button>
                        </div>
                        <div class="manage-list">
                            <?php foreach($banners as $b): ?>
                                <div class="manage-card">
                                    <img src="<?php echo getImagePath($b['image'], 'uploads/banners/'); ?>" alt="Banner">
                                    <div class="manage-info">
                                        <h4><?php echo $b['title']; ?></h4>
                                        <p><?php echo $b['subtitle']; ?></p>
                                    </div>
                                    <div class="manage-btns">
                                        <button onclick='openEditForm("banner", <?php echo json_encode($b); ?>)' class="edit-icon"><i class="fas fa-edit"></i></button>
                                        <button onclick="deleteItem('banner', <?php echo $b['id']; ?>)" class="delete-icon"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion('searchCarsSection')">
                        <span><i class="fas fa-search"></i> Searched Cars</span><i class="fas fa-chevron-down arrow"></i>
                    </button>
                    <div id="searchCarsSection" class="accordion-content">
                        <div class="manage-actions">
                            <button class="add-btn" onclick="showAddForm('searched_cars')"><i class="fas fa-plus"></i> Add Searched Car</button>
                        </div>
                        <div class="manage-list">
                            <?php foreach($searchedCars as $c): ?>
                                <div class="manage-card">
                                    <img src="<?php echo getImagePath($c['image'], 'uploads/cars/'); ?>" alt="Car">
                                    <div class="manage-info">
                                        <h4><?php echo $c['name']; ?></h4>
                                        <p><?php echo $c['category']; ?> | ₹<?php echo $c['price']; ?></p>
                                    </div>
                                    <div class="manage-btns">
                                        <button onclick='openEditForm("searched_cars", <?php echo json_encode($c); ?>)' class="edit-icon"><i class="fas fa-edit"></i></button>
                                        <button onclick="deleteItem('searched_cars', <?php echo $c['id']; ?>)" class="delete-icon"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion('latestCarsSection')">
                        <span><i class="fas fa-car"></i> Latest Cars</span><i class="fas fa-chevron-down arrow"></i>
                    </button>
                    <div id="latestCarsSection" class="accordion-content">
                        <div class="manage-actions">
                            <button class="add-btn" onclick="showAddForm('latest_cars')"><i class="fas fa-plus"></i> Add Latest Car</button>
                        </div>
                        <div class="manage-list">
                            <?php foreach($latestCars as $c): ?>
                                <div class="manage-card">
                                    <img src="<?php echo getImagePath($c['image'], 'uploads/cars/'); ?>" alt="Car">
                                    <div class="manage-info">
                                        <h4><?php echo $c['name']; ?></h4>
                                        <p>₹<?php echo $c['price']; ?></p>
                                    </div>
                                    <div class="manage-btns">
                                        <button onclick='openEditForm("latest_cars", <?php echo json_encode($c); ?>)' class="edit-icon"><i class="fas fa-edit"></i></button>
                                        <button onclick="deleteItem('latest_cars', <?php echo $c['id']; ?>)" class="delete-icon"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div id="manageFormContainer" style="display:none; margin-top:20px; border-top:1px solid #ccc; padding-top:20px;">
                    <h3 id="formTitle">Add/Edit Item</h3>
                    <form id="dynamicManageForm" onsubmit="handleManageSubmit(event)" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="manage_data">
                        <input type="hidden" name="operation" id="formOperation">
                        <input type="hidden" name="type" id="formType">
                        <input type="hidden" name="item_id" id="formItemId">
                        
                        <div id="formFields"></div>
                        
                        <div class="form-group">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-input">
                            <small>Leave empty to keep existing image (if editing)</small>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="form-btn">Save</button>
                            <button type="button" class="form-btn" style="background:#666;" onclick="hideManageForm()">Cancel</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="logo"><a href="index.php"><h1>Cars<span>Dekho</span></h1></a></div>
                    <div class="header-search">
                        <div class="search-wrapper">
                            <input type="text" id="mainSearchInput" class="search-input" placeholder="Search for cars..." aria-label="Search" autocomplete="off">
                            <div id="searchSuggestions" class="search-suggestions"></div>
                            <button class="search-btn"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="header-actions">
                        <div class="language-selector">
                            <select class="language-dropdown">
                                <option value="en">English</option><option value="hi">हिन्दी</option>
                            </select>
                        </div>
                        <button class="icon-btn wishlist"><i class="fas fa-heart"></i><span>Wishlist</span></button>
                        <?php if ($isLoggedIn): ?>
                            <div class="user-dropdown">
                                <button class="user-btn" onclick="toggleUserDropdown()">
                                    <div class="user-avatar">
                                        <?php if ($userProfileImage && file_exists('uploads/profiles/' . $userProfileImage)): ?>
                                            <img src="uploads/profiles/<?php echo htmlspecialchars($userProfileImage); ?>" alt="Profile">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($userName, 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($userName); ?></span>
                                    <span class="user-role-badge <?php echo $userRole; ?>"><?php echo strtoupper($userRole); ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-menu" id="userDropdown">
                                    <div class="dropdown-header">
                                        <div class="dropdown-user-name"><?php echo htmlspecialchars($userName); ?></div>
                                        <div class="dropdown-user-email"><?php echo htmlspecialchars($userEmail); ?></div>
                                    </div>
                                    <button class="dropdown-item" onclick="editProfile()"><i class="fas fa-user-edit"></i> Edit Profile</button>
                                    <?php if ($userRole === 'admin'): ?>
                                        <button class="dropdown-item" onclick="openManageDataModal()"><i class="fas fa-cog"></i> Manage Data</button>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
                                </div>
                            </div>
                        <?php else: ?>
                            <button class="icon-btn login" onclick="openAuthModal()"><i class="fas fa-user"></i> Login</button>
                        <?php endif; ?>
                        <button class="mobile-menu-toggle" id="mobileMenuToggle"><span></span><span></span><span></span></button>
                    </div>
                </div>
            </div>
        </div>
        <nav class="header-nav" id="headerNav">
            <div class="container">
                <div class="nav-content">
                    <ul class="nav-list">
                        <li class="nav-item"><a href="#" class="nav-link active">Home</a></li>
                        <li class="nav-item"><a href="#searched-cars" class="nav-link">Searched Cars</a></li>
                        <li class="nav-item"><a href="#latest-cars" class="nav-link">Latest Cars</a></li>
                        <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <section class="banner-section">
        <div class="banner-slider">
            <?php foreach($banners as $i => $b): ?>
                <div class="banner-slide <?php echo $i===0?'active':''; ?>">
                    <img src="<?php echo getImagePath($b['image'], 'uploads/banners/'); ?>" class="banner-image">
                    <div class="banner-overlay"></div>
                    <div class="banner-content">
                        <div class="container">
                            <h2 class="banner-title"><?php echo htmlspecialchars($b['title']); ?></h2>
                            <p class="banner-subtitle"><?php echo htmlspecialchars($b['subtitle']); ?></p>
                            <a href="<?php echo htmlspecialchars($b['link']); ?>" class="banner-btn"><?php echo htmlspecialchars($b['button_text']); ?> <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button class="banner-arrow prev" onclick="changeBannerSlide(-1)"><i class="fas fa-chevron-left"></i></button>
            <button class="banner-arrow next" onclick="changeBannerSlide(1)"><i class="fas fa-chevron-right"></i></button>
            
            <div class="banner-dots">
                <?php foreach($banners as $i => $b): ?>
                    <span class="dot <?php echo $i===0?'active':''; ?>" onclick="currentBannerSlide(<?php echo $i; ?>)"></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cars-section most-searched-section" id="searched-cars">
        <div class="container">
            <div class="section-header"><h2 class="section-title">Most Searched Cars</h2></div>
            <div class="category-tabs">
                <?php foreach(array_keys($carsByCategory) as $i => $cat): ?>
                    <button class="category-tab <?php echo $i===0?'active':''; ?>" onclick="showCategory('<?php echo strtolower($cat); ?>')"><?php echo $cat; ?></button>
                <?php endforeach; ?>
            </div>
            <?php foreach($carsByCategory as $cat => $cars): ?>
                <div class="category-content <?php echo $cat===array_key_first($carsByCategory)?'active':''; ?>" id="category-<?php echo strtolower($cat); ?>">
                    <div class="car-slider-container">
                        <button class="slider-nav-btn slider-prev" onclick="slideCarousel('<?php echo strtolower($cat); ?>', -1)"><i class="fas fa-chevron-left"></i></button>
                        <div class="car-slider">
                            <div class="car-slider-track" id="slider-<?php echo strtolower($cat); ?>">
                                <?php foreach($cars as $c): ?>
                                    <div class="car-card">
                                        <div class="car-image-wrapper"><img src="<?php echo getImagePath($c['image'], 'uploads/cars/'); ?>" class="car-image"></div>
                                        <div class="car-details"><h3 class="car-name"><?php echo $c['name']; ?></h3><div class="car-price"><?php echo formatPrice($c['price']); ?></div></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button class="slider-nav-btn slider-next" onclick="slideCarousel('<?php echo strtolower($cat); ?>', 1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cars-section latest-cars-section" id="latest-cars">
        <div class="container">
            <div class="section-header"><h2 class="section-title">Latest Cars</h2></div>
            <div class="car-slider-container">
                <button class="slider-nav-btn slider-prev" onclick="slideCarousel('latest', -1)"><i class="fas fa-chevron-left"></i></button>
                <div class="car-slider">
                    <div class="car-slider-track" id="slider-latest">
                        <?php foreach($latestCars as $c): ?>
                            <div class="car-card">
                                <div class="car-image-wrapper"><img src="<?php echo getImagePath($c['image'], 'uploads/cars/'); ?>" class="car-image"></div>
                                <div class="car-details"><h3 class="car-name"><?php echo $c['name']; ?></h3><div class="car-price"><?php echo formatPrice($c['price']); ?></div></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="slider-nav-btn slider-next" onclick="slideCarousel('latest', 1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <a href="#" class="footer-logo">Cars<span>Dekho</span></a>
                    <p class="footer-description">CarsDekho is India's leading car search venture that helps users buy cars that are right for them. We provide rich automotive content, including expert reviews, detailed specs and prices.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#searched-cars">Most Searched</a></li>
                        <li><a href="#latest-cars">Latest Launches</a></li>
                        <li><a href="#">Car Reviews</a></li>
                        <li><a href="#">Compare Cars</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-title">Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">EMI Calculator</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-title">Contact Us</h3>
                    <ul class="contact-info">
                        <li class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-text"><strong>Head Office</strong>Kirti Nagar, New Delhi - 110015</div>
                        </li>
                        <li class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                            <div class="contact-text"><strong>Phone</strong><a href="tel:+919876543210">+917870327329</a></div>
                        </li>
                        <li class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-text"><strong>Email</strong><a href="mailto:support@carsdekho.com">harshkrgupta7870@gmail.com</a></div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="copyright"><p>&copy; <?php echo date('Y'); ?> CarsDekho.com. All Rights Reserved.</p></div>
                
            </div>
        </div>
    </footer>

    <!-- Pass PHP data to JS -->
    <script>
        const allCarNames = <?php echo json_encode($allSearchNames); ?>;
    </script>
    <script src="script.js"></script>
    <script>const categories = ['SUV', 'Sedan', 'Hatchback', 'Luxury', 'MUV'];</script>
</body>
</html>