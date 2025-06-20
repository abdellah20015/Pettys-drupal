<?php

namespace Drupal\pettys_new_arrivals\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\pettys_new_arrivals\Service\NewArrivalsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\pettys_new_arrivals\Form\NewArrivalsForm;

/**
 * Controller for New Arrivals.
 */
class NewArrivalsController extends ControllerBase {

  protected $newArrivalsService;

  public function __construct(NewArrivalsService $new_arrivals_service) {
    $this->newArrivalsService = $new_arrivals_service;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('pettys_new_arrivals.service')
    );
  }

  /**
   * Display new arrivals form.
   */
  public function displayForm() {
    $form = $this->formBuilder()->getForm(NewArrivalsForm::class);
    $build = [];
    $build['form'] = $form;

    return $build;
  }

}
