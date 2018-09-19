<?php

namespace Drupal\govcms8_calendar_item\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Calendar item date.
 *
 * @DsField(
 *   id = "calendar_item_date",
 *   title = @Translation("Calendar Item Date"),
 *   entity_type = "node",
 *   ui_limit = {"*|calendar_item"}
 * )
 */
class CalendarItemDate extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['date field'] = [
      '#type' => 'textfield',
      '#title' => 'Field',
      '#default_value' => $config['date field'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();

    $summary = [];
    $summary[] = 'Field: ' . $config['date field'];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $configuration = [
      'date field' => 'created',
    ];

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $config = $this->getConfiguration();
    $datetime = [];

    $date_field = $config['date field'];
    if (!empty($date_field) && $this->entity()->hasField($date_field)) {
      $field = $this->entity()->get($date_field)->value;
      $timestamp = strtotime($field);
      if ($timestamp > 0) {
        $datetime = DrupalDateTime::createFromTimestamp($timestamp);
      }
      else {
        $datetime = DrupalDateTime::createFromTimestamp($field);
      }
      $build = [
        '#theme' => 'calendar_item_date',
        '#datetime' => $datetime,
        '#attached' => [
          'library' => [
            'govcms8_calendar_item/calendar_item',
          ],
        ],
      ];
    }
    return $build;
  }

}
