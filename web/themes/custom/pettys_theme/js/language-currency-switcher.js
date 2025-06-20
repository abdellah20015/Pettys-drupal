(function (Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.languageCurrencySwitcher = {
    attach: function (context, settings) {
      const languageSwitcher = once('language-switcher', '#language-switcher', context)[0];
      const currencySwitcher = once('currency-switcher', '#currency-switcher', context)[0];
      const loadingIndicator = document.getElementById('switcher-loading');

      if (languageSwitcher) {
        languageSwitcher.addEventListener('change', function() {
          handleLanguageChange(this.value);
        });
      }

      if (currencySwitcher) {
        currencySwitcher.addEventListener('change', function() {
          handleCurrencyChange(this.value);
        });
      }

      function showLoading() {
        if (loadingIndicator) {
          loadingIndicator.style.display = 'block';
        }
      }

      function hideLoading() {
        if (loadingIndicator) {
          loadingIndicator.style.display = 'none';
        }
      }

      function handleLanguageChange(languageCode) {
        showLoading();

        // Créer la nouvelle URL avec le préfixe de langue
        const currentPath = window.location.pathname;
        const currentSearch = window.location.search;

        // Supprimer l'ancien préfixe de langue s'il existe
        const pathWithoutLang = currentPath.replace(/^\/[a-z]{2}(\/|$)/, '/');

        // Construire la nouvelle URL
        let newPath;
        if (languageCode === 'en' || languageCode === drupalSettings.path.currentLanguage) {
          // Si c'est la langue par défaut, ne pas ajouter de préfixe
          newPath = pathWithoutLang === '/' ? '/' : pathWithoutLang;
        } else {
          newPath = '/' + languageCode + (pathWithoutLang === '/' ? '' : pathWithoutLang);
        }

        // Redirection
        window.location.href = newPath + currentSearch;
      }

      function handleCurrencyChange(currencyCode) {
        showLoading();

        // Faire une requête AJAX pour changer la devise
        fetch('/api/change-currency', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            currency: currencyCode
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Recharger la page pour mettre à jour les prix
            window.location.reload();
          } else {
            console.error('Erreur lors du changement de devise:', data.message);
            hideLoading();
          }
        })
        .catch(error => {
          console.error('Erreur réseau:', error);
          hideLoading();
        });
      }
    }
  };

})(Drupal, drupalSettings, once);
