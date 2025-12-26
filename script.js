function openAuthModal() {
    document.getElementById('authModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    document.getElementById('authModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    clearAuthAlert();
}

function switchAuthTab(tab) {
    const tabs = document.querySelectorAll('.auth-tab');
    const forms = document.querySelectorAll('.auth-form');
    
    tabs.forEach(t => t.classList.remove('active'));
    forms.forEach(f => f.classList.remove('active'));
    
    if (tab === 'login') {
        tabs[0].classList.add('active');
        document.getElementById('loginForm').classList.add('active');
    } else {
        tabs[1].classList.add('active');
        document.getElementById('registerForm').classList.add('active');
    }
    
    clearAuthAlert();
}

function showAuthAlert(message, type) {
    const alert = document.getElementById('authAlert');
    const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
    alert.className = `alert alert-${type}`;
    alert.innerHTML = icon + ' ' + message;
    alert.style.display = 'flex';
}

function clearAuthAlert() {
    document.getElementById('authAlert').style.display = 'none';
}

async function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'login');
    
    const submitBtn = form.querySelector('.form-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing in...';
    
    try {
        const response = await fetch('index.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAuthAlert(result.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAuthAlert(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
        }
    } catch (error) {
        showAuthAlert('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign In';
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'register');
    
    const submitBtn = form.querySelector('.form-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating account...';
    
    try {
        const response = await fetch('index.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAuthAlert(result.message, 'success');
            form.reset();
            setTimeout(() => switchAuthTab('login'), 2000);
        } else {
            showAuthAlert(result.message, 'error');
        }
        
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Account';
    } catch (error) {
        showAuthAlert('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Account';
    }
}

function toggleUserDropdown() {
    document.getElementById('userDropdown').classList.toggle('active');
}

function editProfile() {
    toggleUserDropdown();
    openEditProfileModal();
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'index.php?logout=1';
    }
}

document.addEventListener('click', function(e) {
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown && !userDropdown.contains(e.target)) {
        document.getElementById('userDropdown')?.classList.remove('active');
    }
});

document.getElementById('authModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAuthModal();
});

function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    clearProfileAlert();
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    clearProfileAlert();
    document.getElementById('editProfileForm').reset();
}

function showProfileAlert(message, type) {
    const alert = document.getElementById('profileAlert');
    const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
    alert.className = `alert alert-${type}`;
    alert.innerHTML = icon + ' ' + message;
    alert.style.display = 'flex';
}

function clearProfileAlert() {
    document.getElementById('profileAlert').style.display = 'none';
}

document.getElementById('profileImageInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showProfileAlert('Please select a valid image (JPG, PNG, or GIF)', 'error');
            e.target.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showProfileAlert('Image size must be less than 5MB', 'error');
            e.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImage').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password');
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

