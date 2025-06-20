<?php

use Drupal\pettys_commerce\CurrencyConverter;

/**
 * @file
 * Preprocess functions for block templates in Pettys Theme.
 */

/**
 * Implements hook_preprocess_block().
 */
function pettys_theme_preprocess_block(array &$variables) {
  $block_label = $variables['elements']['#configuration']['label'] ?? '';
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;

  if ($block_label === 'Promo Top Bar') {
    $content = $variables['elements']['content']['#block_content'];
    $variables['promo_text'] = $content->get('field_texte')->value ?? '';
    $variables['button_text'] = $content->get('field_bouton')->value ?? '';
    $variables['button_url'] = $content->get('field_url')->isEmpty() ? '' : $content->get('field_url')->first()->getUrl()->toString();
  }

  if ($block_label === 'Language and Currency') {
    // Gestion des langues
    $language_manager = \Drupal::languageManager();
    $languages = $language_manager->getLanguages();
    $language_options = [];
    foreach ($languages as $lang_code => $language) {
      $language_options[$lang_code] = $language->getName();
    }
    $current_language = $language_manager->getCurrentLanguage()->getId();

    $variables['language_options'] = $language_options;
    $variables['current_language'] = array_key_exists($current_language, $language_options) ? $current_language : array_key_first($language_options);

    // Gestion des devises
    $currency_options = [];
    if (\Drupal::moduleHandler()->moduleExists('commerce_price')) {
      $currency_storage = \Drupal::entityTypeManager()->getStorage('commerce_currency');
      $currencies = $currency_storage->loadByProperties(['status' => TRUE]);
      foreach ($currencies as $currency) {
        $currency_options[$currency->getCurrencyCode()] = $currency->getName();
      }
    }

    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');
    if (!array_key_exists($current_currency, $currency_options)) {
      $current_currency = array_key_first($currency_options);
    }

    $variables['currency_options'] = $currency_options;
    $variables['current_currency'] = $current_currency;

    // Attacher la bibliothèque JavaScript
    $variables['#attached']['library'][] = 'pettys_theme/language-currency-switcher';
  }


  if ($block_content) {
    $bundle = $block_content->bundle();
    $preprocess_functions = [
      'sale_banner_block' => 'pettys_theme_preprocess_block__sale_banner_block',
      'pet_tech_banner_block' => 'pettys_theme_preprocess_block__pet_tech_banner_block',
      'flash_sale_block' => 'pettys_theme_preprocess_block__flash_sale_block',
      'featured_product_block' => 'pettys_theme_preprocess_block__featured_product_block',
      'social_posts_block' => 'pettys_theme_preprocess_block__social_posts_block',
      'social_post_images_block' => 'pettys_theme_preprocess_block__social_post_images_block',
      'hero_slider' => 'pettys_theme_preprocess_block__hero_slider',
      'journey_timeline_block' => 'pettys_theme_preprocess_block__journey_timeline_block',
      'faq_block' => 'pettys_theme_preprocess_block__faq_block',
      'product_detail_block' => 'pettys_theme_preprocess_block__product_detail_block',
      'best_sellers_block' => 'pettys_theme_preprocess_block__best_sellers_block',
      'categories_block' => 'pettys_theme_preprocess_block__categories_block',
      'popular_products_block' => 'pettys_theme_preprocess_block__popular_products_block',
      'toys_for_pets_block' => 'pettys_theme_preprocess_block__toys_for_pets_block',
    ];
    if (isset($preprocess_functions[$bundle])) {
      $preprocess_functions[$bundle]($variables);
    }
  }
}

/**
 * Preprocess for Sale Banner Block.
 */
function pettys_theme_preprocess_block__sale_banner_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'sale_banner_block') {
    $theme_path = get_theme_path();
    $variables['content']['field_sale_background_image'] = $block_content->get('field_sale_background_image')->isEmpty() || !($media = $block_content->get('field_sale_background_image')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
      ? $theme_path . '/images/still-life-domestic-animal-accessories-composition 1.png'
      : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
    $variables['content']['field_sale_subtitle'] = $block_content->get('field_sale_subtitle')->value ?? '';
    $variables['content']['field_sale_title'] = $block_content->get('field_sale_title')->value ?? '';
    $variables['content']['field_sale_end_date'] = $block_content->get('field_sale_end_date')->value ?? '';
    if (!$block_content->get('field_sale_button_url')->isEmpty()) {
      $link = $block_content->get('field_sale_button_url')->first();
      $variables['content']['field_sale_button_url_data'] = ['url' => $link->getUrl()->toString(), 'title' => $link->title ?? ''];
    }
  }
}

