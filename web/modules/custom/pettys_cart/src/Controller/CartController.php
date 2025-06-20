<?php

namespace Drupal\pettys_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartController extends ControllerBase {

  public function addToCart($product_key, $variation_key) {
  $uid = \Drupal::currentUser()->id();
  $database = \Drupal::database();

  try {
    $existing = $database->select('pettys_cart', 'c')
      ->fields('c', ['id', 'quantity'])
      ->condition('uid', $uid)
      ->condition('product_key', $product_key)
      ->condition('variation_key', $variation_key)
      ->execute()
      ->fetchObject();

    if ($existing) {
      $database->update('pettys_cart')
        ->fields(['quantity' => $existing->quantity + 1])
        ->condition('id', $existing->id)
        ->execute();
    } else {
      $database->insert('pettys_cart')
        ->fields([
          'uid' => $uid,
          'product_key' => $product_key,
          'variation_key' => $variation_key,
          'quantity' => 1,
          'created' => time(),
        ])
        ->execute();
    }

    // Calculer le nouveau cart_count
    $cart_count = 0;
    $query = $database->select('pettys_cart', 'c')
      ->fields('c', ['quantity'])
      ->condition('uid', $uid);
    $result = $query->execute()->fetchAll();

    foreach ($result as $item) {
      $cart_count += (int) $item->quantity;
    }

    $this->messenger()->addMessage($this->t('Article ajouté au panier.'));
    return new JsonResponse(['success' => true, 'cart_count' => $cart_count]);
  } catch (\Exception $e) {
    \Drupal::logger('pettys_cart')->error('Erreur lors de l\'ajout au panier : @message', ['@message' => $e->getMessage()]);
    return new JsonResponse(['success' => false, 'message' => $this->t('Erreur lors de l\'ajout au panier.')], 500);
  }
}

  public function viewCart() {
    $uid = \Drupal::currentUser()->id();
    $database = Database::getConnection();
    $items = $database->select('pettys_cart', 'c')
      ->fields('c', ['product_key', 'variation_key', 'quantity'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchAll();

    $cart_items = [];
    $total = 0;
    foreach ($items as $item) {
      $product_data = $this->getProductData($item->product_key, $item->variation_key);
      $cart_items[] = [
        'product_key' => $item->product_key,
        'title' => $product_data['title'],
        'variation' => $item->variation_key,
        'quantity' => $item->quantity,
        'price' => $product_data['price'],
        'subtotal' => $item->quantity * $product_data['price'],
      ];
      $total += $item->quantity * $product_data['price'];
    }

    return [
      '#theme' => 'pettys_cart',
      '#items' => $cart_items,
      '#total' => $total,
      '#cache' => ['max-age' => 0],
    ];
  }

public function getCartCount() {
  $uid = \Drupal::currentUser()->id();
  $database = \Drupal::database();
  $cart_count = 0;

  try {
    $query = $database->select('pettys_cart', 'c')
      ->fields('c', ['quantity'])
      ->condition('uid', $uid);
    $result = $query->execute()->fetchAll();

    foreach ($result as $item) {
      $cart_count += (int) $item->quantity;
    }
  } catch (\Exception $e) {
    \Drupal::logger('pettys_cart')->error('Erreur lors de la récupération du cart_count : @message', ['@message' => $e->getMessage()]);
  }

  return new JsonResponse(['cart_count' => $cart_count]);
}

  /**
 * Récupère les données du produit à partir du type de bloc product_detail_block.
 */
private function getProductData($product_key, $variation_key) {
  $query = \Drupal::entityQuery('block_content')
    ->condition('type', 'product_detail_block')
    ->condition('field_title', str_replace('-', ' ', $product_key))
    ->accessCheck(TRUE);
  $block_ids = $query->execute();

  if (!empty($block_ids)) {
    $block = \Drupal\block_content\Entity\BlockContent::load(reset($block_ids));
    if ($block) {
      return [
        'title' => $block->get('field_title')->value,
        'price' => $block->get('field_price')->value ?? 0.00,
      ];
    }
  }

  // Fallback si aucun bloc correspondant n'est trouvé
  return [
    'title' => 'Article inconnu',
    'price' => 0.00,
  ];
}
}
