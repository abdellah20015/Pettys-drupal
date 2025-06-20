<?php

namespace Drupal\pettys_new_arrivals\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\node\Entity\Node;
use Drupal\pettys_commerce\CurrencyConverter;

/**
 * Service for New Arrivals functionality.
 */
class NewArrivalsService {

  protected $entityTypeManager;
  protected $fileUrlGenerator;
  protected $tempStore;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileUrlGeneratorInterface $file_url_generator,
    PrivateTempStoreFactory $temp_store_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
    $this->tempStore = $temp_store_factory->get('pettys_new_arrivals');
  }

  /**
   * Get all products marked as new arrivals.
   */
  public function getAllNewArrivalsProducts() {
    $product_query = \Drupal::entityQuery('node')
      ->condition('type', 'produit')
      ->condition('status', 1)
      ->condition('field_est_une_nouvelle_arrivee', TRUE)
      ->accessCheck(TRUE);

    $product_nids = $product_query->execute();

    if (empty($product_nids)) {
      return [];
    }

    return $this->loadNodesByIds($product_nids);
  }

  /**
   * Get selected products for display.
   */
  public function getSelectedProducts() {
    $selected = $this->tempStore->get('selected_products') ?: [];

    if (empty($selected)) {
      return [];
    }

    $nodes = $this->loadNodesByIds($selected);
    return $this->formatProducts($nodes);
  }

  /**
   * Helper method to load nodes by IDs.
   */
  protected function loadNodesByIds(array $nids) {
    $nodes = [];
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      if ($node) {
        $nodes[] = $node;
      }
    }
    return $nodes;
  }

  /**
   * Get products for block display (public method).
   */
  public function getProducts() {
    return $this->getSelectedProducts();
  }

  /**
   * Get configuration.
   */
  public function getConfiguration() {
    return $this->tempStore->get('configuration');
  }

  /**
   * Format products for display.
   */
  public function formatProducts(array $nodes) {
    $products = [];
    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');
    $currency_symbol = CurrencyConverter::getCurrencySymbol($current_currency);

    foreach ($nodes as $node) {
      if ($node->getType() === 'produit' && $node->isPublished()) {
        $image_url = $this->getProductImage($node);

        $current_price_usd = $this->getFieldValue($node, ['field_prix_actuel'], '0.00');
        $old_price_usd = $this->getFieldValue($node, ['field_ancien_prix'], '');

        $current_price = CurrencyConverter::convert($current_price_usd, $current_currency);
        $old_price = $old_price_usd ? CurrencyConverter::convert($old_price_usd, $current_currency) : '';

        $products[] = [
          'nid' => $node->id(),
          'name' => $this->getFieldValue($node, ['field_nom_du_produit'], $node->getTitle()),
          'image' => $image_url,
          'current_price' => number_format($current_price, 2) . $currency_symbol,
          'old_price' => $old_price ? number_format($old_price, 2) . $currency_symbol : '',
          'rating' => $this->generateRating($this->getFieldValue($node, ['field_evaluation'], 4)),
          'icons' => $this->getProductIcons($node),
          'url' => $this->getFieldValue($node, ['field_product_url'], '/node/' . $node->id()),
        ];
      }
    }

    return $products;
  }

  /**
   * Get product image URL with proper Media entity handling.
   */
  protected function getProductImage($node) {
  if ($node->hasField('field_image_du_produit') && !$node->get('field_image_du_produit')->isEmpty()) {
    $media_entity = $node->get('field_image_du_produit')->entity;

    if ($media_entity && $media_entity->hasField('field_media_image') && !$media_entity->get('field_media_image')->isEmpty()) {
      $file = $media_entity->get('field_media_image')->entity;
      if ($file) {
        return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }
  }

  return NULL;
}


  /**
   * Format price with currency.
   */
  protected function formatPrice($price) {
    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');
    $currency_symbol = CurrencyConverter::getCurrencySymbol($current_currency);

    if (empty($price) || $price === '0.00') {
      return '0.00 ' . $currency_symbol;
    }

    $price = is_numeric($price) ? $price : floatval($price);
    $converted_price = CurrencyConverter::convert($price, $current_currency);
    return number_format($converted_price, 2) . ' ' . $currency_symbol;
  }

  /**
   * Get old price formatted.
   */
  protected function getOldPrice($node) {
    $old_price = $this->getFieldValue($node, ['field_ancien_prix'], '');
    return $old_price ? $this->formatPrice($old_price) : '';
  }

  /**
   * Get product icons with improved error handling.
   */
  protected function getProductIcons($node) {
    $icons = [];
    if ($node->hasField('field_product_icons') && !$node->get('field_product_icons')->isEmpty()) {
      foreach ($node->get('field_product_icons')->referencedEntities() as $paragraph) {
        if ($paragraph && $paragraph->bundle() === 'product_icon') {
          $icon_class = $this->getFieldValue($paragraph, ['field_icon_class'], '');
          $icon_action = $this->getFieldValue($paragraph, ['field_icon_action'], '');

          if (!empty($icon_class) && !empty($icon_action)) {
            $icons[] = [
              'class' => $icon_class,
              'action' => $icon_action,
              'url' => '/node/' . $node->id(),
            ];
          }
        }
      }
    }

    return $icons;
  }

  /**
   * Generate rating stars.
   */
  protected function generateRating($rating) {
    $rating = (int) $rating;
    $rating = max(0, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
  }

  /**
   * Get field value with fallbacks.
   */
  protected function getFieldValue($entity, array $field_names, $default = '') {
    foreach ($field_names as $field_name) {
      if ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
        $field_item = $entity->get($field_name)->first();

        // Check if the field item is a link.
        if ($field_item && method_exists($field_item, 'getUrl')) {
          return $field_item->getUrl()->toString();
        }

        // If the field is a text field, return its value.
        $value = $entity->get($field_name)->value;
        return $value !== null ? $value : '';
      }
    }
  }

}
