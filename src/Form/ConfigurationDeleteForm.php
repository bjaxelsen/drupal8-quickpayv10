<?php
/**
 * @file
 * Contains \Drupal\quickpay\Form\ConfigurationDeleteForm.
 */

namespace Drupal\quickpay\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a deletion confirmation form for Quickpay configuration entity.
 */
class ConfigurationDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the configuration %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('quickpay.configuration_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->logger('quickpay')->notice('Quickpay configuration %name has been deleted.', array('%name' => $this->entity->label()));
    drupal_set_message($this->t('Quickpay configuration %name has been deleted.', array('%name' => $this->entity->label())));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
?>
