<?php

namespace Drupal\govcms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\govcms\Lifecycle\Lifecycle;

class LifecycleReportController extends ControllerBase {

  protected $lifecycle;

  // Create method is used for dependency injection.
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('govcms.lifecycle')
    );
  }

  // Constructor to inject the Lifecycle service.
  public function __construct(Lifecycle $lifecycle) {
    $this->lifecycle = $lifecycle;
  }

  // Method to display the lifecycle report.
  public function displayLifecycle() {
    $govcmsVersion = $this->getGovcmsVersion(); // Fetch the GovCMS version.

    $lifecycleData = $this->lifecycle->getLifecycleStatus();
    $modulesTable = $this->formatDataForTable($lifecycleData['modules'], 'Modules');
    $themesTable = $this->formatDataForTable($lifecycleData['themes'], 'Themes');

    return [
      [
        '#type' => 'markup',
        '#markup' => '<h2>General System Information</h2>'
      ],
      [
        '#type' => 'markup',
        '#markup' => $this->t('GovCMS Version: @version', ['@version' => $govcmsVersion]),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      ],
      [
        '#type' => 'markup',
        '#markup' => '<h2>Lifecycle</h2>'
      ],
      $modulesTable,
      $themesTable,
    ];
  }

  // Helper method to format data for the table.
  private function formatDataForTable($data, $type) {
    $rows = [];
    foreach ($data as $status => $items) {
      foreach ($items as $item) {
        $rows[] = [$item, $status];
      }
    }
    return [
      '#theme' => 'table',
      '#header' => [$type, 'Lifecycle', 'Status', 'EOL'],
      '#rows' => $rows,
      '#empty' => $this->t('No items to display.'),
    ];
  }

  // Method to fetch the GovCMS version.
  private function getGovcmsVersion() {
    $profile = \Drupal::service('extension.list.profile')->get('govcms');
    return $profile->info['version'] ?? 'Unknown';
  }
}
