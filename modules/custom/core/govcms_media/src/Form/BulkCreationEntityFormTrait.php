<?php

namespace Drupal\govcms_media\Form;

use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a redirect chain in entity forms for bulk entity creation.
 *
 * Code from \Drupal\lightning_core\Form\BulkCreationEntityFormTrait
 * in lightning_core 8.x-2.6 submodule.
 */
trait BulkCreationEntityFormTrait {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $query = $this->getRequest()->query;

    if ($query->has('bulk_create')) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $this->getEntity();

      // If there are more entities to create, redirect to the edit form for the
      // next one in line.
      $queue = $query->get('bulk_create', []);

      if (is_array($queue)) {
        $id = array_shift($queue);

        $redirect = $this->entityTypeManager
          ->getStorage($entity->getEntityTypeId())
          ->load($id)
          ->toUrl('edit-form', [
            'query' => [
              'bulk_create' => $queue ?: TRUE,
            ],
          ]);
        $form_state->setRedirectUrl($redirect);
      }
      // Otherwise, try to redirect to the entity type's collection.
      else {
        try {
          $form_state->setRedirectUrl($entity->toUrl('collection'));
        }
        catch (UndefinedLinkTemplateException $e) {
          // The entity type does not declare a collection, so don't do
          // anything.
        }
      }
    }
  }

}
