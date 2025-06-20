document.addEventListener('DOMContentLoaded', function() {
    const heroSlider = {
        slider: document.querySelector('.hero-slider-wrapper'),
        slides: document.querySelectorAll('.hero-slide'),
        dots: document.querySelectorAll('.hero-dots .dot'),
        currentSlide: 0,
        autoPlayInterval: null,
        autoPlayDelay: 5000,
        isAutoPlaying: true,

        init() {
            if (!this.slider || this.slides.length === 0) {
                console.warn('Hero slider: Aucun slider trouvé');
                return;
            }

            this.setupEventListeners();
            this.startAutoPlay();

            this.preloadImages();

            console.log(`Hero slider initialisé avec ${this.slides.length} slides`);
        },

        setupEventListeners() {
            this.dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    this.goToSlide(index);
                    this.pauseAutoPlay();
                    this.resumeAutoPlayAfterDelay();
                });
            });

            this.slider.addEventListener('mouseenter', () => {
                this.pauseAutoPlay();
            });

            this.slider.addEventListener('mouseleave', () => {
                if (this.isAutoPlaying) {
                    this.startAutoPlay();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    this.previousSlide();
                    this.pauseAutoPlay();
                    this.resumeAutoPlayAfterDelay();
                } else if (e.key === 'ArrowRight') {
                    this.nextSlide();
                    this.pauseAutoPlay();
                    this.resumeAutoPlayAfterDelay();
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseAutoPlay();
                } else if (this.isAutoPlaying) {
                    this.startAutoPlay();
                }
            });
        },

        goToSlide(index) {
            if (index < 0 || index >= this.slides.length) {
                return;
            }

            // Désactiver le slide et dot actuels
            this.slides[this.currentSlide].classList.remove('active');
            this.dots[this.currentSlide].classList.remove('active');

            // Activer le nouveau slide et dot
            this.currentSlide = index;
            this.slides[this.currentSlide].classList.add('active');
            this.dots[this.currentSlide].classList.add('active');

            console.log(`Slide actuel: ${this.currentSlide + 1}/${this.slides.length}`);
        },

        nextSlide() {
            const nextIndex = (this.currentSlide + 1) % this.slides.length;
            this.goToSlide(nextIndex);
        },

        previousSlide() {
            const prevIndex = this.currentSlide === 0 ? this.slides.length - 1 : this.currentSlide - 1;
            this.goToSlide(prevIndex);
        },

        startAutoPlay() {
            if (this.slides.length <= 1) {
                return; // Pas d'auto-play s'il n'y a qu'un slide
            }

            this.pauseAutoPlay(); // S'assurer qu'il n'y a pas d'interval en cours

            this.autoPlayInterval = setInterval(() => {
                this.nextSlide();
            }, this.autoPlayDelay);
        },

        pauseAutoPlay() {
            if (this.autoPlayInterval) {
                clearInterval(this.autoPlayInterval);
                this.autoPlayInterval = null;
            }
        },

        resumeAutoPlayAfterDelay(delay = 3000) {
            setTimeout(() => {
                if (this.isAutoPlaying) {
                    this.startAutoPlay();
                }
            }, delay);
        },

        toggleAutoPlay() {
            this.isAutoPlaying = !this.isAutoPlaying;

            if (this.isAutoPlaying) {
                this.startAutoPlay();
            } else {
                this.pauseAutoPlay();
            }
        },

        preloadImages() {
            // Précharger les images de fond pour une transition plus fluide
            this.slides.forEach(slide => {
                const bgElement = slide.querySelector('.hero-background');
                if (bgElement) {
                    const bgImage = bgElement.style.background;
                    const urlMatch = bgImage.match(/url\(['"]?([^'"]+)['"]?\)/);
                    if (urlMatch) {
                        const img = new Image();
                        img.src = urlMatch[1];
                    }
                }
            });
        },

        // Méthode publique pour contrôler le slider depuis l'extérieur
        setAutoPlayDelay(delay) {
            this.autoPlayDelay = delay;
            if (this.autoPlayInterval) {
                this.startAutoPlay();
            }
        },

        // Méthode pour détruire le slider (utile pour SPA)
        destroy() {
            this.pauseAutoPlay();
            // Nettoyer les event listeners si nécessaire
        }
    };

    // Initialiser le slider
    heroSlider.init();

    // Exposer le slider globalement pour pouvoir le contrôler depuis l'extérieur
    window.heroSlider = heroSlider;
});

// Fonction utilitaire pour redémarrer le slider (utile pour le développement)
function restartHeroSlider() {
    if (window.heroSlider) {
        window.heroSlider.destroy();
        setTimeout(() => {
            window.heroSlider.init();
        }, 100);
    }
}
