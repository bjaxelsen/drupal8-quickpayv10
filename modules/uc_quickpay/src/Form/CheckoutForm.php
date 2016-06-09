<?php
/**
 * @file
 * Implementation of checkout form that will go to Quickpay
 */

namespace  Drupal\uc_quickpay\Form;

use Drupal\quickpay\Form\CheckoutForm as QuickpayCheckoutForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\quickpay\Entity\Quickpay;
use Drupal\uc_order\Entity\Order;
use Drupal\Core\Url;

class CheckoutForm extends QuickpayCheckoutForm {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_quickpay_checkout';
  }

  /**
   * Build the checkout form
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\uc_order\Entity\Order
   *   The order object
   * @param \Drupal\quickpay\Entity\Quickpay $quickpay_config
   *   The Quickpay configuration
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, Order $order = NULL, $quickpay_config = NUYLL) {
    $this->quickpay = $quickpay_config;
    $this->order_id = $order->id();
    $this->amount = $order->getTotal();
    $this->currency = $order->getCurrency();
    $this->continue_url =  Url::fromRoute('uc_cart.checkout_complete', array(), array('absolute' => TRUE))->toString();
    $this->cancel_url =  Url::fromRoute('uc_cart.checkout_review', array(), array('absolute' => TRUE))->toString();
    $form = parent::buildForm($form, $form_state);
    return $form;
  }
}
