// =========================================================================
// Section: Imports & Setup
// =========================================================================
gsap.registerPlugin(ScrollTrigger);

// =========================================================================
// Section: Top Bar & Header / Navigation - Sticky Header on Scroll
// =========================================================================
document.addEventListener("DOMContentLoaded", function () {
  let isSticky = false;

  // Fonction pour activer le mode sticky
  const enableStickyHeader = () => {
    if (!document.querySelector(".sticky-header-container")) {
      const stickyContainer = document.createElement("div");
      stickyContainer.className = "sticky-header-container";
      const topBar = document.querySelector(".top-bar");
      const mainHeader = document.querySelector(".main-header");
      topBar.parentNode.insertBefore(stickyContainer, topBar);
      stickyContainer.appendChild(topBar);
      stickyContainer.appendChild(mainHeader);

      gsap.set(".sticky-header-container", {
        position: "fixed",
        top: 0,
        width: "100%",
        zIndex: 1001,
      });

      gsap.to(".main-header", {
        backgroundColor: "#FFFFFF",
        borderBottomColor: "#000000",
        duration: 0.5,
      });
      gsap.to(".main-nav .nav, .nav-right .icon-btn i", {
        color: "#000000",
        duration: 0.5,
      });
      gsap.to(".logo img", {
        filter: "none",
        duration: 0.5,
      });
    }
    isSticky = true;
  };

  // Fonction pour désactiver le mode sticky
  const disableStickyHeader = () => {
    const stickyContainer = document.querySelector(".sticky-header-container");
    if (stickyContainer) {
      const topBar = stickyContainer.querySelector(".top-bar");
      const mainHeader = stickyContainer.querySelector(".main-header");
      const parentElement = stickyContainer.parentNode;

      stickyContainer.removeChild(topBar);
      stickyContainer.removeChild(mainHeader);

      parentElement.insertBefore(topBar, stickyContainer);
      parentElement.insertBefore(mainHeader, stickyContainer);

      parentElement.removeChild(stickyContainer);
    }

    gsap.to(".main-header", {
      backgroundColor: "#948373",
      borderBottomColor: "#FFFF",
      duration: 0.5,
    });
    gsap.to(".main-nav .nav, .nav-right .icon-btn i", {
      color: "#FFFFFF",
      duration: 0.5,
    });
    gsap.to(".logo img", {
      filter: "invert(1)",
      duration: 0.5,
    });

    isSticky = false;
  };

  // Écouteur de défilement pour activer/désactiver le sticky header
  window.addEventListener("scroll", () => {
    if (window.scrollY > 0 && !isSticky) {
      enableStickyHeader();
    } else if (window.scrollY === 0 && isSticky) {
      disableStickyHeader();
    }

    // Gestion de la classe scrolled pour le header
    const header = document.querySelector(".main-header");
    if (window.scrollY > 0) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });

  // =========================================================================
  // Section: Header / Navigation - Gestion du menu mobile
  // =========================================================================
  const createMobileMenu = () => {
    const header = document.querySelector(".main-header");
    const nav = document.querySelector(".main-nav");

    const mobileMenuBtn = document.createElement("button");
    mobileMenuBtn.className = "mobile-menu-toggle";
    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';

    const closeMenuBtn = document.createElement("button");
    closeMenuBtn.className = "mobile-menu-close";
    closeMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeMenuBtn.style.display = "none";

    const mediaQuery = window.matchMedia("(max-width: 768px)");

    function handleScreenChange(e) {
      if (e.matches) {
        if (!document.querySelector(".mobile-menu-toggle")) {
          header.querySelector(".container").prepend(mobileMenuBtn);
          nav.classList.add("mobile");
          nav.prepend(closeMenuBtn);
        }
      } else {
        if (document.querySelector(".mobile-menu-toggle")) {
          mobileMenuBtn.remove();
          closeMenuBtn.remove();
          nav.classList.remove("mobile");
          nav.classList.remove("open");
        }
      }
    }
    handleScreenChange(mediaQuery);
    mediaQuery.addEventListener("change", handleScreenChange);

    mobileMenuBtn.addEventListener("click", function () {
      nav.classList.toggle("open");
      const isOpen = nav.classList.contains("open");
      this.innerHTML = isOpen
        ? '<i class="fas fa-bars"></i>'
        : '<i class="fas fa-bars"></i>';
      closeMenuBtn.style.display = isOpen ? "block" : "none";

      if (isOpen) {
        gsap.from(".main-nav.mobile.open .nav-list", {
          x: -50,
          opacity: 0,
          duration: 0.5,
          ease: "power2.out",
        });
      } else {
        gsap.to(".main-nav.mobile.open .nav-list", {
          x: 0,
          opacity: 1,
          duration: 0.5,
          ease: "power2.out",
        });
      }
    });
    closeMenuBtn.addEventListener("click", function () {
      nav.classList.remove("open");
      closeMenuBtn.style.display = "none";
      mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';

      gsap.to(".main-nav.mobile.open .nav-list", {
        x: 0,
        opacity: 1,
        duration: 0.5,
        ease: "power2.out",
      });
    });
  };
  createMobileMenu();

   // =========================================================================
  // Section: Product Detail Section
  // =========================================================================
  const initProductDetail = () => {
    const productDetailSection = document.querySelector(".product-detail");
    if (!productDetailSection) return;

    const mainImage = document.querySelector(".product-detail .main-image img");
    const thumbnails = document.querySelectorAll(".product-detail .thumbnail");
    const colorOptions = document.querySelectorAll(".product-detail .color-option");
    const productGallery = document.querySelector(".product-gallery");

    if (!mainImage || thumbnails.length === 0 || colorOptions.length === 0 || !productGallery) return;

    // Définition des filtres de couleur (identiques au SCSS)
    const colorFilters = {
      black: 'none',
      gray: 'grayscale(100%) brightness(80%)',
      brown: 'sepia(100%) hue-rotate(20deg) saturate(150%) brightness(90%)',
      white: 'brightness(120%) contrast(90%)',
      red: 'hue-rotate(0deg) saturate(200%) brightness(90%)',
      blue: 'hue-rotate(200deg) saturate(150%) brightness(90%)',
      green: 'hue-rotate(120deg) saturate(150%) brightness(90%)',
      yellow: 'hue-rotate(60deg) saturate(150%) brightness(110%)',
      pink: 'hue-rotate(330deg) saturate(120%) brightness(95%)',
      purple: 'hue-rotate(270deg) saturate(150%) brightness(90%)',
      orange: 'hue-rotate(40deg) saturate(150%) brightness(95%)',
      navy: 'hue-rotate(220deg) saturate(150%) brightness(80%)',
      beige: 'sepia(20%) hue-rotate(30deg) brightness(110%)',
      silver: 'grayscale(80%) brightness(110%)',
      gold: 'hue-rotate(50deg) saturate(150%) brightness(100%)'
    };

    // Fonction pour appliquer le filtre de couleur
    function applyColorFilter(color) {
      const filter = colorFilters[color] || 'none';
      const images = document.querySelectorAll(".product-gallery .main-image img, .product-gallery .thumbnail img");

      images.forEach(img => {
        // Transition fluide du filtre
        gsap.to(img, {
          duration: 0.3,
          ease: "power2.out",
          filter: filter,
        });
      });
    }

    // Fonction pour extraire la couleur depuis l'attribut data-color ou les classes CSS
    function extractColor(element) {
      // Priorité 1: attribut data-color
      const dataColor = element.getAttribute("data-color");
      if (dataColor) {
        return dataColor.split("|")[0];
      }

      // Priorité 2: classe CSS color-*
      const colorClass = Array.from(element.classList)
        .find(cls => cls.startsWith("color-"));
      if (colorClass) {
        return colorClass.replace("color-", "");
      }

      return null;
    }

    // Fonction pour mettre à jour la couleur de la galerie (pour compatibilité CSS)
    function updateGalleryColor(color) {
      if (!productGallery) return;

      // Supprimer toutes les classes de couleur existantes
      const existingColorClasses = Array.from(productGallery.classList)
        .filter(cls => cls.startsWith("color-"));
      existingColorClasses.forEach(cls => productGallery.classList.remove(cls));

      // Ajouter la nouvelle classe de couleur
      if (color) {
        productGallery.classList.add(`color-${color}`);
      }
    }

    // Gestion des clics sur les vignettes
    thumbnails.forEach((thumbnail) => {
      thumbnail.addEventListener("click", function () {
        // Mettre à jour l'état actif des vignettes
        thumbnails.forEach((t) => t.classList.remove("active"));
        this.classList.add("active");

        // Changer l'image principale
        const newSrc = this.querySelector("img").src;
        const thumbnailImg = this.querySelector("img");
        const dataColor = thumbnailImg ? extractColor(thumbnailImg) : null;

        gsap.to(mainImage, {
          opacity: 0,
          duration: 0.3,
          onComplete: () => {
            mainImage.src = newSrc;
            if (dataColor) {
              updateGalleryColor(dataColor);
              applyColorFilter(dataColor);
            }
            gsap.to(mainImage, {
              opacity: 1,
              duration: 0.3,
            });
          },
        });
      });
    });

    // Gestion des clics sur les options de couleur
    colorOptions.forEach((color) => {
      color.addEventListener("click", function () {
        // Mettre à jour l'état actif des couleurs
        colorOptions.forEach((c) => c.classList.remove("active"));
        this.classList.add("active");

        // Extraire la couleur sélectionnée
        const selectedColor = extractColor(this);

        if (selectedColor) {
          // Mettre à jour la classe CSS de la galerie
          updateGalleryColor(selectedColor);

          // Animation de transition
          gsap.to(".product-gallery .main-image img, .product-gallery .thumbnail img", {
            opacity: 0.7,
            duration: 0.2,
            onComplete: () => {
              // Appliquer le filtre de couleur
              applyColorFilter(selectedColor);

              // Restaurer l'opacité
              gsap.to(".product-gallery .main-image img, .product-gallery .thumbnail img", {
                opacity: 1,
                duration: 0.3,
              });
            },
          });
        }
      });
    });

    // Initialisation avec la couleur active par défaut
    const activeColor = document.querySelector(".color-option.active");
    if (activeColor) {
      const defaultColor = extractColor(activeColor);
      if (defaultColor) {
        updateGalleryColor(defaultColor);
        applyColorFilter(defaultColor);
      }
    }

    // Gestion du panier
    const addToCartBtn = document.querySelector(".product-detail .btn-cart");
    const cartCountElement = document.querySelector(".cart-count");

    if (addToCartBtn && cartCountElement) {
      let cartCount = parseInt(localStorage.getItem("cartCount")) || 0;
      cartCountElement.textContent = cartCount;

      addToCartBtn.addEventListener("click", function () {
        cartCount++;
        localStorage.setItem("cartCount", cartCount);

        gsap.to(cartCountElement, {
          duration: 0.3,
          scale: 1.2,
          onComplete: () => {
            cartCountElement.textContent = cartCount;
            gsap.to(cartCountElement, {
              duration: 0.3,
              scale: 1,
            });
          },
        });

        gsap.fromTo(
          this,
          { scale: 1 },
          { scale: 1.1, duration: 0.2, yoyo: true, repeat: 1 }
        );
      });
    }

    // Animations d'entrée
    gsap.from(".product-detail .product-gallery", {
      x: -50,
      opacity: 0,
      duration: 0.8,
      ease: "power2.out",
      scrollTrigger: {
        trigger: ".product-detail",
        start: "top 80%",
        toggleActions: "play none none none",
      },
    });

    gsap.from(".product-detail .product-info", {
      x: 50,
      opacity: 0,
      duration: 0.8,
      ease: "power2.out",
      scrollTrigger: {
        trigger: ".product-detail",
        start: "top 80%",
        toggleActions: "play none none none",
      },
    });
  };

  initProductDetail();


  // =========================================================================
  // Section: Featured Product Section
  // =========================================================================
  const initFeaturedSlider = () => {
    const featuredSlides = document.querySelectorAll(".featured-slide");
    const sliderContainer = document.querySelector(
      ".featured-product .slider-pagination"
    );

    if (featuredSlides.length === 0 || !sliderContainer) return;

    sliderContainer.innerHTML = `
    <div class="control-track">
      <div class="control-handle">
        <span class="control-arrow control-left"><i class="bi bi-chevron-left"></i></span>
        <span class="control-arrow control-right"><i class="bi bi-chevron-right"></i></span>
      </div>
    </div>
  `;

    const handle = sliderContainer.querySelector(".control-handle");
    const track = sliderContainer.querySelector(".control-track");
    const leftArrow = sliderContainer.querySelector(".control-left");
    const rightArrow = sliderContainer.querySelector(".control-right");

    let isDragging = false;
    let startY;
    let currentPosition = 0.5;

    featuredSlides.forEach((slide) => {
      gsap.set(slide, {
        opacity: 0.5,
        onComplete: () => {
          slide.classList.add("active");
        },
      });
    });

    const updateSlideOpacity = (position) => {
      const opacity = 1 - position;

      featuredSlides.forEach((slide) => {
        gsap.to(slide, {
          opacity: opacity,
          duration: 0.2,
          ease: "power2.out",
        });
      });
    };

    const startDrag = (e) => {
      if (
        e.target.classList.contains("control-arrow") ||
        e.target.tagName === "I"
      )
        return;

      isDragging = true;
      e.preventDefault();

      if (e.type === "touchstart") {
        startY = e.touches[0].clientY;
      } else {
        startY = e.clientY;
      }

      gsap.to(handle, {
        scale: 1.05,
        duration: 0.2,
        ease: "power2.out",
      });
    };

    const drag = (e) => {
      if (!isDragging) return;

      let trackRect = track.getBoundingClientRect();
      let currentY = e.type === "touchmove" ? e.touches[0].clientY : e.clientY;
      let position = (currentY - trackRect.top) / trackRect.height;

      position = Math.max(0, Math.min(position, 1));
      currentPosition = position;

      gsap.set(handle, {
        top: `${position * 100}%`,
        y: "-50%",
      });

      updateSlideOpacity(position);
    };

    const endDrag = () => {
      if (!isDragging) return;
      isDragging = false;

      gsap.to(handle, {
        scale: 1,
        duration: 0.2,
        ease: "power2.out",
      });
    };

    const moveLeft = () => {
      currentPosition = Math.max(0, currentPosition - 0.1);

      gsap.to(handle, {
        top: `${currentPosition * 100}%`,
        y: "-50%",
        duration: 0.3,
        ease: "power2.out",
      });

      updateSlideOpacity(currentPosition);
    };

    const moveRight = () => {
      currentPosition = Math.min(1, currentPosition + 0.1);

      gsap.to(handle, {
        top: `${currentPosition * 100}%`,
        y: "-50%",
        duration: 0.3,
        ease: "power2.out",
      });

      updateSlideOpacity(currentPosition);
    };

    handle.addEventListener("mousedown", startDrag);
    handle.addEventListener("touchstart", startDrag);

    window.addEventListener("mousemove", drag);
    window.addEventListener("touchmove", drag, { passive: false });

    window.addEventListener("mouseup", endDrag);
    window.addEventListener("touchend", endDrag);

    leftArrow.addEventListener("click", moveLeft);
    rightArrow.addEventListener("click", moveRight);

    gsap.set(handle, {
      top: "40%",
      y: "50%",
    });

    gsap.from(".featured-product .featured-title", {
      opacity: 0,
      y: 30,
      duration: 0.8,
      ease: "power2.out",
      scrollTrigger: {
        trigger: ".featured-product",
        start: "top 70%",
      },
    });

    gsap.from(".featured-product .btn-featured", {
      opacity: 0,
      y: 20,
      duration: 0.6,
      delay: 0.3,
      ease: "back.out(1.5)",
      scrollTrigger: {
        trigger: ".featured-product",
        start: "top 70%",
      },
    });

    gsap.from(".featured-product .featured-image img", {
      scale: 1.1,
      opacity: 0.8,
      duration: 1,
      ease: "power2.out",
      scrollTrigger: {
        trigger: ".featured-product",
        start: "top 70%",
      },
    });
  };

  initFeaturedSlider();

  // =========================================================================
  // Section: FAQ Section
  // =========================================================================
  const faqQuestions = document.querySelectorAll(".faq-question");

  faqQuestions.forEach((question) => {
    question.addEventListener("click", function () {
      const answer = this.nextElementSibling;
      const isActive = this.classList.contains("active");
      document.querySelectorAll(".faq-answer").forEach((a) => {
        a.style.display = "none";
      });
      document.querySelectorAll(".faq-question").forEach((q) => {
        q.classList.remove("active");
      });
      if (!isActive) {
        this.classList.add("active");
        answer.style.display = "block";
        gsap.from(answer, {
          opacity: 0,
          height: 0,
          duration: 0.3,
          ease: "power2.out",
        });
      }
    });
  });

  // =========================================================================
  // Section: Footer Section
  // =========================================================================

  const backToTopBtn = document.querySelector(".back-to-top");
  backToTopBtn.style.display = "none";
  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
      backToTopBtn.style.display = "block";
    } else {
      backToTopBtn.style.display = "none";
    }
  });
  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
});
