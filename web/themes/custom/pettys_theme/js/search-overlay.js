(function($) {
  $(document).ready(function() {
    // Éléments du DOM
    var searchTrigger = $('.js-search-trigger');
    var searchOverlay = $('.search-bar-overlay');
    var searchClose = $('.js-search-close');
    var searchInput = searchOverlay.find('input[type="search"], input[name="keys"]');

    // Ouvrir la barre de recherche
    searchTrigger.on('click', function(e) {
      e.preventDefault();
      searchOverlay.addClass('active');
      setTimeout(function() {
        searchInput.focus();
      }, 300);
    });

    // Fermer la barre de recherche
    searchClose.on('click', function() {
      searchOverlay.removeClass('active');
    });

    // Fermer avec Escape
    $(document).on('keydown', function(e) {
      if (e.key === 'Escape' && searchOverlay.hasClass('active')) {
        searchOverlay.removeClass('active');
      }
    });
  });
})(jQuery);
