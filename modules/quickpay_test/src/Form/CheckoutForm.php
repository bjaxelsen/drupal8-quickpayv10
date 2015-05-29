<?php
/**
 * @file
 * Contains \Drupal\quickpay_test\Form\CheckoutForm.
 */

namespace Drupal\quickpay_test\Form;

use Drupal\quickpay\Quickpay;
use Drupal\quickpay\Form\CheckoutForm as QuickpayCheckoutForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Checkout form.
 */
class CheckoutForm extends QuickpayCheckoutForm {
  public function __construct() {
    $this->order_id = $_SESSION['order_id'];
    $this->amount = 100;
    $this->currency = 'DKK';
    $this->continue_url = \Drupal::url('quickpay_test.success', array(), array('absolute' => TRUE));
    $this->cancel_url = \Drupal::url('quickpay_test.checkout', array('order_id' => $this->order_id), array('absolute' => TRUE));
  }

  /**
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'quickpay_test_checkout';
  }

  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