/**
 * Preprocess for Pet Tech Banner Block.
 */
function pettys_theme_preprocess_block__pet_tech_banner_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'pet_tech_banner_block') {
    $theme_path = get_theme_path();
    $variables['content']['field_pet_tech_background'] = $block_content->get('field_pet_tech_background')->isEmpty() || !($media = $block_content->get('field_pet_tech_background')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
      ? $theme_path . '/images/pet-tech-default.jpg'
      : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
    $variables['content']['field_pet_tech_title'] = $block_content->get('field_pet_tech_title')->value ?? '';
    if (!$block_content->get('field_pet_tech_button_url')->isEmpty()) {
      $link = $block_content->get('field_pet_tech_button_url')->first();
      $variables['content']['field_pet_tech_button_url_data'] = ['url' => $link->getUrl()->toString(), 'title' => $link->title ?? ''];
    }
  }
}

/**
 * Preprocess for Flash Sale Block.
 */
function pettys_theme_preprocess_block__flash_sale_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'flash_sale_block') {
    $theme_path = get_theme_path();
    $variables['content']['field_flash_sale_background'] = $block_content->get('field_flash_sale_background')->isEmpty() || !($media = $block_content->get('field_flash_sale_background')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
      ? $theme_path . '/images/flash-sale-default.jpg'
      : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
    $variables['content']['field_flash_sale_title'] = $block_content->get('field_flash_sale_title')->value ?? '';
    $variables['content']['field_flash_sale_end_date'] = $block_content->get('field_flash_sale_end_date')->value ?? '';
    $buttons = [];
    foreach ($block_content->get('field_flash_sale_buttons') as $button_item) {
      if (!$button_item->isEmpty()) {
        $buttons[] = ['url' => $button_item->getUrl()->toString(), 'title' => $button_item->title];
      }
    }
    $variables['content']['field_flash_sale_buttons'] = $buttons;
    $variables['configuration']['label_display'] = FALSE;
    $variables['elements']['#configuration']['label_display'] = FALSE;
  }
}

/**
 * Preprocess for Featured Product Block.
 */
function pettys_theme_preprocess_block__featured_product_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'featured_product_block') {
    $theme_path = get_theme_path();
    $variables['content']['field_featured_background'] = $block_content->get('field_featured_background')->isEmpty() || !($media = $block_content->get('field_featured_background')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
      ? $theme_path . '/images/featured-product-default.jpg'
      : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
    $variables['content']['field_featured_section_title'] = $block_content->get('field_featured_section_title')->value ?? '';
    $variables['content']['field_featured_subtitle'] = $block_content->get('field_featured_subtitle')->value ?? '';
    $variables['content']['field_featured_title'] = $block_content->get('field_featured_title')->value ?? '';
    if (!$block_content->get('field_featured_button_url')->isEmpty()) {
      $link = $block_content->get('field_featured_button_url')->first();
      $variables['content']['field_featured_button_url_data'] = ['url' => $link->getUrl()->toString(), 'title' => $link->title ?? ''];
    }
    $variables['configuration']['label_display'] = FALSE;
    $variables['elements']['#configuration']['label_display'] = FALSE;
  }
}

/**
 * Preprocess for Social Posts Block.
 */
function pettys_theme_preprocess_block__social_posts_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'social_posts_block') {
    $variables['platform'] = $block_content->get('field_platform')->value ?? 'Instagram';
    $variables['handle'] = $block_content->get('field_handle')->isEmpty() ? ['url' => '', 'title' => '@pettys_official'] : [
      'url' => $block_content->get('field_handle')->first()->getUrl()->toString(),
      'title' => $block_content->get('field_handle')->first()->getTitle(),
    ];
    $variables['description'] = $block_content->get('field_description')->value ?? 'Follow us for the latest pet products and updates!';
    $variables['theme_path'] = get_theme_path();
  }
}

/**
 * Preprocess for Social Post Images Block.
 */
