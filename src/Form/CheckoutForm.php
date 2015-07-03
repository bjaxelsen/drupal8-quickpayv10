<?php
/**
 * @file
 * Contains \Drupal\quickpay\Form\CheckoutForm.
 */

namespace Drupal\quickpay\Form;

use Drupal\Core\Form\FormBase;
use Drupal\quickpay\CheckoutFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quickpay\Quickpay;
use Drupal\quickpay\QuickpayException;

/**
 * The checkout form for redirect users to QuickPay for payment.
 *
 * This is an abstract class, since custom modules must extend this form
 * in their own checkout forms.
 * Add custom variables to send to quickpay by adding them to $this->_custom.
 *
 * @TODO: Add possiblity for other msgtypes.
 */
abstract class CheckoutForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Make sure all the required properties are set on the form.
    $this->validateImplementation();
    // Build the form.
    $form['#method'] = 'POST';
    $form['#action'] = 'https://payment.quickpay.net';
    // Required variables.
    $data['version'] = QUICKPAY_VERSION;
    $data['merchant_id'] = $this->quickpay->merchant_id;
    $data['agreement_id'] = $this->quickpay->agreement_id;
    $data['order_id'] = $this->quickpay->orderPrefix . $this->order_id;
    // Ensure that Order number is at least 4 characters. Else Quickpay will
    // reject the request.
    if (strlen($data['order_id']) < 4) {
      $data['order_id'] = $this->quickpay->order_prefix . substr('0000' . $this->order_id, -4 + strlen($this->quickpay->order_prefix));
    }
    $currency_info = $this->quickpay->currencyInfo($this->currency);
    $data['amount'] = $this->quickpay->wireAmount($this->amount, $currency_info);
    $data['currency'] = $currency_info['code'];
    $data['continueurl'] = $this->continue_url;
    $data['cancelurl'] = $this->cancel_url;
    $data['callbackurl'] = \Drupal::url('quickpay.callback', array('order_id' => $this->order_id), array('absolute' => TRUE));
    // Set the optional variables.
    $data['language'] = $this->quickpay->getLanguage();
    $data['autocapture'] = isset($this->quickpay->autocapture) && $this->quickpay->autocapture ? '1' : '0';
    $data['payment_methods'] = $this->quickpay->getPaymentMethods();
    $data['autofee'] = $this->quickpay->autofee ? 1 : 0;
    // Add the ID of the Quickpay configuration as a custom variable to load it
    // in the response.
    $data['variables[quickpay_configuration_id]'] = $this->quickpay->id;
    // Add any custom fields.
    if (isset($this->_custom) && is_array($this->_custom)) {
      foreach ($this->_custom as $key => $value) {
        $data['variables[' . $key . ']'] = $value;
      }
    }
    // Build the checksum.
    $data['checksum'] = $this->quickpay->getChecksum($data);
    // Add all data elements as hidden input fields.
    foreach ($data as $name => $value) {
      $form[$name] = array('#type' => 'hidden', '#value' => $value);
    }
    $form['submit'] = array(
      '#id' => 'quickpay-submit-button',
      '#type' => 'submit',
      '#value' => t('Continue to QuickPay'),
    );
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

  /**
   * Validates the implementation.
   *
   * Checking if the required properties has been set.
   */
  private function validateImplementation() {
    // Make sure the required variables are available.
    if (!isset($this->quickpay)) {
      throw new QuickpayException(t('Concrete must define "order_id".'));
    }
    if (!isset($this->order_id)) {
      throw new QuickpayException(t('Concrete must define "order_id".'));
    }
    if (!isset($this->amount)) {
      throw new QuickpayException(t('Concrete must define "amount".'));
    }
    if (!isset($this->currency)) {
      throw new QuickpayException(t('Concrete must define "currency".'));
    }
    if (!isset($this->continue_url)) {
      throw new QuickpayException(t('Concrete must define "continue_url".'));
    }
    if (!isset($this->cancel_url)) {
      throw new QuickpayException(t('Concrete must define "cancel_url".'));
    }
  }

}
