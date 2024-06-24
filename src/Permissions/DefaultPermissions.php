<?php

namespace Drupal\govcms\Permissions;

use Drupal\user\RoleInterface;

/**
 * Defines a class for managing default permissions.
 */
final class DefaultPermissions {

  public const array DEFAULT_PERMISSIONS = [
    'role_delegation' => [
      'govcms_site_administrator' => [
        'assign govcms_content_approver role',
        'assign govcms_content_author role',
        'assign govcms_site_administrator role',
      ],
    ],
    'module_permissions' => [
      'govcms_site_administrator' => [
        'administer managed modules',
        'administer managed modules permissions',
      ],
    ],
    'securitytxt' => [
      RoleInterface::ANONYMOUS_ID => ['view securitytxt'],
      RoleInterface::AUTHENTICATED_ID => ['view securitytxt'],
    ],
  ];

}
