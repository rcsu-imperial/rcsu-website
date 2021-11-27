const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobile-nav');
const handleHamburgerToggle = function() {
    hamburger.classList.toggle('open');
    mobileMenu.classList.toggle('open');
}

hamburger.onclick = () => handleHamburgerToggle();
hamburger.addEventListener('keyup', (event) => {
    if(event.key === 'Space' || event.key === 'Enter') {
        handleHamburgerToggle();
    }
});

/* Find all links in extended menu, on activation, close extended menu */
const linksInExtendedMobileMenu = document.getElementsByClassName('extended-links');
Array.from(linksInExtendedMobileMenu).forEach(function(link) {
    link.addEventListener('click', handleHamburgerToggle);
    link.addEventListener('keyup', (event) => {
        if (event.key === 'Space' || event.key === 'Enter') {
            handleHamburgerToggle();
        }
    });
});