function pettys_theme_preprocess_block__social_post_images_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'social_post_images_block') {
    $social_images = [];
    foreach ($block_content->get('field_social_images_ref') as $item) {
      $node = $item->entity;
      if ($node && !$node->get('field_images')->isEmpty()) {
        foreach ($node->get('field_images') as $image_item) {
          $media = $image_item->entity;
          if ($media && !$media->get('field_media_image')->isEmpty() && ($image = $media->get('field_media_image')->entity)) {
            $social_images[] = [
              'url' => \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri()),
              'alt' => $media->get('field_media_image')->alt ?: 'Social Post Image',
              'width' => ($info = getimagesize($image->getFileUri())) ? $info[0] : 160,
              'height' => $info ? $info[1] : 160,
            ];
          }
        }
      }
    }
    $variables['social_images'] = $social_images;
  }
}

/**
 * Preprocess for Hero Slider Block.
 */
function pettys_theme_preprocess_block__hero_slider(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'hero_slider') {
    $slides = [];
    foreach ($block_content->get('field_hero_slide') as $slide_ref) {
      $slide_node = $slide_ref->entity;
      if ($slide_node && $slide_node->access('view')) {
        $background_image = '';
        if (!$slide_node->get('field_hero_slide_background')->isEmpty() && ($media = $slide_node->get('field_hero_slide_background')->entity) && !$media->get('field_media_image')->isEmpty() && ($image = $media->get('field_media_image')->entity)) {
          $background_image = \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
        }
        $button_url = '#';
        $button_text = 'En savoir plus';
        if (!$slide_node->get('field_hero_slide_button_url')->isEmpty() && ($link = $slide_node->get('field_hero_slide_button_url')->first())) {
          $button_url = $link->getUrl()->toString();
          $button_text = $link->getTitle() ?: 'En savoir plus';
        }
        $description = $slide_node->get('field_hero_slide_description')->isEmpty() ? 'Description manquante' : ($slide_node->get('field_hero_slide_description')->first()->processed ?? $slide_node->get('field_hero_slide_description')->first()->value);
        $slides[] = [
          'title' => $slide_node->get('field_hero_slide_title')->value ?? 'Titre manquant',
          'description' => $description,
          'button_url' => $button_url,
          'button_text' => $button_text,
          'background_image' => $background_image,
          'nid' => $slide_node->id(),
        ];
      }
    }
    $variables['slides'] = $slides;
    $variables['#attached']['library'][] = 'pettys_theme/global-styling';
    $variables['#attached']['drupalSettings']['heroSlider'] = [
      'autoSlide' => TRUE,
      'slideInterval' => 5000,
      'animationDuration' => 800,
    ];
  }
}

/**
 * Preprocess for Journey Timeline Block.
 */
function pettys_theme_preprocess_block__journey_timeline_block(array &$variables) {
  $variables['attributes']['class'][] = 'our-journey';
  $variables['theme_path'] = get_theme_path();
  if (isset($variables['elements']['content']['field_timeline_items'])) {
    $item_count = count($variables['elements']['content']['field_timeline_items']['#items'] ?? []);
    $variables['default_active_index'] = min(2, floor($item_count / 2));
  }
}

/**
 * Preprocess for FAQ Block.
 */
function pettys_theme_preprocess_block__faq_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'faq_block') {
    $variables['faq_section_title'] = $block_content->get('field_faq_section_title')->value ?? 'FAQ';
    $variables['faq_intro'] = $block_content->get('field_faq_intro')->value ?? 'Si vous avez des questions, nous sommes là pour vous aider !';
    $faq_items = [];
    foreach ($block_content->get('field_faq_items') as $item) {
      if ($node = $item->entity) {
        $faq_items[] = [
          'question' => $node->getTitle(),
          'answer' => $node->hasField('field_answer') && !$node->get('field_answer')->isEmpty() ? $node->get('field_answer')->value : '',
        ];
      }
    }
    $variables['faq_items'] = $faq_items;
    $variables['theme_path'] = get_theme_path();
  }
}

/**
 * Preprocess for Product Detail Block.
 */
