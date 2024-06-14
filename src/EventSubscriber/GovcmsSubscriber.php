<?php

namespace Drupal\govcms\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\govcms\Permissions\DefaultPermissions;
use Drupal\user\Entity\Role;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\govcms\Event\ModuleInstalledEvent;

/**
 * GovCMS event subscriber.
 */
class GovcmsSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a GovcmsSubscriber object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ModuleInstalledEvent::MODULES_INSTALLED => ['onModulesInstalled'],
    ];
  }

  /**
   * Subscribe to the modules installed event dispatched.
   *
   * @param \Drupal\govcms\Event\ModuleInstalledEvent $event
   *
   * @return void
   */
  public function onModulesInstalled(ModuleInstalledEvent $event) {
    $modules = $event->modules;
    if (!empty($modules)) {
      foreach ($event->modules as $module) {
        if (isset(DefaultPermissions::DEFAULT_PERMISSIONS[$module])) {
          $this->grantPermissionsOnModuleInstalled($module);
        }
      }
    }
  }

  /**
   * Grant permissions when the module is installed.
   */
  public function grantPermissionsOnModuleInstalled($module) {
    // Check if the specific module is installed.
    if (\Drupal::moduleHandler()->moduleExists($module)) {
      $permissions = DefaultPermissions::DEFAULT_PERMISSIONS[$module];
      foreach ($permissions as $role_name => $perms) {
        // Grant permissions to the user role.
        // user_role_grant_permissions($role_name, $permissions);
        if ($role = Role::load($role_name)) {
          foreach ($perms as $perm) {
            $role->grantPermission($perm);
          }
          $role->save();
        }

        // Optionally, log a message.
        $this->messenger->addStatus($this->t('Permissions granted to %role.', ['%role' => $role_name]));
      }
    }
  }

}
