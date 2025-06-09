document.addEventListener('DOMContentLoaded', function () {
    const hamburgerButton = document.getElementById('hamburgerButton');
    const navLinks = document.getElementById('headerNavLinks'); // This is your .header-buttons div

    // Function to close the menu
    function closeMenu() {
        if (navLinks.classList.contains('mobile-menu-active')) {
            navLinks.classList.remove('mobile-menu-active');
            hamburgerButton.classList.remove('open');
            document.body.classList.remove('modal-abierto');
            hamburgerButton.setAttribute('aria-expanded', 'false');
        }
    }

    // Function to open/toggle the menu
    function toggleMenu() {
        navLinks.classList.toggle('mobile-menu-active');
        hamburgerButton.classList.toggle('open');
        document.body.classList.toggle('modal-abierto');

        const isExpanded = hamburgerButton.getAttribute('aria-expanded') === 'true' || false;
        hamburgerButton.setAttribute('aria-expanded', !isExpanded);

        if (navLinks.classList.contains('mobile-menu-active')) {
            const flashMessageInMenu = navLinks.querySelector('#flash-message');
            if (flashMessageInMenu && flashMessageInMenu.textContent.trim() !== '') {
                flashMessageInMenu.classList.add('show');
            }
        }
    }

    if (hamburgerButton && navLinks) {
        // Event listener for the hamburger button
        hamburgerButton.addEventListener('click', function (event) {
            event.stopPropagation(); // Prevent this click from being caught by the document listener immediately
            toggleMenu();
        });

        // Event listener to close menu when a link *inside* it is clicked
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function (event) {
                // event.stopPropagation(); // Optional: if you want to be very explicit
                closeMenu(); // Close menu on any internal link click
            });
        });

        // Event listener for clicks on the document to close menu if click is outside
        document.addEventListener('click', function (event) {
            // Check if the menu is open AND
            // if the click target is NOT the menu itself or a descendant of the menu AND
            // if the click target is NOT the hamburger button or a descendant of the hamburger button
            if (navLinks.classList.contains('mobile-menu-active') &&
                !navLinks.contains(event.target) &&
                event.target !== hamburgerButton && !hamburgerButton.contains(event.target)
            ) {
                closeMenu();
            }
        });

        // Optional: Close menu on 'Escape' key press
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && navLinks.classList.contains('mobile-menu-active')) {
                closeMenu();
            }
        });
    }

    // --- Optional: Flash Message Logic ---
    // (Ensure this is correctly placed if not handled by Blade's @push('scripts') in the HTML)
    // const flashMessage = document.getElementById('flash-message');
    // if (flashMessage && flashMessage.textContent.trim() !== '') {
    //     // Logic to show/hide
    // }
});