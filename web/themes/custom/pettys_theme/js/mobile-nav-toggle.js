document.addEventListener('DOMContentLoaded', function() {
  const dropdownItems = document.querySelectorAll('.main-nav.mobile .nav-item.has-dropdown');

  dropdownItems.forEach(item => {
    const link = item.querySelector('a.nav');
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const dropdownMenu = item.querySelector('.dropdown-menu');

      
      dropdownItems.forEach(otherItem => {
        if (otherItem !== item) {
          otherItem.querySelector('.dropdown-menu').classList.remove('open');
        }
      });

      dropdownMenu.classList.toggle('open');
    });
  });
});