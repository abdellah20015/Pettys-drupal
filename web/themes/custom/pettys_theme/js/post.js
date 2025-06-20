(function() {
  'use strict';

  // Fonction pour éviter l'exécution multiple
  function processOnce(element) {
    if (element.dataset.processed) {
      return false;
    }
    element.dataset.processed = 'true';
    return true;
  }

  function socialPostImagesFix() {
    const targetBlock = document.querySelector('#block-pettys-theme-social-post-images-block');

    if (!targetBlock || !processOnce(targetBlock)) {
      return;
    }

    // Chercher le conteneur d'images (équivalent de div > div:eq(1))
    const blockChildren = targetBlock.children;
    if (blockChildren.length < 1) return;

    const firstDiv = blockChildren[0];
    const imagesContainer = firstDiv.children.length > 1 ? firstDiv.children[1] : null;

    if (!imagesContainer) return;

    // Créer le nouveau conteneur
    const newContainer = document.createElement('div');
    newContainer.className = 'social-post-images';

    // Chercher toutes les régions contextuelles
    const contextualRegions = imagesContainer.querySelectorAll('div > div.contextual-region');

    contextualRegions.forEach(function(region) {
      // Chercher l'image dans div > div:last-child img
      const divs = region.children;
      if (divs.length > 0) {
        const lastDiv = divs[divs.length - 1];
        const img = lastDiv.querySelector('img');

        if (img) {
          const imageItem = document.createElement('div');
          imageItem.className = 'image-item';
          imageItem.appendChild(img.cloneNode(true));
          newContainer.appendChild(imageItem);
        }
      }
    });

    // Remplacer le contenu
    imagesContainer.innerHTML = '';
    imagesContainer.appendChild(newContainer);
  }

  // Exécuter au chargement du DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', socialPostImagesFix);
  } else {
    socialPostImagesFix();
  }

  // Observer les changements DOM pour les contenus chargés dynamiquement
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        socialPostImagesFix();
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });

})();
