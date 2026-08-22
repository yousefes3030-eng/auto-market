// AutoMarket - Main JavaScript
// Handles all interactive functionality

document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navMenu');
    const navOverlay = document.getElementById('navOverlay');
    const navDrawerClose = document.getElementById('navDrawerClose');
    
    function openMobileNav() {
        if (!navMenu) return;
        navMenu.classList.add('active');
        document.body.classList.add('nav-open');
        if (mobileMenuToggle) {
            mobileMenuToggle.classList.add('is-open');
            mobileMenuToggle.setAttribute('aria-expanded', 'true');
            mobileMenuToggle.setAttribute('aria-label', 'Close menu');
        }
        if (navDrawerClose) {
            navDrawerClose.focus();
        }
    }
    
    function closeMobileNav(restoreFocus) {
        if (!navMenu || !navMenu.classList.contains('active')) return;
        navMenu.classList.remove('active');
        document.body.classList.remove('nav-open');
        if (mobileMenuToggle) {
            mobileMenuToggle.classList.remove('is-open');
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
            mobileMenuToggle.setAttribute('aria-label', 'Open menu');
            if (restoreFocus !== false && window.innerWidth <= 992) {
                mobileMenuToggle.focus();
            }
        }
    }
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (navMenu.classList.contains('active')) {
                closeMobileNav();
            } else {
                openMobileNav();
            }
        });
    }
    
    if (navOverlay) {
        navOverlay.addEventListener('click', closeMobileNav);
    }
    
    if (navDrawerClose) {
        navDrawerClose.addEventListener('click', closeMobileNav);
    }
    
    if (navMenu) {
        navMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', closeMobileNav);
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        closeMobileNav();
        document.querySelectorAll('.user-dropdown-menu.show').forEach(function(menu) {
            menu.classList.remove('show');
            if (menu.parentElement) menu.parentElement.classList.remove('open');
            const toggle = menu.parentElement && menu.parentElement.querySelector('.user-dropdown-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        });
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) closeMobileNav(false);
    });
    
    // User Dropdown Toggle
    const dropdownToggles = document.querySelectorAll('.user-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.parentElement;
            const menu = dropdown.querySelector('.user-dropdown-menu');
            const isOpen = menu.classList.toggle('show');
            dropdown.classList.toggle('open', isOpen);
            this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            
            document.querySelectorAll('.user-dropdown-menu.show').forEach(m => {
                if (m !== menu) {
                    m.classList.remove('show');
                    m.parentElement.classList.remove('open');
                }
            });
        });
    });
    
    document.addEventListener('click', function() {
        document.querySelectorAll('.user-dropdown-menu.show').forEach(m => {
            m.classList.remove('show');
            if (m.parentElement) m.parentElement.classList.remove('open');
        });
    });
    
    // Filters Toggle (Mobile)
    const filtersToggle = document.getElementById('filtersToggle');
    const filtersSidebar = document.getElementById('filtersSidebar');
    const filtersClose = document.getElementById('filtersClose');
    
    if (filtersToggle && filtersSidebar) {
        filtersToggle.addEventListener('click', function(e) {
            e.preventDefault();
            filtersSidebar.classList.toggle('active');
            document.body.classList.toggle('filters-open', filtersSidebar.classList.contains('active'));
        });
    }
    
    if (filtersClose && filtersSidebar) {
        filtersClose.addEventListener('click', function(e) {
            e.preventDefault();
            filtersSidebar.classList.remove('active');
            document.body.classList.remove('filters-open');
        });
    }

    const filtersOverlay = document.getElementById('filtersOverlay');
    if (filtersOverlay && filtersSidebar) {
        filtersOverlay.addEventListener('click', function() {
            filtersSidebar.classList.remove('active');
            document.body.classList.remove('filters-open');
        });
    }

    // Admin sidebar
    const adminSidebar = document.getElementById('adminSidebar');
    const adminOverlay = document.getElementById('adminOverlay');
    const adminMenuToggle = document.getElementById('adminMenuToggle');
    const adminSidebarClose = document.getElementById('adminSidebarClose');

    function closeAdminNav() {
        if (!adminSidebar) return;
        adminSidebar.classList.remove('open');
        document.body.classList.remove('admin-nav-open');
        if (adminMenuToggle) adminMenuToggle.setAttribute('aria-expanded', 'false');
    }

    function openAdminNav() {
        if (!adminSidebar) return;
        adminSidebar.classList.add('open');
        document.body.classList.add('admin-nav-open');
        if (adminMenuToggle) adminMenuToggle.setAttribute('aria-expanded', 'true');
    }

    if (adminMenuToggle) {
        adminMenuToggle.addEventListener('click', function() {
            if (adminSidebar && adminSidebar.classList.contains('open')) {
                closeAdminNav();
            } else {
                openAdminNav();
            }
        });
    }

    if (adminSidebarClose) {
        adminSidebarClose.addEventListener('click', closeAdminNav);
    }

    if (adminOverlay) {
        adminOverlay.addEventListener('click', closeAdminNav);
    }

    document.querySelectorAll('.admin-nav-item').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                closeAdminNav();
            }
        });
    });

    document.querySelectorAll('.demo-fill').forEach(button => {
        button.addEventListener('click', function() {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            if (email) email.value = this.dataset.email || '';
            if (password) password.value = this.dataset.password || '';
        });
    });
    
    // Broken image fallback
    const placeholder = (window.APP_URL || '') + '/assets/images/placeholder-car.jpg';
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            if (this.dataset.fallbackApplied) return;
            this.dataset.fallbackApplied = '1';
            this.src = placeholder;
        });
    });
    
    // Auto-hide flash messages
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation enhancements
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Price range inputs
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (minPriceInput && maxPriceInput) {
        minPriceInput.addEventListener('change', function() {
            if (maxPriceInput.value && parseFloat(this.value) > parseFloat(maxPriceInput.value)) {
                maxPriceInput.value = this.value;
            }
        });
        
        maxPriceInput.addEventListener('change', function() {
            if (minPriceInput.value && parseFloat(this.value) < parseFloat(minPriceInput.value)) {
                minPriceInput.value = this.value;
            }
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Image gallery keyboard navigation
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb) => {
        thumb.setAttribute('tabindex', '0');
        thumb.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.click();
            }
        });
    });
    
});

const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .error {
        border-color: var(--danger) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
`;
document.head.appendChild(style);
