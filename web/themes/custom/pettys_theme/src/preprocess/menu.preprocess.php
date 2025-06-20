<?php

/**
 * @file
 * Preprocess functions for menu templates in Pettys Theme.
 */

/**
 * Implements hook_preprocess_menu().
 */
function pettys_theme_preprocess_menu(&$variables) {
  if ($variables['menu_name'] === 'social-icons-menu') {
    foreach ($variables['items'] as &$item) {
      if (preg_match('/<i class="([^"]+)"><\/i>/', $item['title'], $matches)) {
        $item['attributes'] = new \Drupal\Core\Template\Attribute();
        $item['attributes']->addClass(explode(' ', $matches[1]));
        $item['title'] = '';
      }
      $item['attributes']->setAttribute('target', '_blank');
    }
  }
}