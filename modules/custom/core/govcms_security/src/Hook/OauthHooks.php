<?php

declare(strict_types=1);

namespace Drupal\govcms_security\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\govcms_security\GovcmsSecurityRestrictions;
use Drupal\simple_oauth\Entity\Oauth2Scope;
use Drupal\simple_oauth\Plugin\ScopeGranularity\Permission as ScopeGranularityPermission;
use Drupal\simple_oauth\Plugin\ScopeGranularity\Role as ScopeGranularityRole;
use Drupal\user\RoleInterface;

/**
 * Provides hooks for Simple OAuth.
 */
class OauthHooks {

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for oauth2_scope_form.
   *
   * @see \Drupal\simple_oauth\Entity\Form\Oauth2ScopeForm::form()
   * @see \Drupal\simple_oauth\Plugin\ScopeGranularity\Role::buildConfigurationForm()
   * @see \Drupal\simple_oauth\Plugin\ScopeGranularity\Permission::buildConfigurationForm()
   */
  #[Hook('form_oauth2_scope_form_alter')]
  public function oauth2ScopeFormAlter(array &$form, FormStateInterface $form_state, string $form_id) : void {
    if (!isset($form['parent_and_granularity']['granularity_configuration'])) {
      return;
    }

    $subform = &$form['parent_and_granularity']['granularity_configuration'];
    $restricted_roles_removed = FALSE;
    $restricted_permissions_removed = FALSE;

    // Remove restricted roles from OAuth2 Scope granularity.
    if (isset($subform['role']['#options'])) {
      foreach (array_keys($subform['role']['#options']) as $rid) {
        if (GovcmsSecurityRestrictions::isRestrictedRole($rid)) {
          unset($subform['role']['#options'][$rid]);
          $restricted_roles_removed = TRUE;
        }
      }
    }
    if (isset($subform['role']['#default_value'])) {
      $role = $subform['role']['#default_value'];
      // Role value can be empty or set to a restricted one via config import.
      if (empty($role) || GovcmsSecurityRestrictions::isRestrictedRole($role)) {
        $subform['role']['#default_value'] = '';
        // Force the select list to show an empty option so that the first role
        // in the list is not selected by the browser.
        $subform['role']['#empty_value'] = '';
        $restricted_roles_removed = TRUE;
      }
    }

    // Remove restricted permissions from OAuth2 Scope granularity.
    if (isset($subform['permission']['#options'])) {
      foreach ($subform['permission']['#options'] as $provider => $permissions) {
        foreach (array_keys($permissions) as $permission) {
          if (GovcmsSecurityRestrictions::isRestrictedPermission($permission)) {
            unset($subform['permission']['#options'][$provider][$permission]);
            $restricted_permissions_removed = TRUE;
          }
        }
      }
    }
    if (isset($subform['permission']['#default_value'])) {
      $permission = $subform['permission']['#default_value'];
      // Permission can be empty due to hook_oauth2_scope_load() or set to
      // a restricted one via config import.
      if (empty($permission) || GovcmsSecurityRestrictions::isRestrictedPermission($permission)) {
        $subform['permission']['#default_value'] = '';
        // Force the select list to show an empty option so that the first
        // permission in the list is not selected by the browser.
        $subform['permission']['#empty_value'] = '';
        $restricted_permissions_removed = TRUE;
      }
    }

    // Add a warning text.
    if ($restricted_roles_removed || $restricted_permissions_removed) {
      $subform['govcms_security_warning_wrapper'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['messages', 'messages--warning'],
        ],
        '#weight' => -10,
        'govcms_security_warning' => [
          '#type' => 'markup',
          '#prefix' => '<p>',
          '#markup' => new TranslatableMarkup('Restricted roles and permissions are removed by govcms_security.'),
          '#suffix' => '</p>',
        ],
      ];
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_presave() for oauth2_scope entity.
   */
  #[Hook('oauth2_scope_presave')]
  public function oauth2ScopePresave(EntityInterface $scope) : void {
    if ($scope instanceof Oauth2Scope) {
      $this->applyOauth2ScopeRestrictions($scope, 'presave');
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_load() for oauth2_scope entity.
   */
  #[Hook('oauth2_scope_load')]
  public function oauth2ScopeLoad(array $scopes) : void {
    foreach ($scopes as $scope) {
      if ($scope instanceof Oauth2Scope) {
        $this->applyOauth2ScopeRestrictions($scope, 'load');
      }
    }
  }

  /**
   * Apply govcms_security restrictions to an OAuth2 Scope entity.
   *
   * @param \Drupal\simple_oauth\Entity\Oauth2Scope $scope
   *   The scope entity.
   * @param string $phase
   *   The phase when the restrictions are applied (for logging purpose).
   *
   * @return bool
   *   TRUE if any restriction is applied. The altered entity is not saved.
   */
  protected function applyOauth2ScopeRestrictions(Oauth2Scope $scope, string $phase = 'load') : bool {
    $granularity = $scope->getGranularity();
    $configuration = $granularity->getConfiguration();
    $has_changed = FALSE;
    if ($granularity instanceof ScopeGranularityRole) {
      $role = $configuration['role'] ?? NULL;
      if (!empty($role) && GovcmsSecurityRestrictions::isRestrictedRole($role)) {
        // Override the restricted role with anonymous.
        $configuration['role'] = RoleInterface::ANONYMOUS_ID;
        $has_changed = TRUE;
        \Drupal::logger('govcms_security')->alert(sprintf('[%s] The restricted role "%s" was replaced with "anonymous" in OAuth2 Scope "%s".', $phase, $role, $scope->id()));
      }
    }
    elseif ($granularity instanceof ScopeGranularityPermission) {
      $permission = $configuration['permission'] ?? NULL;
      if (!empty($permission) && GovcmsSecurityRestrictions::isRestrictedPermission($permission)) {
        // There is no minimum permission in Drupal. Remove it instead.
        $configuration['permission'] = '';
        $has_changed = TRUE;
        \Drupal::logger('govcms_security')->alert(sprintf('[%s] The restricted permission "%s" was removed from OAuth2 Scope "%s".', $phase, $permission, $scope->id()));
      }
    }

    if ($has_changed) {
      $granularity->setConfiguration($configuration);
      $scope->set('granularity_configuration', $configuration);
    }

    return $has_changed;
  }

}
