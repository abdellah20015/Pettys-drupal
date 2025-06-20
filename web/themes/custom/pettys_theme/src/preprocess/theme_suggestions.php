<?php

/**
 * @file
 * Theme suggestion hooks for Pettys Theme.
 */

/**
 * Implements hook_theme_suggestions_menu_alter().
 */
function pettys_theme_theme_suggestions_menu_alter(array &$suggestions, array $variables) {
  if ($variables['menu_name'] == 'main-menu') {
    $suggestions[] = 'menu__main';
  }
  if ($variables['menu_name'] == 'social-icons-menu') {
    $suggestions[] = 'menu__social_icons';
  }
}

/**
 * Implements hook_theme_suggestions_block_alter().
 */
function pettys_theme_theme_suggestions_block_alter(array &$suggestions, array $variables) {
  $block_content = $variables['elements']['content']['#block_content'] ?? NULL;
  if ($block_content) {
    switch ($block_content->bundle()) {
      case 'faq_block':
        $suggestions[] = 'block__faq_block';
        break;
      case 'sale_banner_block':
        $suggestions[] = 'block__sale_banner_block';
        break;
      case 'pet_tech_banner_block':
        $suggestions[] = 'block__pet_tech_banner_block';
        break;
      case 'flash_sale_block':
        $suggestions[] = 'block__flash_sale_block';
        break;
      case 'featured_product_block':
        $suggestions[] = 'block__featured_product_block';
        break;
      case 'hero_slider':
        $suggestions[] = 'block__hero_slider';
        break;
      case 'product_detail_block':
        $suggestions[] = 'block__product_detail_block';
        break;
      case 'best_sellers_block':
        $suggestions[] = 'block__best_sellers_block';
        break;
      case 'categories_block':
        $suggestions[] = 'block__categories_block';
        break;
      case 'popular_products_block':
        $suggestions[] = 'block__popular_products_block';
        break;
      case 'toys_for_pets_block':
        $suggestions[] = 'block__toys_for_pets_block';
        break;
    }
  }
}