function pettys_theme_preprocess_block__product_detail_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'product_detail_block') {
    $theme_path = get_theme_path();
    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');

    $product_detail = [
      'title' => $block_content->get('field_product_title')->value ?? 'Titre par défaut',
      'price' => (float)($block_content->get('field_price')->value ?? 0.00),
      'rating' => (float)($block_content->get('field_rating')->value ?? 0.0),
      'reviews_count' => (int)($block_content->get('field_reviews_count')->value ?? 0),
      'badge' => $block_content->get('field_badge')->value ?? '',
      'description' => $block_content->get('field_description')->value ?? '',
      'add_to_cart_label' => $block_content->get('field_add_to_cart_label')->value ?? 'Ajouter au panier',
      'main_image' => $theme_path . '/images/placeholder.jpg',
      'colors' => [],
      'thumbnail_images' => [],
      'currency_symbol' => CurrencyConverter::getCurrencySymbol($current_currency),
    ];

    // Conversion et formatage du prix comme "100 DH"
    $converted_price = CurrencyConverter::convert($product_detail['price'], $current_currency);
    $product_detail['price'] = number_format($converted_price, 2) . $product_detail['currency_symbol'];

    if (!$block_content->get('field_buy_now_url')->isEmpty()) {
      $link = $block_content->get('field_buy_now_url')->first();
      $variables['content']['field_buy_now_url_data'] = ['url' => $link->getUrl()->toString(), 'title' => $link->title ?? ''];
    }

    if (!$block_content->get('field_main_image')->isEmpty() && ($media = $block_content->get('field_main_image')->entity) && !$media->get('field_media_image')->isEmpty() && ($file = $media->get('field_media_image')->entity)) {
      $product_detail['main_image'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
    }

    if (!$block_content->get('field_thumbnail_images')->isEmpty()) {
      $thumbnail_images = [];
      $color_index = 0;
      $colors = $block_content->get('field_colors')->getValue() ?: [];
      foreach ($block_content->get('field_thumbnail_images') as $thumbnail) {
        if (($media = $thumbnail->entity) && !$media->get('field_media_image')->isEmpty() && ($file = $media->get('field_media_image')->entity)) {
          $thumbnail_images[] = [
            '#type' => 'inline_template',
            '#template' => '<img src="{{ src }}" alt="{{ alt }}" loading="lazy" data-color="{{ color }}">',
            '#context' => [
              'src' => \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri()),
              'alt' => $media->get('field_media_image')->alt ?: 'Thumbnail Image',
              'color' => isset($colors[$color_index]['value']) ? explode('|', $colors[$color_index]['value'])[1] : 'default',
            ],
          ];
          $color_index = min($color_index + 1, count($colors) - 1);
        }
      }
      $product_detail['thumbnail_images'] = $thumbnail_images;
    }

    if (!$block_content->get('field_colors')->isEmpty()) {
      foreach ($block_content->get('field_colors') as $color) {
        [$label, $css_class] = explode('|', $color->value);
        $product_detail['colors'][] = ['label' => $label, 'css_class' => $css_class];
      }
    }

    $variables['product_detail'] = $product_detail;
  }
}

/**
 * Preprocess for Best Sellers Block.
 */
function pettys_theme_preprocess_block__best_sellers_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'best_sellers_block') {
    $theme_path = get_theme_path();
    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');
    $currency_symbol = CurrencyConverter::getCurrencySymbol($current_currency);

    $best_sellers_data = [
      'title' => $block_content->get('field_best_sellers_title')->value ?? 'Best Seller',
      'subtitle' => $block_content->get('field_best_sellers_subtitle')->value ?? 'Découvrez nos produits les plus vendus des trois derniers mois.',
      'button_link' => [
        'url' => $block_content->get('field_button_link')->isEmpty() ? '/produits' : $block_content->get('field_button_link')->first()->getUrl()->toString(),
        'title' => $block_content->get('field_button_link')->isEmpty() ? 'Tous les produits' : $block_content->get('field_button_link')->first()->getTitle(),
      ],
    ];

    $best_sellers_products = [];
    foreach ($block_content->get('field_best_sellers_products')->referencedEntities() as $node) {
      if ($node->bundle() === 'produit' && $node->get('field_est_un_best_seller')->value == 1) {
        $image_uri = $node->get('field_image_du_produit')->isEmpty() || !($media = $node->get('field_image_du_produit')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
          ? $theme_path . '/images/product-placeholder.jpg'
          : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());

        $current_price_usd = $node->get('field_prix_actuel')->value ?? 0.00;
        $old_price_usd = $node->get('field_ancien_prix')->value ?? '';

        $current_price = CurrencyConverter::convert($current_price_usd, $current_currency);
        $old_price = $old_price_usd ? CurrencyConverter::convert($old_price_usd, $current_currency) : '';

        $best_sellers_products[] = [
          'name' => $node->getTitle(),
          'image' => $image_uri,
          'current_price' => number_format($current_price, 2) . $currency_symbol,
          'old_price' => $old_price ? number_format($old_price, 2) . $currency_symbol : '',
          'rating' => $node->get('field_evaluation')->isEmpty() ? '★★★★☆' : str_repeat('★', min(5, (int)$node->get('field_evaluation')->value)) . str_repeat('☆', max(0, 5 - (int)$node->get('field_evaluation')->value)),
        ];
      }
    }
    $variables['best_sellers_data'] = $best_sellers_data;
    $variables['best_sellers_products'] = array_slice($best_sellers_products, 0, 5);
    $variables['configuration']['label_display'] = FALSE;
    $variables['elements']['#configuration']['label_display'] = FALSE;
  }
}

