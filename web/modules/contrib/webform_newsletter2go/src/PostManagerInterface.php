<?php

namespace Drupal\webform_newsletter2go;

/**
 * Interface PostManagerInterface.
 *
 * @package Drupal\webform_newsletter2go
 */
interface PostManagerInterface {

  /**
   * The newsletter2go API link.
   */
  const ENDPOINT = 'https://api.newsletter2go.com';

  /**
   * Creates contact.
   *
   * @param string $form_id
   *   The double opt-in registration form id.
   * @param array $data
   *   The webform submission data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function doubleOptInPost($form_id, array $data);

}
