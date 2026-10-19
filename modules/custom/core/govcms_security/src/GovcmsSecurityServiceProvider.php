<?php

declare(strict_types=1);

namespace Drupal\govcms_security;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Implements a service provider.
 */
class GovcmsSecurityServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function register(ContainerBuilder $container): void {
    $modules = $container->getParameter('container.modules') ?? [];
    // Only register OAuth Hooks if Simple OAuth is enabled.
    if (isset($modules['simple_oauth'])) {
      $container->register('govcms_security.oauth_hooks', 'Drupal\govcms_security\Hook\OauthHooks')
        ->setAutowired(TRUE);
    }
  }

}
