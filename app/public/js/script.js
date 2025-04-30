/**
 * Toggles the visibility of a password input field.
 * Finds the button with id="togglePassword" and the input with id="password".
 */
function setupPasswordToggle() {
    const togglePasswordButton = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');
    const eyeIcon = togglePasswordButton ? togglePasswordButton.querySelector('i') : null; // Get the icon inside the button

    if (togglePasswordButton && passwordInput && eyeIcon) {
        togglePasswordButton.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle the icon
            if (type === 'password') {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
    } else {
        // Optional: Log an error if elements aren't found
        // console.warn("Password toggle elements not found (#togglePassword, #password, or icon).");
    }
}

/**
 * Handles the mobile sidebar toggle functionality
 */
function setupMobileSidebar() {
    const sidebarToggleBtn = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.sidebar');
    
    // Create a backdrop element for mobile
    let backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
    
    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
        
        // Close sidebar when clicking on backdrop
        backdrop.addEventListener('click', function() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
        
        // Close sidebar when clicking on sidebar links on mobile
        const sidebarLinks = sidebar.querySelectorAll('.nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            });
        });
    }
}

// --- Run functions when the DOM is fully loaded ---
document.addEventListener('DOMContentLoaded', function() {
    setupPasswordToggle();
    setupMobileSidebar();

    // Add other global initializations here if needed
});