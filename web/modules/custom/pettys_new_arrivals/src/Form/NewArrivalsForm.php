<?php

namespace Drupal\pettys_new_arrivals\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pettys_new_arrivals\Service\NewArrivalsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Form for new arrivals selection.
 */
class NewArrivalsForm extends FormBase {

  protected $newArrivalsService;
  protected $tempStore;

  public function __construct(NewArrivalsService $new_arrivals_service, PrivateTempStoreFactory $temp_store_factory) {
    $this->newArrivalsService = $new_arrivals_service;
    $this->tempStore = $temp_store_factory->get('pettys_new_arrivals');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('pettys_new_arrivals.service'),
      $container->get('tempstore.private')
    );
  }

  public function getFormId() {
    return 'pettys_new_arrivals_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Récupérer la configuration actuelle
    $config = $this->newArrivalsService->getConfiguration();
    $all_products = $this->newArrivalsService->getAllNewArrivalsProducts();
    $selected_products = $this->tempStore->get('selected_products') ?: [];

    // Section de formulaire générale

    $form['general']['section_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Titre de la section'),
      '#default_value' => $config['title'] ,
      '#required' => TRUE,
    ];

    $form['general']['section_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sous-titre de la section'),
      '#default_value' => $config['subtitle'],
      '#required' => TRUE,
    ];

    // Section de sélection des produits
    $form['products_selection'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sélection des produits') . ' (' . count($all_products) . ' disponibles)',
      '#collapsible' => FALSE,
    ];

    if (!empty($all_products)) {
      $options = [];
      foreach ($all_products as $product) {
        $options[$product->id()] = $product->getTitle() . ' (ID: ' . $product->id() . ')';
      }

      $form['products_selection']['products'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Produits à afficher'),
        '#options' => $options,
        '#default_value' => $selected_products,
        '#description' => $this->t('Sélectionnez les produits à afficher dans la section "Nouvelles Arrivées".'),
      ];
    } else {
      $form['products_selection']['no_products'] = [
        '#markup' => '<div class="messages messages--warning">' . $this->t('Aucun produit marqué comme "Nouvelle Arrivée" n\'a été trouvé. Veuillez créer des produits et cocher le champ "Est une nouvelle arrivée".') . '</div>',
      ];
    }

    // Actions
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enregistrer'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Sauvegarder la configuration générale
    $config = [
      'title' => $form_state->getValue('section_title'),
      'subtitle' => $form_state->getValue('section_subtitle'),
    ];
    $this->tempStore->set('configuration', $config);

    // Sauvegarder la sélection des produits
    $selected = array_filter($form_state->getValue('products') ?: []);
    $this->tempStore->set('selected_products', array_keys($selected));

    $this->messenger()->addMessage($this->t('Configuration mise à jour avec succès. @count produit(s) sélectionné(s).', [
      '@count' => count($selected),
    ]));
  }

}
