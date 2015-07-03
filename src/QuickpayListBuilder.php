<?php
/**
 * @file
 * Contains \Drupal\quickpay\QuickpayListBuilder.
 */

namespace Drupal\quickpay;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Defines a class to build a listing of quickpay config entities.
 *
 * @see \Drupal\quickpay\Entity\Quickpay.
 */
class QuickpayListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quickpay_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Name');
    $header['description'] = t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['description'] = SafeMarkup::checkPlain($entity->get('description'));
    return $row + parent::buildRow($entity);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    drupal_set_message(t('The quickpay configuration settings have been updated.'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = array(
        'title' => t('Edit configuration'),
        'weight' => 20,
        'url' => $entity->urlInfo('edit-form'),
      );
    }
    return $operations;
  }

}

?>
