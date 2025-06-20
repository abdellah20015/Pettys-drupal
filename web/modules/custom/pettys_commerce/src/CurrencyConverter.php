<?php

namespace Drupal\pettys_commerce;
use Drupal\commerce_price\Entity\Currency;

/**
 * Classe utilitaire pour gérer la conversion des devises via Fawaz Ahmed API.
 */
class CurrencyConverter {

  /**
   * Récupère les taux de change via l'API gratuite Fawaz Ahmed.
   *
   * @param string $base_currency
   *   La devise de base (par exemple, USD).
   * @return array|null
   *   Les taux de change ou NULL en cas d'erreur.
   */
  public static function getExchangeRates($base_currency = 'USD') {
    try {
      $api_url = "https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/" . strtolower($base_currency) . ".json";

      $context = stream_context_create([
        'http' => [
          'timeout' => 10,
          'user_agent' => 'Drupal Currency Converter',
          'method' => 'GET',
        ]
      ]);

      $response = file_get_contents($api_url, false, $context);

      if ($response === FALSE) {
        throw new \Exception('Impossible de récupérer les données depuis l\'API Fawaz Ahmed');
      }

      $data = json_decode($response, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Erreur de décodage JSON: ' . json_last_error_msg());
      }

      if (isset($data[strtolower($base_currency)])) {
        $rates = [];

        // Convertir les clés en majuscules pour la cohérence
        foreach ($data[strtolower($base_currency)] as $currency => $rate) {
          $rates[strtoupper($currency)] = (float) $rate;
        }


        $rates[strtoupper($base_currency)] = 1.0;

        return $rates;
      }

      throw new \Exception('Format de réponse API invalide');

    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Convertit un montant d'une devise à une autre.
   *
   * @param float $amount
   *   Le montant à convertir.
   * @param string $to_currency
   *   La devise cible.
   * @param string $base_currency
   *   La devise de base (par défaut USD).
   * @return float|null
   *   Le montant converti ou l'original si la conversion échoue.
   */
  public static function convert($amount, $to_currency, $base_currency = 'USD') {
    $exchange_rates = self::getExchangeRates($base_currency);
    if ($exchange_rates && isset($exchange_rates[strtoupper($to_currency)])) {
      return round($amount * $exchange_rates[strtoupper($to_currency)], 2);
    }
    return $amount;
  }

  /**
   * Get the currency symbol for a given currency code.
   *
   * @param string $currency_code
   *   The currency code (e.g., USD, MAD, GBP).
   * @return string
   *   The currency symbol (e.g., $, DH, £).
   */
  public static function getCurrencySymbol($currency_code) {
    $currency_storage = \Drupal::entityTypeManager()->getStorage('commerce_currency');
    $currency = $currency_storage->load($currency_code);
    if ($currency instanceof Currency) {
      return $currency->getSymbol();
    }
    return $currency_code;
  }

}
