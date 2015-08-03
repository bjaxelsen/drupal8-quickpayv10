<?php
/**
 * @file
 * Contains \Drupal\quickpay_example\Form\CheckoutForm.
 */

namespace Drupal\quickpay_example\Form;

use Drupal\quickpay\Form\CheckoutForm as QuickpayCheckoutForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\quickpay\Entity\Quickpay;

/**
 * Checkout form.
 */
class CheckoutForm extends QuickpayCheckoutForm {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quickpay_example_checkout';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL) {
    $this->quickpay = Quickpay::load('example');
    $this->order_id = $node->id();
    $this->amount = 1;
    $this->currency = 'DKK';
    $this->continue_url =  Url::fromRoute('quickpay_example.success', array(), array('absolute' => TRUE))->toString();
    $this->cancel_url =  Url::fromRoute('quickpay_example.checkout', array('node' => $node->id()), array('absolute' => TRUE))->toString();
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
