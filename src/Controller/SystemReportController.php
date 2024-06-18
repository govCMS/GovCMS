<?php

declare(strict_types=1);

namespace Drupal\govcms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\govcms\Lifecycle\Lifecycle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Utility\TableSort;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Returns responses for GovCMS routes.
 */
final class SystemReportController extends ControllerBase {

  /**
   * The lifecycle service.
   */
  private readonly Lifecycle $lifecycle;

  /**
   * The current request.
   */
  private readonly Request $request;

  /**
   * The controller constructor.
   */
  public function __construct(
    Lifecycle $lifecycle,
    Request $request,
    ModuleHandlerInterface $moduleHandler,
    ThemeHandlerInterface $themeHandler
  ) {
    $this->lifecycle = $lifecycle;
    $this->request = $request;
    $this->moduleHandler = $moduleHandler;
    $this->themeHandler = $themeHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('govcms.lifecycle'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('module_handler'),
      $container->get('theme_handler')
    );
  }

  /**
   * Builds the response.
   */
  public function __invoke(): array {
    return $this->buildPage();
  }

  /**
   * Builds the complete page with general info and lifecycle table.
   */
  private function buildPage(): array {
    // Build the general info section.
    $govcms_general_info_render_array = $this->buildGeneralInfoSection();

    // Build the lifecycle table.
    $lifecycle_table_render_array = $this->buildLifecycleTable();

    // Combine both sections.
    return $govcms_general_info_render_array + $lifecycle_table_render_array;
  }

  /**
   * Builds the general info section.
   */
  private function buildGeneralInfoSection(): array {
    $govcmsVersion = $this->getGovcmsVersion();

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

    return [
      '#type' => 'markup',
      '#markup' => '',
      '#prefix' => '<div class="system-status-general-info">',
      '#suffix' => '</div>',
      'items' => $govcms_general_info,
    ];
  }

  /**
   * Builds the lifecycle status table.
   */
  private function buildLifecycleTable(): array {
    // Get the lifecycle status data.
    $lifecycle_status = $this->lifecycle->getLifecycleStatus();

    // Prepare the header.
    $header = [
      ['data' => $this->t('Name'), 'field' => 'name'],
      ['data' => $this->t('Type'), 'field' => 'type'],
      ['data' => $this->t('Lifecycle'), 'field' => 'lifecycle'],
      ['data' => $this->t('Status'), 'field' => 'status', 'sort' => 'desc'],
    ];

    // Prepare the rows.
    $rows = [];
    foreach ($lifecycle_status as $type => $statuses) {
      foreach ($statuses as $lifecycle => $items) {
        foreach ($items as $item) {
          // Check if the module or theme is enabled.
          $isEnabled = false;
          if ($type === 'modules' && $this->moduleHandler->moduleExists($item)) {
            $isEnabled = true;
          }
          if ($type === 'themes' && $this->themeHandler->themeExists($item)) {
            $isEnabled = true;
          }
          $statusText = $isEnabled ? 'Enabled' : 'Disabled';
          $row_class = $isEnabled ? 'color-error' : 'color-success';

          $rows[] = [
            'data' => [
              'name' => $item,
              'type' => ucfirst($type),
              'lifecycle' => ucfirst($lifecycle),
              'status' => $statusText,
            ],
            'class' => [$row_class],
          ];
        }
      }
    }

    // Sort the rows using the custom sorting function.
    $rows = $this->_data_custom_sort($rows, $header);

    // Add the heading.
    $build['heading'] = [
      '#markup' => '<h2 class="system-status-general-info__header">Lifecycle</h2>',
    ];

    $documentationUrl = Url::fromUri('https://github.com/govCMS/GovCMS/wiki/4.2-GovCMS-Lifecycle', ['attributes' => ['target' => '_blank', 'rel' => 'noopener noreferrer']]);
    $documentationLink = Link::fromTextAndUrl($this->t('documentation'), $documentationUrl)->toString();
    $documentationText = '<p>For more information on GovCMS lifecycle statuses, please refer to our ' . $documentationLink . '.</p>';

    // Add the legend.
    $build['legend'] = [
      '#markup' => '
        <div class="lifecycle-legend">
          <ul>
            <li><strong>Deprecated:</strong> Modules and themes included in the current release but scheduled for removal due to future non-support or non-compliance with standards..</li>
            <li><strong>Obsolete:</strong> Modules and themes no longer installable on new sites and retained only for backward compatibility, recommended for replacement to avoid security or compatibility issues.</li>
            <li>' .$documentationText. '</li>
          </ul>
        </div>',
    ];

    // Build the table render array.
    $build['lifecycle_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No lifecycle status data available.'),
      '#attributes' => ['class' => ['table table-striped table-bordered tablesort-enabled']],
    ];

    // Attach library for table sorting.
    $build['#attached']['library'][] = 'core/drupal.tablesort';

    return $build;
  }

  /**
   * Return GovCMS version.
   *
   * @return mixed|string
   */
  private function getGovcmsVersion() {
    $profile = \Drupal::service('extension.list.profile')->get('govcms');
    return $profile->info['version'] ?? 'Unknown';
  }

  /**
   * Custom sorting function for table rows.
   *
   * @param array $rows
   *   The rows to be sorted.
   * @param array $header
   *   The table headers defining the sort order.
   *
   * @return array
   *   The sorted rows.
   */
  private function _data_custom_sort(array $rows, array $header): array {
    // Determine the sort field and order from the headers.
    $order_info = TableSort::getOrder($header, $this->request);
    $sort = TableSort::getSort($header, $this->request);

    // Extract the actual field name to sort by.
    $order = $order_info['sql'] ?? '';
    if (!$order) {
      // If we can't determine the order field, return the rows unsorted.
      return $rows;
    }

    // Perform the sorting.
    usort($rows, function($a, $b) use ($order, $sort) {
      $a_value = $a['data'][$order] ?? '';
      $b_value = $b['data'][$order] ?? '';

      if ($sort == 'asc') {
        return strcasecmp($a_value, $b_value);
      } else {
        return strcasecmp($b_value, $a_value);
      }
    });

    //print_r($rows);

    return $rows;
  }

}
