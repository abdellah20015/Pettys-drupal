<?php

namespace Drupal\pettys_new_arrivals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\pettys_new_arrivals\Service\NewArrivalsService;

/**
 * Provides a New Arrivals Block.
 *
 * @Block(
 *   id = "new_arrivals_block",
 *   admin_label = @Translation("New Arrivals Block"),
 *   category = @Translation("Pettys")
 * )
 */
class NewArrivalsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The new arrivals service.
   *
   * @var \Drupal\pettys_new_arrivals\Service\NewArrivalsService
   */
  protected $newArrivalsService;

  /**
   * Constructs a new NewArrivalsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\pettys_new_arrivals\Service\NewArrivalsService $new_arrivals_service
   *   The new arrivals service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    NewArrivalsService $new_arrivals_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->newArrivalsService = $new_arrivals_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('pettys_new_arrivals.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Récupération la configuration (Form) et les produits
    $config = $this->newArrivalsService->getConfiguration();
    $products = $this->newArrivalsService->getSelectedProducts();

    // Si aucun produit, ne pas afficher le bloc
    if (empty($products)) {
      return [
        '#markup' => '<div class="new-arrivals-empty">' . $this->t('No new arrivals products selected.') . '</div>',
      ];
    }

    // Valeurs par défaut pour la configuration (Form)
    $section_title = !empty($config['title']) ? $config['title'] : $this->t('New Arrivals');
    $section_subtitle = !empty($config['subtitle']) ? $config['subtitle'] : $this->t('Discover our latest products');

    return [
      // rendu du bloc
      '#theme' => 'pettys_new_arrivals',
      // variables pour cette bloc
      '#new_arrivals_products' => $products,
      '#section_title' => $section_title,
      '#section_subtitle' => $section_subtitle,
      // assets css et js
      '#attached' => [
        'library' => ['pettys_new_arrivals/new-arrivals'],
      ],
    ];
  }

}
