<?php

namespace Drupal\govcms_security\Form;

use Drupal\user\Form\UserLoginForm;
use Drupal\Core\Form\FormStateInterface;

class GovcmsLoginForm extends UserLoginForm {

    /**
     * @{inheritdoc}
     */
    public function validateFinal(array &$form, FormStateInterface $form_state) {
        if ($form_state->hasAnyErrors()) {
            return;
        }
        parent::validateFinal($form, $form_state);
    }
}
