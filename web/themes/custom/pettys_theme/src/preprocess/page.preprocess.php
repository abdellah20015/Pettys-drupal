<?php

/**
 * @file
 * Preprocess functions for page templates in Pettys Theme.
 */

use Drupal\node\NodeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\block_content\Entity\BlockContent;

/**
 * Implements hook_preprocess_page().
 */
function pettys_theme_preprocess_page(&$variables) {
  // User data.
  if (\Drupal::currentUser()->isAuthenticated()) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $variables['user'] = [
      'id' => \Drupal::currentUser()->id(),
      'name' => $user->getAccountName(),
    ];
  }

  // Theme path.
  $theme_path = get_theme_path();
  $variables['theme_path'] = $theme_path;

  // Dynamic Brand Partners.
  $block_content = BlockContent::load(35);
  $brands = [];
  $paw_path_uri = $theme_path . '/images/food-bool.png';

  if ($block_content) {
    if (!$block_content->get('field_brand_sliders')->isEmpty()) {
      foreach ($block_content->get('field_brand_sliders') as $item) {
        $node = $item->entity;
        if ($node instanceof \Drupal\node\NodeInterface) {
          $logo_uri = '';
          if ($node->hasField('field_logo_marque') && !$node->get('field_logo_marque')->isEmpty()) {
            $media = $node->get('field_logo_marque')->entity;
            if ($media && $media->hasField('field_media_image')) {
              $image = $media->get('field_media_image')->entity;
              if ($image) {
                $logo_uri = \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
              }
            }
          }
          $brands[] = [
            'name' => $node->getTitle(),
            'image' => $logo_uri ?: $theme_path . '/images/brand-placeholder.png',
            'url' => $node->hasField('field_brand_url') && !$node->get('field_brand_url')->isEmpty() ? $node->get('field_brand_url')->uri : '#',
          ];
        }
      }
    }

    // Retrieve Paw Path image (field_paw_path_image).
    if (!$block_content->get('field_paw_path_image')->isEmpty()) {
      $media = $block_content->get('field_paw_path_image')->entity;
      if ($media && $media->hasField('field_media_image')) {
        $image = $media->get('field_media_image')->entity;
        if ($image) {
          $paw_path_uri = \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
        }
      }
    }
  }

  // Pass data to the region.
  $variables['page']['brand_partners'] = [
    '#theme' => 'brand_partners',
    '#brands' => $brands,
    '#paw_path_image' => $paw_path_uri,
    '#theme_path' => $theme_path,
  ];

  // Load Main Menu links.
  $menu_name = 'main-menu';
  $menu_tree = \Drupal::menuTree();
  $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
  $parameters->setMinDepth(1);
  $parameters->setMaxDepth(1);
  $tree = $menu_tree->load($menu_name, $parameters);
  $manipulators = [
    ['callable' => 'menu.default_tree_manipulators:checkAccess'],
    ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
  ];
  $tree = $menu_tree->transform($tree, $manipulators);
  $main_menu_links = [];
  foreach ($tree as $item) {
    if ($item->link->isEnabled()) {
      $main_menu_links[] = [
        'title' => $item->link->getTitle(),
        'url' => $item->link->getUrlObject()->toString(),
        'attributes' => $item->link->getOptions()['attributes'] ?? [],
      ];
    }
  }
  $variables['main_menu_links'] = $main_menu_links;
}
