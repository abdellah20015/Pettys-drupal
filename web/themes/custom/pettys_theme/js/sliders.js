document.addEventListener('DOMContentLoaded', function() {
    // =========================================================================
    // Brand Partners Slider (Theme)
    // =========================================================================
    initBrandPartnersSlider();

    function initBrandPartnersSlider() {
        // Sélecteurs spécifiques à la section Brand Partners
        const brandSection = document.querySelector('.brand-partners');
        if (!brandSection) {
            console.log('Section Brand Partners non trouvée');
            return;
        }

        const wrapper = brandSection.querySelector('.brands-wrapper');
        const prevBtn = brandSection.querySelector('.brands-slider .slider-nav.prev');
        const nextBtn = brandSection.querySelector('.brands-slider .slider-nav.next');

        if (!wrapper) {
            console.error('Élément wrapper non trouvé dans Brand Partners');
            return;
        }

        const items = wrapper.querySelectorAll('.brand-item');

        if (!prevBtn || !nextBtn || items.length === 0) {
            console.error('Éléments du Brand Partners slider non trouvés');
            console.log('Items trouvés:', items.length);
            console.log('Prev button:', !!prevBtn);
            console.log('Next button:', !!nextBtn);
            return;
        }

        let visibleItems = items.length;

        function updateButtonStates() {
            // Bouton précédent - désactivé quand tous les éléments sont visibles
            if (visibleItems === items.length) {
                prevBtn.classList.add('disabled');
                prevBtn.disabled = true;
            } else {
                prevBtn.classList.remove('disabled');
                prevBtn.disabled = false;
            }

            // Bouton suivant - désactivé quand il ne reste qu'un élément visible
            if (visibleItems === 1) {
                nextBtn.classList.add('disabled');
                nextBtn.disabled = true;
            } else {
                nextBtn.classList.remove('disabled');
                nextBtn.disabled = false;
            }
        }

        // Event listener pour le bouton suivant (cacher des éléments)
        nextBtn.addEventListener('click', function() {
            console.log("Brand Partners Next button clicked, visibleItems:", visibleItems);
            if (visibleItems > 1) {
                const elementsToHide = Array.from(items).filter(item =>
                    item.style.display !== 'none' && getComputedStyle(item).display !== 'none'
                );
                if (elementsToHide.length > 0) {
                    elementsToHide[0].style.display = 'none';
                    visibleItems--;
                    updateButtonStates();
                }
            }
        });

        // Event listener pour le bouton précédent (afficher des éléments)
        prevBtn.addEventListener('click', function() {
            console.log("Brand Partners Prev button clicked, visibleItems:", visibleItems);
            if (visibleItems < items.length) {
                const elementsHidden = Array.from(items).filter(item =>
                    item.style.display === 'none' || getComputedStyle(item).display === 'none'
                );

                if (elementsHidden.length > 0) {
                    const elementToShow = elementsHidden[elementsHidden.length - 1];
                    elementToShow.style.display = '';
                    visibleItems++;
                    updateButtonStates();
                }
            }
        });

        // Initialiser l'état des boutons
        updateButtonStates();

        console.log('Brand Partners slider initialisé avec', items.length, 'marques');
    }

    // =========================================================================
    // Fonction générique pour d'autres sliders du thème (si nécessaire)
    // =========================================================================
    function initOtherSliders() {
        // Placeholder pour d'autres sliders du thème
        // Vous pouvez ajouter d'autres sliders ici si nécessaire
    }
});
