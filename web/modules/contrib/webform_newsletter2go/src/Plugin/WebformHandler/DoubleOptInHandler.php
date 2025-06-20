<?php

namespace Drupal\webform_newsletter2go\Plugin\WebformHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformMessageManagerInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Drupal\webform_newsletter2go\PostManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform submission newsletter2go double opt-in handler.
 *
 * @WebformHandler(
 *   id = "newsletter2go_double_opt_in",
 *   label = @Translation("Newsletter2Go double opt-in form"),
 *   category = @Translation("External"),
 *   description = @Translation("Posts webform submissions to Newsletter2Go."),
 *   cardinality =
 *   \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results =
 *   \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission =
 *   \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 *   tokens = TRUE,
 * )
 */
class DoubleOptInHandler extends WebformHandlerBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The token manager.
   *
   * @var \Drupal\webform\WebformTokenManagerInterface
   */
  protected $tokenManager;

  /**
   * The webform message manager.
   *
   * @var \Drupal\webform\WebformMessageManagerInterface
   */
  protected $messageManager;

  /**
   * A webform element plugin manager.
   *
   * @var \Drupal\webform\Plugin\WebformElementManagerInterface
   */
  protected $elementManager;

  /**
   * The newsletter2go post manager.
   *
   * @var \Drupal\webform_newsletter2go\PostManagerInterface
   */
  protected $postManager;

  /**
   * List of unsupported webform submission properties.
   *
   * The below properties will not being included in a remote post.
   *
   * @var array
   */
  protected $unsupportedProperties = [
    'metatag',
  ];


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

      $instance = new static($configuration, $plugin_id, $plugin_definition);

      $instance->loggerFactory = $container->get('logger.factory');
      $instance->configFactory = $container->get('config.factory');
      $instance->renderer = $container->get('renderer');
      $instance->entityTypeManager = $container->get('entity_type.manager');
      $instance->conditionsValidator = $container->get('webform_submission.conditions_validator');
      $instance->tokenManager = $container->get('webform.token_manager');
      $instance->moduleHandler = $container->get('module_handler');
      $instance->messageManager = $container->get('webform.message_manager');
      $instance->elementManager = $container->get('plugin.manager.webform.element');
      $instance->postManager = $container->get('webform_newsletter2go.post_manager');

      $instance->setConfiguration($configuration);

      return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $configuration = $this->getConfiguration();
    $settings = $configuration['settings'];

    return [
      '#settings' => $settings,
    ] + parent::getSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $field_names = array_keys(\Drupal::service('entity_field.manager')->getBaseFieldDefinitions('webform_submission'));
    $excluded_data = array_combine($field_names, $field_names);
    return [
      'form_id'  => '',
      'excluded_data' => $excluded_data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $webform = $this->getWebform();
    $form['form_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form ID'),
      '#description' => $this->t('Enter your Newsletter2Go form id.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['form_id'],
    ];
    // Submission data.
    $form['submission_data'] = [
      '#type' => 'details',
      '#title' => $this->t('Submission data'),
    ];
    $form['submission_data']['excluded_data'] = [
      '#type' => 'webform_excluded_columns',
      '#title' => $this->t('Posted data'),
      '#title_display' => 'invisible',
      '#webform_id' => $webform->id(),
      '#required' => TRUE,
      '#default_value' => $this->configuration['excluded_data'],
    ];
    $this->elementTokenValidate($form);
    return $this->setSettingsParents($form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->applyFormStateToConfiguration($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get state of the webform submission.
    $state = $webform_submission->getWebform()->getSetting('results_disabled') ? WebformSubmissionInterface::STATE_COMPLETED : $webform_submission->getState();
    if ($state == 'completed') {
      $data = $this->getRequestData($webform_submission);
      $this->postManager->doubleOptInPost($this->configuration['form_id'], $data);
    }
  }

  /**
   * Get a webform submission's request data.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   The webform submission to be posted.
   *
   * @return array
   *   A webform submission converted to an associative array.
   */
  protected function getRequestData(WebformSubmissionInterface $webform_submission) {
    // Get submission and elements data.
    $data = $webform_submission->toArray(TRUE);

    // Remove unsupported properties from data.
    // These are typically added by other module's like metatag.
    $unsupported_properties = array_combine($this->unsupportedProperties, $this->unsupportedProperties);
    $data = array_diff_key($data, $unsupported_properties);

    // Flatten data and prioritize the element data over the
    // webform submission data.
    $element_data = $data['data'];
    unset($data['data']);
    $data = $element_data + $data;

    // Excluded selected submission data.
    $data = array_diff_key($data, $this->configuration['excluded_data']);

    // Replace tokens.
    $data = $this->replaceTokens($data, $webform_submission);

    return $data;
  }

}
