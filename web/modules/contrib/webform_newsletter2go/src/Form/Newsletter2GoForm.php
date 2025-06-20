<?php

namespace Drupal\webform_newsletter2go\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Newsletter2GoForm.
 *
 * @package Drupal\webform_newsletter2go\Form
 */
class Newsletter2GoForm extends ConfigFormBase {

  /**
   * The config name.
   */
  const CONFIG = 'webform_newsletter2go.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIG,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webform_newsletter2go_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG);
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('Enter your Newsletter2Go username.'),
      '#required' => TRUE,
      '#default_value' => $config->get('username'),
      '#weigth' => 0,
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#description' => $this->t('Enter your Newsletter2Go password.'),
      '#required' => TRUE,
      '#weigth' => 1,
    ];
    $form['auth_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Auth key'),
      '#description' => $this->t('Enter your Newsletter2Go auth key.'),
      '#required' => TRUE,
      '#default_value' => $config->get('auth_key'),
      '#weigth' => 2,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config(self::CONFIG);
    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

}
