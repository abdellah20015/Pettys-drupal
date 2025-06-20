<?php

/**
 * @file
 * Preprocess functions for region templates in Pettys Theme.
 */

/**
 * Implements hook_preprocess_region__footer().
 */
function pettys_theme_preprocess_region__footer(&$variables) {
  $theme_path = get_theme_path();
  $variables['theme_path'] = $theme_path;
  $variables['site_logo'] = theme_get_setting('logo.url', 'pettys_theme');
  $variables['site_name'] = \Drupal::config('system.site')->get('name');
  $variables['copyright_text'] = \Drupal::service('twig')->renderInline(theme_get_setting('footer_copyright_text', 'pettys_theme') ?: '© Pettys {{ "now"|date("Y") }}, All Rights Reserved');
  $variables['newsletter_text'] = theme_get_setting('footer_newsletter_text', 'pettys_theme') ?: 'Glow from the inside out! Inbox to INBOX!';
  $payment_fid = theme_get_setting('footer_payment_methods_image', 'pettys_theme');
  $variables['payment_methods_image'] = ($payment_fid && is_array($payment_fid) && !empty($payment_fid[0]) && ($file = \Drupal\file\Entity\File::load($payment_fid[0])))
    ? \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri())
    : $theme_path . '/images/icons_payment.png';

  $menu_tree = \Drupal::menuTree();
  $parameters = $menu_tree->getCurrentRouteMenuTreeParameters('social-icons-menu')->setMinDepth(1)->setMaxDepth(1);
  $tree = $menu_tree->load('social-icons-menu', $parameters);
  $tree = $menu_tree->transform($tree, [
    ['callable' => 'menu.default_tree_manipulators:checkAccess'],
    ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
  ]);
  $social_links = [];
  foreach ($tree as $item) {
    if ($item->link->isEnabled()) {
      preg_match('/<i class="([^"]+)"><\/i>/', $item->link->getTitle(), $matches);
      $social_links[] = [
        'url' => $item->link->getUrlObject()->toString(),
        'label' => $item->link->getTitle(),
        'icon' => $matches[1] ?? '',
      ];
    }
  }

  $ordered_social_links = [];
  foreach (['Instagram', 'Facebook', 'YouTube', 'WhatsApp'] as $platform) {
    foreach ($social_links as $item) {
      if (stripos($item['label'], $platform) !== false || stripos($item['icon'], "bi-$platform") !== false) {
        $ordered_social_links[] = $item;
        break;
      }
    }
  }
  $variables['social_links'] = $ordered_social_links ?: $social_links;
}

/**
 * Implements hook_preprocess_region().
 */
function pettys_theme_preprocess_region(&$variables) {
  if ($variables['region'] !== 'primary_menu') {
    return;
  }

  $block = \Drupal\block\Entity\Block::load('pettys_theme_customsearchform');
  if ($block) {
    $variables['custom_search_form'] = \Drupal::service('renderer')->renderRoot(
      \Drupal::entityTypeManager()->getViewBuilder('block')->view($block)
    );
  }

  $nids = \Drupal::entityQuery('node')
    ->condition('type', 'nav_right_item')
    ->condition('status', 1)
    ->sort('field_weight', 'ASC')
    ->accessCheck(TRUE)
    ->execute();
  $nav_items = [];
  foreach (\Drupal\node\Entity\Node::loadMultiple($nids) as $node) {
    $nav_items[] = [
      'type' => $node->get('field_nav_item_type')->value ?? '',
      'icon' => $node->get('field_icon')->value ?? '',
      'text' => $node->get('field_text')->value ?? '',
      'url' => $node->get('field_lien')->isEmpty() ? '#' : $node->get('field_lien')->first()->getUrl()->toString(),
      'weight' => $node->get('field_weight')->value ?? 0,
    ];
  }

  $cart_count = 0;
  $uid = \Drupal::currentUser()->id();
  $database = \Drupal::database();
  if ($database->schema()->tableExists('pettys_cart')) {
    $result = $database->select('pettys_cart', 'c')
      ->fields('c', ['quantity'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchAll();
    foreach ($result as $item) {
      $cart_count += (int) $item->quantity;
    }
  }
  $variables['cart_count'] = $cart_count;
  $variables['nav_right_items'] = $nav_items;

  if (\Drupal::currentUser()->isAuthenticated()) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $variables['user'] = [
      'id' => \Drupal::currentUser()->id(),
      'name' => $user->getAccountName(),
    ];
  }
}
