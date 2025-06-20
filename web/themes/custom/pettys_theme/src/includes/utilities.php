<?php

/**
 * @file
 * Utility functions for Pettys Theme.
 */

/**
 * Get the theme path.
 *
 * @return string
 *   The base path to the Pettys Theme.
 */
function get_theme_path() {
  return \Drupal::request()->getBasePath() . '/' . \Drupal::service('extension.list.theme')->getPath('pettys_theme');
}


/**
 * Implements hook_theme().
 */
function pettys_theme_theme($existing, $type, $theme, $path) {
  return [
    'brand_partners' => [
      'variables' => [
        'brands' => NULL,
        'theme_path' => NULL,
        'paw_path_image' => NULL,
      ],
      'template' => 'block/block--brand-partners-block',
    ],
    'toys_for_pets' => [
      'variables' => [
        'toys_section' => NULL,
      ],
    ],
    'region__primary_menu' => [
      'variables' => [
        'content' => NULL,
        'nav_right_items' => [],
        'cart_count' => 0,
        'custom_search_form' => NULL,
        'user' => NULL,
        'logged_in' => FALSE,
      ],
      'template' => 'regions/region--primary-menu',
    ],
  ];
}
