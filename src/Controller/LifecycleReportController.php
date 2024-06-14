<?php

namespace Drupal\govcms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\govcms\Lifecycle\Lifecycle;

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
          $govcmsLifeCycleData[] = [$item, ucfirst($status), ucfirst($type)];
        }
      }
    }
    $govcmsLifeCycleTable = $this->formatDataForTable($govcmsLifeCycleData);

    $govcms_general_info_item = [
      [
        '#type' => 'markup',
        '#markup' => '<h3 class="system-status-general-info__item-title">GovCMS Version</h3>' . $this->t('@version', ['@version' => $govcmsVersion]),
        '#prefix' => '<div class="system-status-general-info__item card">',
        '#suffix' => '</div>',
      ],
    ];

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
        '#markup' => '<h2 class="system-status-general-info__header">Lifecycle</h2>',
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
      $isEnabled = \Drupal::service('module_handler')->moduleExists($item[0]);
      $statusClass = $isEnabled ? 'color-error' : 'color-success';
      $statusText = $isEnabled ? 'Enabled' : 'Disabled';

      $rows[] = [
        'data' => [
          ['data' => $item[0], 'class' => ['column-item']],
          ['data' => $item[2], 'class' => ['column-type']],
          ['data' => $item[1], 'class' => ['column-lifecycle']],
          ['data' => $statusText, 'class' => ['column-status']],
          ['data' => 'EOL Placeholder', 'class' => ['column-eol']],
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
        ['data' => 'EOL', 'class' => ['column-eol']],
      ],
      '#rows' => $rows,
      '#empty' => t('No items to display.'),
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-bordered'],
      ],
    ];
  }


}
