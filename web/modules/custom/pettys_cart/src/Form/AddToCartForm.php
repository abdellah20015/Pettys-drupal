<?php

namespace Drupal\pettys_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for adding products to cart.
 */
class AddToCartForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pettys_cart_add_to_cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = []) {
    $product_key = $options['product_key'] ?? 'default';
    $label = $options['label'] ?? 'Add To Cart';

    $form['product_key'] = [
      '#type' => 'hidden',
      '#value' => $product_key,
    ];

    $form['quantity'] = [
      '#type' => 'hidden',
      '#value' => 1,
    ];

    $form['add_to_cart'] = [
      '#type' => 'submit',
      '#value' => $label,
      '#attributes' => [
        'class' => ['btn', 'btn-cart'],
      ],
      '#ajax' => [
        'callback' => '::ajaxAddToCart',
        'wrapper' => 'cart-messages',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];

    $form['messages'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'cart-messages'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $product_key = $form_state->getValue('product_key');
    $quantity = $form_state->getValue('quantity');

    // Add to session cart
    $cart = $_SESSION['pettys_cart'] ?? [];

    if (isset($cart[$product_key])) {
      $cart[$product_key]['quantity'] += $quantity;
    } else {
      $cart[$product_key] = [
        'product_key' => $product_key,
        'quantity' => $quantity,
        'added_time' => time(),
      ];
    }

    $_SESSION['pettys_cart'] = $cart;

    // Show success message
    $this->messenger()->addMessage($this->t('Product added to cart successfully!'));
  }

  /**
   * Ajax callback for add to cart.
   */
  public function ajaxAddToCart(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();

    // Add success message
    $response->addCommand(new \Drupal\Core\Ajax\MessageCommand(
      $this->t('Product added to cart!'),
      NULL,
      ['type' => 'status']
    ));

    return $response;
  }
}
