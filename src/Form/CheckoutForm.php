<?php
/**
 * @file
 * Contains \Drupal\quickpay\Form\CheckoutForm.
 */

namespace Drupal\quickpay\Form;

use Drupal\Core\Form\FormBase;
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
    $quickpay = new Quickpay();
    $form['#method'] = 'POST';
    $form['#action'] = 'https://payment.quickpay.net';
    // Required variables.
    $data['version'] = QUICKPAY_VERSION;
    $data['merchant_id'] = $quickpay->merchant;
    $data['agreement_id'] = $quickpay->agreement;
    $data['order_id'] = $quickpay->orderPrefix . $this->order_id;
    // Ensure that Order number is at least 4 characters. Else Quickpay will
    // reject the request. @TODO - Check that this is still required for v10.
    if (strlen($data['order_id']) < 4) {
      $data['order_id'] = $quickpay->orderPrefix . '0000';
    }
    $currency_info = $quickpay->currencyInfo($this->currency);
    $data['amount'] = $quickpay->wireAmount($this->amount, $currency_info);
    $data['currency'] = $currency_info['code'];
    $data['continueurl'] = $this->continue_url;
    $data['cancelurl'] = $this->cancel_url;
    $data['callbackurl'] = \Drupal::url('quickpay.callback', array('order_id' => $this->order_id), array('absolute' => TRUE));
    // Set the optional variables.
    $data['language'] = $quickpay->getLanguage();
    $data['autocapture'] = isset($this->autocapture) && $this->autocapture ? '1' : '0';
    $data['payment_methods'] = $quickpay->getPaymentMethods();
    $data['autofee'] = $quickpay->autofee ? 1 : 0;
    // Add any custom fields.
    if (isset($this->_custom) && is_array($this->_custom)) {
      foreach ($this->_custom as $key => $value) {
        $data['variables[' . $key . ']'] = $value;
      }
    }
    // Since Quickpay is generating their checksum from all the POST parameters,
    // the value of the button (the text) is a part of the request parameter,
    // with key op.
    // @TODO - Find a better solution.
    $data['op'] = t('Continue to QuickPay');
    // Build the checksum.
    $data['checksum'] = $quickpay->getChecksum($data);
    // Build the form elements.
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