async function handleUpdateProfile(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'update_profile');

    const submitBtn = form.querySelector('.form-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    try {
        const response = await fetch('index.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showProfileAlert(result.message, 'success');
            
            const userNameElements = document.querySelectorAll('.user-btn span:first-of-type, .dropdown-user-name');
            userNameElements.forEach(el => el.textContent = result.full_name);

            const userEmailElements = document.querySelectorAll('.dropdown-user-email');
            userEmailElements.forEach(el => el.textContent = result.email);

            if (result.profile_image) {
                const avatarElements = document.querySelectorAll('.user-avatar');
                avatarElements.forEach(el => {
                    el.innerHTML = `<img src="uploads/profiles/${result.profile_image}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                });
            }

            form.querySelector('[name="current_password"]').value = '';
            form.querySelector('[name="new_password"]').value = '';
            form.querySelector('[name="confirm_password"]').value = '';

            setTimeout(() => {
                closeEditProfileModal();
                window.location.reload();
            }, 2000);
        } else {
            showProfileAlert(result.message, 'error');
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    } catch (error) {
        showProfileAlert('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

document.getElementById('editProfileModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditProfileModal();
    }
});

function openManageDataModal() {
    document.getElementById('manageDataModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeManageDataModal() {
    document.getElementById('manageDataModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    hideManageForm();
}

function toggleAccordion(sectionId) {
    const content = document.getElementById(sectionId);
    const header = content.previousElementSibling;
    
    if (content.classList.contains('show')) {
        content.classList.remove('show');
        header.classList.remove('active');
    } else {
        document.querySelectorAll('.accordion-content').forEach(el => el.classList.remove('show'));
        document.querySelectorAll('.accordion-header').forEach(el => el.classList.remove('active'));
        
        content.classList.add('show');
        header.classList.add('active');
    }
}

function showAddForm(type) {
    document.getElementById('manageFormContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Add New Item';
    document.getElementById('formOperation').value = 'add';
    document.getElementById('formType').value = type;
    document.getElementById('formItemId').value = '';
    
    generateFormFields(type, null);
    document.querySelector('#manageDataModal .modal-content').scrollTo({ top: document.querySelector('#manageDataModal .modal-content').scrollHeight, behavior: 'smooth' });
}

function openEditForm(type, data) {
    document.getElementById('manageFormContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Edit Item';
    document.getElementById('formOperation').value = 'update';
    document.getElementById('formType').value = type;
    document.getElementById('formItemId').value = data.id;
    
    generateFormFields(type, data);
    
    document.querySelector('#manageDataModal .modal-content').scrollTo({ top: document.querySelector('#manageDataModal .modal-content').scrollHeight, behavior: 'smooth' });
}

function hideManageForm() {
    document.getElementById('manageFormContainer').style.display = 'none';
    document.getElementById('dynamicManageForm').reset();
}

function generateFormFields(type, data) {
    const container = document.getElementById('formFields');
    container.innerHTML = '';
    
    if (type === 'banner') {
        container.innerHTML += createInput('Title', 'title', data ? data.title : '');
        container.innerHTML += createInput('Subtitle', 'subtitle', data ? data.subtitle : '');
        container.innerHTML += createInput('Button Text', 'button_text', data ? data.button_text : '');
    } 
    else if (type === 'searched_cars') {
        container.innerHTML += createInput('Car Name', 'name', data ? data.name : '');
        container.innerHTML += createInput('Model', 'model', data ? data.model : '');
        container.innerHTML += createInput('Price', 'price', data ? data.price : '', 'number');
        
        let catHtml = `<div class="form-group"><label class="form-label">Category</label><select name="category" class="form-select">`;
        ['SUV', 'Sedan', 'Hatchback', 'Luxury', 'MUV'].forEach(cat => {
            const selected = (data && data.category === cat) ? 'selected' : '';
            catHtml += `<option value="${cat}" ${selected}>${cat}</option>`;
        });
        catHtml += `</select></div>`;
        container.innerHTML += catHtml;
    } 
    else if (type === 'latest_cars') {
        container.innerHTML += createInput('Car Name', 'name', data ? data.name : '');
        container.innerHTML += createInput('Price', 'price', data ? data.price : '', 'number');
    }
}

function createInput(label, name, value, type='text') {
    return `<div class="form-group">
                <label class="form-label">${label}</label>
                <input type="${type}" name="${name}" class="form-input" value="${value}" required>
            </div>`;
}

async function handleManageSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const btn = form.querySelector('button[type="submit"]');
    const oldText = btn.innerText;
    btn.innerText = 'Saving...'; btn.disabled = true;

    try {
        const res = await fetch('index.php', { method: 'POST', body: formData });
        const json = await res.json();
        
        if (json.success) {
            alert(json.message);
            window.location.reload(); 
        } else {
            alert('Error: ' + json.message);
        }
    } catch (err) {
        alert('Request failed');
    }
    btn.innerText = oldText; btn.disabled = false;
}

async function deleteItem(type, id) {
    if (!confirm('Are you sure you want to delete this item?')) return;
    
    const formData = new FormData();
    formData.append('action', 'manage_data');
    formData.append('operation', 'delete');
    formData.append('type', type);
    formData.append('item_id', id);

    try {
        const res = await fetch('index.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
            window.location.reload();
        } else {
            alert('Delete failed: ' + json.message);
        }
    } catch (err) {
        alert('Request error');
    }
}

const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const headerNav = document.getElementById('headerNav');

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileMenuToggle.classList.toggle('active');
        headerNav.classList.toggle('active');
    });
}

document.addEventListener('click', (e) => {
    if (headerNav.classList.contains('active') && !headerNav.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
        headerNav.classList.remove('active');
        mobileMenuToggle.classList.remove('active');
    }
});

let currentBannerIndex = 0;
const bannerSlides = document.querySelectorAll('.banner-slide');
const bannerDots = document.querySelectorAll('.banner-dots .dot');
let bannerInterval;

function showBannerSlide(index) {
    if (bannerSlides.length === 0) return;
    if (index >= bannerSlides.length) currentBannerIndex = 0;
    else if (index < 0) currentBannerIndex = bannerSlides.length - 1;
    else currentBannerIndex = index;

    bannerSlides.forEach(slide => slide.classList.remove('active'));
    bannerDots.forEach(dot => dot.classList.remove('active'));

    if (bannerSlides[currentBannerIndex]) bannerSlides[currentBannerIndex].classList.add('active');
    if (bannerDots[currentBannerIndex]) bannerDots[currentBannerIndex].classList.add('active');
}

function changeBannerSlide(direction) {
    showBannerSlide(currentBannerIndex + direction);
    resetBannerInterval();
}

function currentBannerSlide(index) {
    showBannerSlide(index);
    resetBannerInterval();
}

function startBannerSlideShow() {
    if (bannerSlides.length > 1) {
        bannerInterval = setInterval(() => showBannerSlide(currentBannerIndex + 1), 5000);
    }
}

function resetBannerInterval() {
    clearInterval(bannerInterval);
    startBannerSlideShow();
}

if (bannerSlides.length > 0) {
    showBannerSlide(0);
    startBannerSlideShow();
}

function showCategory(categoryName) {
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.toggle('active', tab.textContent.toLowerCase() === categoryName.toLowerCase());
    });

    document.querySelectorAll('.category-content').forEach(content => {
        content.classList.toggle('active', content.id === 'category-' + categoryName);
    });

    const slider = document.getElementById('slider-' + categoryName);
    if (slider) {
        slider.style.transform = 'translateX(0)';
        sliderPositions[categoryName] = 0;
    }
}

const sliderPositions = {};

function slideCarousel(categoryName, direction) {
    const slider = document.getElementById('slider-' + categoryName);
    if (!slider) return;

    const cards = slider.querySelectorAll('.car-card');
    if (cards.length === 0) return;

    if (sliderPositions[categoryName] === undefined) sliderPositions[categoryName] = 0;

    const cardWidth = cards[0].offsetWidth;
    const gap = 25;
    const moveDistance = cardWidth + gap;
    const maxScroll = -(cards.length - 4) * moveDistance;

    let newPosition = sliderPositions[categoryName] + (direction * -moveDistance);

    if (newPosition > 0) newPosition = 0;
    else if (newPosition < maxScroll) newPosition = maxScroll;

    slider.style.transform = `translateX(${newPosition}px)`;
    sliderPositions[categoryName] = newPosition;
}

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

const mainSearchInput = document.getElementById('mainSearchInput');
const searchSuggestions = document.getElementById('searchSuggestions');

if (mainSearchInput && typeof allCarNames !== 'undefined') {
    mainSearchInput.addEventListener('input', function(e) {
        const val = e.target.value.toLowerCase();
        searchSuggestions.innerHTML = '';
        
        if (val.length > 0) {
            const matches = allCarNames.filter(car => car.toLowerCase().includes(val));
            if (matches.length > 0) {
                searchSuggestions.classList.add('active');
                matches.forEach(match => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.textContent = match;
                    div.onclick = function() {
                        mainSearchInput.value = match;
                        searchSuggestions.classList.remove('active');
                    };
                    searchSuggestions.appendChild(div);
                });
            } else {
                searchSuggestions.classList.remove('active');
            }
        } else {
            searchSuggestions.classList.remove('active');
        }
    });

    document.addEventListener('click', function(e) {
        if (!mainSearchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.classList.remove('active');
        }
    });
}

console.log('CarsDekho - All scripts loaded successfully!');