/**
 * Preprocess for Categories Block.
 */
function pettys_theme_preprocess_block__categories_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'categories_block') {
    $theme_path = get_theme_path();
    $categories_data = [
      'title' => $block_content->get('field_categories_title')->value ?? 'Categories',
      'subtitle' => $block_content->get('field_categories_subtitle')->value ?? 'Navigate By Choosing The Type Of Pets You Own.',
      'button_link' => [
        'url' => $block_content->get('field_button_link')->isEmpty() ? '/categories' : $block_content->get('field_button_link')->first()->getUrl()->toString(),
        'title' => $block_content->get('field_button_link')->isEmpty() ? 'All Categories' : $block_content->get('field_button_link')->first()->getTitle(),
      ],
    ];

    $categories = [];
    foreach ($block_content->get('field_categories')->referencedEntities() as $term) {
      $image_uri = $term->get('field_category_image')->isEmpty() || !($media = $term->get('field_category_image')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
        ? $theme_path . '/images/category-placeholder.jpg'
        : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
      $size = $term->get('field_category_size')->isEmpty() || !in_array($term->get('field_category_size')->value, ['large', 'large_large', 'large_large|large']) ? 'small' : 'large';
      $item_count = \Drupal::entityQuery('node')
        ->condition('type', 'produit')
        ->condition('status', 1)
        ->condition('field_product_category', $term->id())
        ->accessCheck(TRUE)
        ->count()
        ->execute();
      $categories[] = [
        'name' => $term->getName(),
        'image' => $image_uri,
        'size' => $size,
        'item_count' => $item_count,
        'url' => $term->get('field_category_url')->isEmpty() ? '/category/' . $term->id() : $term->get('field_category_url')->first()->getUrl()->toString(),
      ];
    }
    $variables['categories_data'] = $categories_data;
    $variables['categories'] = $categories;
    $variables['theme_path'] = $theme_path;
    $variables['#cache']['contexts'][] = 'user.permissions';
    $variables['#cache']['tags'] = ['taxonomy_term_list', 'node_list:produit'];
    $variables['configuration']['label_display'] = FALSE;
    $variables['elements']['#configuration']['label_display'] = FALSE;
  }
}

/**
 * Preprocess for Popular Products Block.
 */
