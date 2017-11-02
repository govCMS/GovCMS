<?php

namespace Drupal\govcms\Installer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class optionalForm.
 *
 * @package Drupal\govCMS\Installer\Form
 */
class optionalForm extends FormBase {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * govCMSInstallerForm constructor.
   *
   * @param ModuleInstallerInterface $module_installer
   */
  public function __construct(ModuleInstallerInterface $module_installer) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'govcms_install_optional_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Optional modules');

    $install_state = $form_state->getBuildInfo()['args'][0];

    // If we have any configurable_dependencies in the profile then show them
    // to the user so they can be selected.
    if (!empty($install_state['profile_info']['dependencies_optional'])) {
      $form['modules_optional'] = [
        '#type' => 'container',
        '#tree' => TRUE,
      ];
      foreach ($install_state['profile_info']['dependencies_optional'] as $module_name) {
        $module_path = 'profiles/govcms/modules/custom/optional/' . $module_name . '/' . $module_name . '.info.yml';
        if (file_exists($module_path) && $module_info_file = file_get_contents($module_path)) {
          $module_info = Yaml::parse($module_info_file);
          $form['modules_optional'][$module_name] = [
            '#title' => $module_info['name'],
            '#description' => !empty($module_info['description']) ? $module_info['description'] : '',
            '#type' => 'checkbox',
            '#default_value' => !empty($module_info['enabled']),
          ];
        }
        continue;
      }
    }
    else {
      $form['#suffix'] = $this->t('There are no available modules at this time.');
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#weight' => 99,
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $modules_array = $form_state->getValue('modules_optional');

    if (!empty($modules_array) && is_array($modules_array)) {
      $modules = array_filter($modules_array, function ($enabled) {
        return (bool) $enabled;
      });
      // Install optional modules.
      if (!empty($modules) && is_array($modules)) {
        $this->moduleInstaller->install(array_keys($modules));
      }
    }
  }

}
