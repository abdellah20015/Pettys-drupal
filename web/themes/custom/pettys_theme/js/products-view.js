document.addEventListener('DOMContentLoaded', function() {

  function generateStars(rating) {
    return '★'.repeat(Math.floor(rating)) + '☆'.repeat(5 - Math.floor(rating));
  }

  document.querySelectorAll('.views-col').forEach(function(column) {
    const hasPrice = column.querySelector('.views-field-field-prix-actuel .field-content').textContent.trim();
    const hasOldPrice = column.querySelector('.views-field-field-ancien-prix .field-content').textContent.trim();
    const hasRating = column.querySelector('.views-field-field-evaluation .field-content').textContent.trim();

    const elements = {
      image: column.querySelector('.views-field-field-image-du-produit'),
      title: column.querySelector('.views-field-title'),
      oldPrice: column.querySelector('.views-field-field-ancien-prix'),
      currentPrice: column.querySelector('.views-field-field-prix-actuel'),
      rating: column.querySelector('.views-field-field-evaluation')
    };

    if (hasPrice || hasOldPrice || hasRating) {
      // Carte complète
      const imageContainer = document.createElement('div');
      imageContainer.className = 'image-container';
      imageContainer.appendChild(elements.image);

      const cardContent = document.createElement('div');
      cardContent.className = 'card-content';
      cardContent.appendChild(elements.title);

      if (hasPrice || hasOldPrice) {
        const priceContainer = document.createElement('div');
        priceContainer.className = 'price-container';

        if (hasPrice) {
          elements.currentPrice.querySelector('.field-content').textContent = hasPrice + ' $';
          priceContainer.appendChild(elements.currentPrice);
        }

        if (hasOldPrice) {
          elements.oldPrice.querySelector('.field-content').textContent = hasOldPrice + ' $';
          priceContainer.appendChild(elements.oldPrice);
        }

        cardContent.appendChild(priceContainer);
      }

      if (hasRating) {
        const rating = parseFloat(hasRating) || 5;
        const ratingContainer = document.createElement('div');
        ratingContainer.className = 'rating-container';
        ratingContainer.innerHTML = `
          <div class="stars">${generateStars(rating)}</div>
          <span class="rating-text">(${rating}/5)</span>
        `;
        cardContent.appendChild(ratingContainer);
        elements.rating.style.display = 'none';
      }

      column.innerHTML = '';
      column.appendChild(imageContainer);
      column.appendChild(cardContent);
      column.classList.add('product-card-complete');

    } else {
      // Carte simple
      const imageContainer = document.createElement('div');
      imageContainer.className = 'image-container';
      imageContainer.appendChild(elements.image);

      const cardContent = document.createElement('div');
      cardContent.className = 'card-content';
      cardContent.appendChild(elements.title);

      column.innerHTML = '';
      column.appendChild(imageContainer);
      column.appendChild(cardContent);
      column.classList.add('product-card-simple');
    }
  });

  // Animations
  document.querySelectorAll('.product-card-complete, .product-card-simple').forEach((card, i) => {
    card.style.animationDelay = `${i * 0.1}s`;
  });
});