function pettys_theme_preprocess_block__popular_products_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'popular_products_block') {
    $theme_path = get_theme_path();
    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');
    $currency_symbol = CurrencyConverter::getCurrencySymbol($current_currency);

    $popular_products_data = [
      'title' => $block_content->hasField('field_popular_products_title') && !$block_content->get('field_popular_products_title')->isEmpty()
        ? $block_content->get('field_popular_products_title')->value : 'Popular Products',
      'subtitle' => $block_content->hasField('field_popular_products_subtitle') && !$block_content->get('field_popular_products_subtitle')->isEmpty()
        ? $block_content->get('field_popular_products_subtitle')->value : 'Favorite Products Loved by Pet Owners Everywhere!',
      'button_link' => [
        'url' => $block_content->hasField('field_button_link') && !$block_content->get('field_button_link')->isEmpty()
          ? $block_content->get('field_button_link')->first()->getUrl()->toString() : '/produits',
        'title' => $block_content->hasField('field_button_link') && !$block_content->get('field_button_link')->isEmpty()
          ? $block_content->get('field_button_link')->first()->getTitle() : 'All Products',
      ],
    ];

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'produit')
      ->condition('status', 1)
      ->condition('field_est_populaire', TRUE)
      ->sort('created', 'ASC')
      ->accessCheck(TRUE)
      ->range(0, 5);
    $nids = $query->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
    $products = [];
    foreach ($nodes as $node) {
      $image_uri = '';
      if ($node->hasField('field_image_du_produit') && !$node->get('field_image_du_produit')->isEmpty()) {
        $media = $node->get('field_image_du_produit')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $image_field = $media->get('field_media_image');
          if ($image_field->entity) {
            $image_uri = $image_field->entity->getFileUri();
          }
        }
      }

      $current_price_usd = $node->hasField('field_prix_actuel') && !$node->get('field_prix_actuel')->isEmpty()
        ? $node->get('field_prix_actuel')->value : 0.00;
      $old_price_usd = $node->hasField('field_ancien_prix') && !$node->get('field_ancien_prix')->isEmpty()
        ? $node->get('field_ancien_prix')->value : '';

      $current_price = CurrencyConverter::convert($current_price_usd, $current_currency);
      $old_price = $old_price_usd ? CurrencyConverter::convert($old_price_usd, $current_currency) : '';

      $products[] = [
        'name' => $node->getTitle(),
        'image' => $image_uri ? \Drupal::service('file_url_generator')->generateAbsoluteString($image_uri) : $theme_path . '/images/product-placeholder.jpg',
        'current_price' => number_format($current_price, 2) . $currency_symbol,
        'old_price' => $old_price ? number_format($old_price, 2) . $currency_symbol : '',
        'rating' => $node->hasField('field_evaluation') && !$node->get('field_evaluation')->isEmpty()
          ? str_repeat('★', min(5, (int)$node->get('field_evaluation')->value)) . str_repeat('☆', max(0, 5 - (int)$node->get('field_evaluation')->value))
          : '★★★★☆',
      ];
    }

    $variables['popular_products_data'] = $popular_products_data;
    $variables['popular_products'] = $products;
    $variables['theme_path'] = $theme_path;
    $variables['#cache']['contexts'][] = 'user.permissions';
    $variables['#cache']['tags'][] = 'node_list:produit';
    $variables['configuration']['label_display'] = FALSE;
    $variables['elements']['#configuration']['label_display'] = FALSE;
  }
}

/**
 * Preprocess for Toys for Pets Block.
 */
function pettys_theme_preprocess_block__toys_for_pets_block(array &$variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content && $block_content->bundle() === 'toys_for_pets_block') {
    $theme_path = get_theme_path();
    $toys_data = [
      'title' => $block_content->get('field_toys_title')->value ?? 'Jouets pour Animaux',
      'subtitle' => $block_content->get('field_toys_subtitle')->value ?? 'Gardez vos amis à fourrure heureux !',
      'button_link' => [
        'url' => $block_content->get('field_button_link')->isEmpty() ? '/jouets' : $block_content->get('field_button_link')->first()->getUrl()->toString(),
        'title' => $block_content->get('field_button_link')->isEmpty() ? 'Tous les jouets' : $block_content->get('field_button_link')->first()->getTitle(),
      ],
    ];

    $toys = [];
    foreach ($block_content->get('field_produits_jouets')->referencedEntities() as $node) {
      if ($node->bundle() === 'produit' && $node->get('field_nom_du_jouet')->value == 1) {
        $image_uri = $node->get('field_image_du_produit')->isEmpty() || !($media = $node->get('field_image_du_produit')->entity) || $media->get('field_media_image')->isEmpty() || !($image = $media->get('field_media_image')->entity)
          ? $theme_path . '/images/product-placeholder.jpg'
          : \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
        $toys[] = [
          'title' => $node->getTitle(),
          'image' => $image_uri,
          'image_alt' => !$node->get('field_image_du_produit')->isEmpty() ? ($node->get('field_image_du_produit')->entity->get('field_media_image')->alt ?? $node->getTitle()) : $node->getTitle(),
          'size' => $node->get('field_taille_affichage')->value ?? 'small',
        ];
      }
    }
    $variables['toys_data'] = $toys_data;
    $variables['toys'] = $toys;
    $variables['theme_path'] = $theme_path;
    $variables['#cache']['contexts'][] = 'user.permissions';
    $variables['#cache']['tags'] = ['node_list:produit'];
    $variables['configuration']['label_display'] = FALSE;
    $variables['elements']['#configuration']['label_display'] = FALSE;
  }
}
