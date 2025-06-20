<?php

namespace Drupal\webform_newsletter2go;

use \Drupal\Core\Utility\Error;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * Helper newsletter2go class.
 *
 * @package Drupal\webform_newsletter2go
 */
class PostManager implements PostManagerInterface {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * PostManager constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory service.
   */
  public function __construct(ClientInterface $httpClient, StateInterface $state, LoggerChannelFactoryInterface $loggerFactory, ConfigFactory $configFactory) {
    $this->httpClient = $httpClient;
    $this->state = $state;
    $this->loggerFactory = $loggerFactory;
    $this->config = $configFactory->getEditable('webform_newsletter2go.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function doubleOptInPost($form_id, $data) {
    // Check authentication.
    if (!$this->state->get('webform_newsletter2go.access_token.expire') || ($this->state->get('webform_newsletter2go.access_token.expire') && time() >= $this->state->get('webform_newsletter2go.access_token.expire'))) {
      $this->authenticate();
    }

    // Email is required field.
    if (!empty($data['email'])) {
      try {
        $this->httpClient->request('POST', self::ENDPOINT . '/forms/submit/' . trim($form_id), [
          'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->state->get('webform_newsletter2go.access_token'),
          ],
          'json' => [
            'recipient' => $data,
          ],
        ]);
      }
      catch (\Exception $e) {
        Error::logException($this->loggerFactory->get('webform_newsletter2go'), $e);
      }
    }
  }

  /**
   * Provides user authentication.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function authenticate() {
    try {
      $request = $this->httpClient->request('POST', self::ENDPOINT . '/oauth/v2/token', [
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Basic ' . base64_encode($this->config->get('auth_key')),
        ],
        'json' => [
          'username' => $this->config->get('username'),
          'password' => $this->config->get('password'),
          'grant_type' => 'https://nl2go.com/jwt',
        ],
      ]);
      if ($request->getReasonPhrase() == 'OK') {
        $response = json_decode($request->getBody()->getContents());
        $this->state->set('webform_newsletter2go.access_token.expire', time() + $response->expires_in);
        $this->state->set('webform_newsletter2go.access_token', $response->access_token);
      }
    }
    catch (\Exception $e) {
      Error::logException($this->loggerFactory->get('webform_newsletter2go'), $e);
    }
  }

}
