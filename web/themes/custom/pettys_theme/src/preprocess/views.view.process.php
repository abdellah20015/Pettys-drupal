<?php

use Drupal\pettys_commerce\CurrencyConverter;

/**
 * Implements hook_preprocess_views_view().
 */
function pettys_theme_preprocess_views_view(&$variables) {
  if ($variables['view']->id() === 'best_sellers_products') {
    $session = \Drupal::request()->getSession();
    $current_currency = $session->get('commerce_currency', 'USD');
    $variables['currency_symbol'] = CurrencyConverter::getCurrencySymbol($current_currency);
  }
}

/**
 * Implements hook_twig_twig_extension().
 */
function pettys_theme_twig_extension(\Drupal\Core\Template\TwigEnvironment $env) {
  $env->addFilter(new \Twig\TwigFilter('stars', function ($value) {
    $stars = str_repeat('★', min(5, (int)$value)) . str_repeat('☆', max(0, 5 - (int)$value));
    return $stars;
  }));
}
