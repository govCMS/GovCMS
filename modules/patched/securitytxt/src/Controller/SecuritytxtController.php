<?php

namespace Drupal\securitytxt\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\securitytxt\SecuritytxtSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for securitytxt routes.
 */
class SecuritytxtController extends ControllerBase {

  /**
   * The Securitytxt serializer.
   *
   * @var \Drupal\securitytxt\SecuritytxtSerializer
   */
  protected $serializer;

  /**
   * A 'securitytxt.settings' config instance.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * Construct a new SecuritytxtController.
   *
   * @param \Drupal\securitytxt\SecuritytxtSerializer $serializer
   *   The Securitytxt serializer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(SecuritytxtSerializer $serializer, ConfigFactoryInterface $config_factory) {
    $this->serializer = $serializer;
    $this->settings = $config_factory->get('securitytxt.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('securitytxt.serializer'),
      $container->get('config.factory')
    );
  }

  /**
   * Get the security.txt file as a response object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The security.txt file as a response object with a content type of
   *   'text/plain'.
   */
  public function securitytxtFile() {
    $content = $this->serializer->getSecuritytxtFile($this->settings);
    $response = new Response($content, 200, ['Content-Type' => 'text/plain']);

    return $response;
  }

  /**
   * Get the security.txt.sig file as a response object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The security.txt.sig file as a response object with a content type of
   *   'text/plain'.
   */
  public function securitytxtSignature() {
    $content = $this->serializer->getSecuritytxtSignature($this->settings);
    $response = new Response($content, 200, ['Content-Type' => 'text/plain']);

    return $response;
  }

}
