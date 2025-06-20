<?php

namespace Drupal\newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class NewsletterSubscribeForm extends FormBase {

  public function getFormId() {
    return 'newsletter_subscribe_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'] = ['newsletter-form']; // Ajouter la classe newsletter-form au formulaire.

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Enter your email'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Enter your email'),
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#attributes' => [
        'class' => ['btn-newsletter'], // Ajouter la classe btn-newsletter au bouton.
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $created = time();

    $query = Database::getConnection()->insert('newsletter_subscriber')
      ->fields([
        'email' => $email,
        'created' => $created,
      ]);
    $query->execute();

    \Drupal::messenger()->addMessage($this->t('Thank you for subscribing with @email!', ['@email' => $email]));
  }

}
