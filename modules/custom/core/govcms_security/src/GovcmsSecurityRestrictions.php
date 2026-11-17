<?php

declare(strict_types=1);

namespace Drupal\govcms_security;

use Drupal\Core\Site\Settings;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Helper class for blacklisting permissions and roles.
 */
final class GovcmsSecurityRestrictions {

  /**
   * Default restricted permissions.
   */
  public const array RESTRICTED_PERMISSIONS = [
    'administer config permissions',
    'administer managed modules permissions',
    'administer modules',
    'administer permissions',
    'administer seckit',
    'administer site configuration',
    'administer software updates',
    'import configuration',
    'synchronize configuration',
    'use PHP for google analytics tracking visibility',
  ];

  /**
   * Retrieve the restricted permissions.
   *
   * @return array|string[]
   *   The restricted permissions.
   */
  public static function getRestrictedPermissions() : array {
    $restricted_permission_settings = Settings::get('govcms_security_restricted_permissions');
    return is_array($restricted_permission_settings) ? $restricted_permission_settings : self::RESTRICTED_PERMISSIONS;
  }

  /**
   * Check if a permission is restricted.
   *
   * @param string $permission
   *   The permission to check.
   *
   * @return bool
   *   TRUE if restricted.
   */
  public static function isRestrictedPermission(string $permission) : bool {
    $restricted_permissions = self::getRestrictedPermissions();
    return in_array($permission, $restricted_permissions, TRUE);
  }

  /**
   * Check if roles with 'is_admin:true' are allowed in settings.php.
   *
   * @return bool
   *  TRUE if allowed.
   */
  public static function isAdminRoleAllowed() : bool {
    $is_admin_role_allowed = Settings::get('govcms_security_allow_admin_role');
    return $is_admin_role_allowed === TRUE;
  }

  /**
   * Check if a role is restricted due to its is_admin flag.
   *
   * @param \Drupal\user\RoleInterface|string $role
   *   The rid/role to check.
   *
   * @return bool
   *   TRUE if a restricted or an invalid role is given.
   */
  public static function isRestrictedRole(RoleInterface|string $role) : bool {
    if (is_string($role)) {
      $role = Role::load($role);
      if (!$role instanceof Role) {
        // Invalid role is considered as restricted.
        return TRUE;
      }
    }
    return (!self::isAdminRoleAllowed()) && $role->isAdmin();
  }

}
