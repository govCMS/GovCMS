<?php

namespace Drupal\govcms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\govcms\Lifecycle\Lifecycle;
use Drupal\Core\Url;
use Drupal\Core\Link;

class LifecycleReportController extends ControllerBase {

  protected $lifecycle;

  // Create method is used for dependency injection.

  public function __construct(Lifecycle $lifecycle) {
    $this->lifecycle = $lifecycle;
  }

  // Constructor to inject the Lifecycle service.

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('govcms.lifecycle')
    );
  }

  // Method to display the lifecycle report.

  public function displayLifecycle() {
    $govcmsVersion = $this->getGovcmsVersion(); // Fetch the GovCMS version.

    $lifecycleData = $this->lifecycle->getLifecycleStatus();

    $govcmsLifeCycleData = [];
    foreach ($lifecycleData as $type => $statuses) {
      foreach ($statuses as $status => $items) {
        foreach ($items as $item) {
          $govcmsLifeCycleData[] = [$item, ucfirst((string) $status), ucfirst((string) $type)];
        }
      }
    }
    $govcmsLifeCycleTable = $this->formatDataForTable($govcmsLifeCycleData);

    // Descriptions for Lifecycle statuses
    $lifecycleDescriptions = '<p><strong>Deprecated:</strong> Modules and themes included in the current release but scheduled for removal due to future non-support or non-compliance with standards.</p>'
      . '<p><strong>Obsolete:</strong> Modules and themes no longer installable on new sites and retained only for backward compatibility, recommended for replacement to avoid security or compatibility issues.</p>';

    // Link to the documentation
    $documentationLink = '<p>For more information on GovCMS lifecycle statuses, please refer to our <a href="https://github.com/govCMS/GovCMS/wiki/4.2-GovCMS-Lifecycle" target="_blank" rel="noopener noreferrer">documentation</a>.</p>';

    $govcms_general_info_item = [
      [
        '#type' => 'markup',
        '#markup' => '<h3 class="system-status-general-info__item-title">GovCMS Version</h3>' . $this->t('@version', ['@version' => $govcmsVersion]),
        '#prefix' => '<div class="system-status-general-info__item card">',
        '#suffix' => '</div>',
      ],
    ];

    $current_user = \Drupal::currentUser();

    // Check if the current user has uid 1.
    if ($current_user->id() == 1) {
      $url = Url::fromRoute('system.status');
      $link = Link::fromTextAndUrl($this->t('Core Version: @version', ['@version' => \Drupal::VERSION]), $url)->toString();

      $govcms_general_info_item[] = [
        '#type' => 'markup',
        '#markup' => '<h3 class="system-status-general-info__item-title">Drupal Status report</h3>' . $link,
        '#prefix' => '<div class="system-status-general-info__item card">',
        '#suffix' => '</div>',
      ];
    }

    $govcms_general_info_items = [
      '#type' => 'markup',
      '#markup' => '',
      '#prefix' => '<div class="system-status-general-info__items">',
      '#suffix' => '</div>',
      'items' => $govcms_general_info_item,
    ];

    $govcms_general_info = [
      [
        '#type' => 'markup',
        '#markup' => '<h2 class="system-status-general-info__header">General System Information</h2>',
      ],
      $govcms_general_info_items,
    ];

    $govcms_general_info_render_array = [
      '#type' => 'markup',
      '#markup' => '',
      '#prefix' => '<div class="system-status-general-info">',
      '#suffix' => '</div>',
      'items' => $govcms_general_info,
    ];

    return [
      $govcms_general_info_render_array,
      [
        '#type' => 'markup',
        '#markup' => '<h2 class="system-status-general-info__header">Lifecycle</h2>'
        . $lifecycleDescriptions
        . $documentationLink
      ],
      $govcmsLifeCycleTable,
    ];
  }

  // Helper method to format data for the table.

  private function getGovcmsVersion() {
    $profile = \Drupal::service('extension.list.profile')->get('govcms');
    return $profile->info['version'] ?? 'Unknown';
  }

  private function formatDataForTable($data): array {
    $rows = [];
    foreach ($data as $item) {
      $isEnabled = FALSE;

      if ($item[2] === 'Modules') {
        $isEnabled = \Drupal::service('module_handler')->moduleExists($item[0]);
      }
      elseif ($item[2] === 'Themes') {
        $isEnabled = \Drupal::service('theme_handler')->themeExists($item[0]);
      }

      $statusClass = $isEnabled ? 'color-error' : 'color-success';
      $statusText = $isEnabled ? 'Enabled' : 'Disabled';

      $rows[] = [
        'data' => [
          ['data' => $item[0], 'class' => ['column-item']],
          ['data' => $item[2], 'class' => ['column-type']],
          ['data' => $item[1], 'class' => ['column-lifecycle']],
          ['data' => $statusText, 'class' => ['column-status']],
        ],
        'class' => ['align-middle', $statusClass],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => [
        ['data' => 'Name', 'class' => ['column-name']],
        ['data' => 'Type', 'class' => ['column-type']],
        ['data' => 'Lifecycle', 'class' => ['column-lifecycle']],
        ['data' => 'Status', 'class' => ['column-status']],
      ],
      '#rows' => $rows,
      '#empty' => t('No items to display.'),
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-bordered'],
      ],
    ];
  }

}
