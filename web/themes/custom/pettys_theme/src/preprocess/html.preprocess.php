<?php

/**
 * @file
 * Preprocess functions for HTML templates in Pettys Theme.
 */

/**
 * Implements hook_preprocess_html().
 */
function pettys_theme_preprocess_html(&$variables) {
  $variables['#attached']['library'][] = 'pettys_theme/search-modal';
}