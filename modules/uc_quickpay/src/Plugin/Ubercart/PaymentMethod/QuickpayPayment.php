<?php

/**
 * @file
 * Contains \Drupal\uc_quickpay\Plugin\Ubercart\PaymentMethod\QuickpayPayment.
 */

namespace Drupal\uc_quickpay\Plugin\Ubercart\PaymentMethod;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\uc_order\OrderInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;
use Drupal\uc_payment\OffsitePaymentMethodPluginInterface;
use Drupal\uc_payment\Entity\PaymentReceipt;
use Drupal\quickpay\Entity\Quickpay;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Quickpay payment method for Ubercart.
 *
 * @UbercartPaymentMethod(
 *   id = "quickpay",
 *   name = @Translation("Quickpay")
 * )
 */
class QuickpayPayment extends PaymentMethodPluginBase implements OffsitePaymentMethodPluginInterface {
  /**
   * ID of last payment (for capture)
   * @var int
   */
  private $lastPaymentId;

  /**
   * Last payment amount
   * @var float
   */
  private $lastPaymentAmount;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'quickpay_config' => ''
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $names = [];
    $entity_storage = \Drupal::entityTypeManager()->getStorage('quickpay_config');
    foreach ($entity_storage->loadMultiple() as $entity) {
      $entity_id = $entity->id();
      if ($label = $entity->label()) {
        $names[$entity_id] = new TranslatableMarkup('@label (@id)', ['@label' => $label, '@id' => $entity_id]);
      }
      else {
        $names[$entity_id] = $entity_id;
      }
    }

    if (empty($names)) {
      $addlink = Url::fromRoute('quickpay.configuration_add')->toString();
      $form['none'] = ['#markup' => new TranslatableMarkup('You need to create a Quickpay configuration before you can complete the Ubercart Quickpay settings. <a href="@addlink">Add new Quickpay configuration', ['@addlink' => $addlink])];
      return $form;
    }

    $form['quickpay_config'] = [
      '#type' => 'select',
      '#title' => new TranslatableMarkup('Quickpay configuration'),
      '#options' => $names,
      '#description' => new TranslatableMarkup('Here you can select the Quickpay configuration to use for this payment method.'),
      '#default_value' => $this->configuration['quickpay_config']
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['quickpay_config'] = $form_state->getValue(['settings', 'quickpay_config']);

  }

  /**
   * {@inheritdoc}
   */
  public function orderView(OrderInterface $order) {
    $this->extractLastPayment($order->id());

    $captures = db_select('uc_quickpay_capture')
      ->fields('uc_quickpay_capture', array('amount', 'capture_date'))
      ->condition('payment_id', $this->lastPaymentId)
      ->condition('order_id', $order->id())
      ->execute();

    $capture_amount = 0;

    $capture_date = [];

    if ($captures) {
      $capture = current($captures);
      $capture_amount = $capture->amount;
      $capture_date =  \Drupal::service('date.formatter')->format($capture->capture_date);
    }

    if (empty($capture_amount) && !empty($this->lastPaymentAmount)) {
      $build['capture'] = \Drupal::formBuilder()->getForm(
        'Drupal\uc_quickpay\Form\CaptureForm',
        $order->id(),
        $this->lastPaymentId,
        $this->lastPaymentAmount,
        $this->configuration['quickpay_config']
      );
    }
    elseif (!empty($date_text)) {
      $build['captureinfo'] = ['#markup' => $this->t('Captured @date', ['@date' => $date_text])];
    }
    else {
      $build = [];
    }

    return $build;
  }

  /**
   * Get sum of payments
   *
   * @param int $order_id
   *   The order ID
   * @param string $type
   *   Payment type
   *
   * @return float sum of payments
   *
   */
  private function extractLastPayment($order_id, $type = 'quickpay') {
    $ids = \Drupal::entityQuery('uc_payment_receipt')
      ->condition('order_id', $order_id)
      ->condition('method', 'quickpay')
      ->sort('received')
      ->execute();

    $this->lastPaymentAmount = 0;

    if ($ids) {
      $payments = PaymentReceipt::loadMultiple($ids);
      foreach ($payments as $payment) {
        // @todo check this
        // Check if we have an ID stored in the data object (this will
        // be the Quickpay payment ID
        if (isset($payment->data->id)) {
          $this->lastPaymentAmount = (float) $payment->amount->first()->getValue()['value'];
          $this->lastPaymentId = $payment->data->id;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildRedirectForm(array $form, FormStateInterface $form_state, OrderInterface $order = NULL) {
    $quickpay_config = Quickpay::load($this->configuration['quickpay_config']);
    if (empty($quickpay_config)) {
      $form['missing'] = ['#markup' => new TranslatableMarkup('Missing Quickpay configuration')];
      return $form;
    }
    $form = \Drupal::formBuilder()->getForm('Drupal\uc_quickpay\Form\CheckoutForm', $order, $quickpay_config);
    // See uc_payment_form_uc_cart_checkout_review_form_alter() - redirection form
    // must have key 'actions'
    $form['actions'] = [];
    return $form;
  }

}
