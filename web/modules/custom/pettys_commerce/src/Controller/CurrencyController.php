<?php

namespace Drupal\pettys_commerce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur pour gérer les changements de devise.
 */
class CurrencyController extends ControllerBase {

  /**
   * Change la devise dans la session.
   */
  public function changeCurrency(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    if (!isset($data['currency'])) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Devise non spécifiée'
      ], 400);
    }

    $currency_code = $data['currency'];

    // Vérifier si la devise existe
    if (\Drupal::moduleHandler()->moduleExists('commerce_price')) {
      $currency_storage = \Drupal::entityTypeManager()->getStorage('commerce_currency');
      $currency = $currency_storage->load($currency_code);

      if (!$currency || !$currency->status()) {
        return new JsonResponse([
          'success' => FALSE,
          'message' => 'Devise invalide ou non activée'
        ], 400);
      }

      // Sauvegarder dans la session
      $session = $request->getSession();
      $session->set('commerce_currency', $currency_code);

      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Devise changée avec succès',
        'currency' => $currency_code
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
      'message' => 'Module Commerce non disponible'
    ], 500);
  }
}
