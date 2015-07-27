<?php
/**
 * @file
 * Contains \Drupal\quickpay\QuickpayTransaction.
 */

namespace Drupal\quickpay;

use Drupal\quickpay\Entity\Quickpay;

/**
 * Abstracts a transaction.
 */
class QuickpayTransaction {
  /**
   * The QuickPay instance related for the transaction.
   */
  protected $quickpay;

  /**
   * State flag for the transaction.
   */
  protected $loaded = FALSE;

  /**
   * Array to hold the properties of the transaction.
   */
  protected $data = [];

  /**
   * Create a transaction object.
   *
   * @param \Drupal\quickpay\Entity\Quickpay $quickpay
   *   The config for this transaction.
   * @param mixed $transaction
   *   Either the ID of the transaction or information about the transaction as
   *   array or object.
   *
   * @throws \Drupal\quickpay\QuickpayException
   *   If the transaction could not be parsed.
   */
  public function __construct(Quickpay $quickpay, $transaction) {
    $this->quickpay = $quickpay;
    // Check if the second parameter is the transaction itself, or the ID.
    if (is_object($transaction)) {
      $this->loadFromResponse($transaction);
    }
    elseif (is_numeric($transaction)) {
      $this->data['id'] = $transaction;
    }
    else {
      throw new QuickpayException(t('Transaction parameter must be either object or integer'));
    }
    // @TODO HERE;
    \Drupal::logger('quickpay')->error(print_r($transaction, TRUE));
  }

  /**
   * Set the properties of the transaction from a Quickpay response.
   *
   * The response can by example be from the callback or from a service call.
   *
   * @param mixed $response
   *   Either array or object with properties.
   *
   * @throws \Drupal\quickpay\QuickpayException
   *   If the response object could not be parsed.
   */
  private function loadFromResponse($response) {
    if (is_object($response)) {
      $response = (array) $response;
    }
    if (!is_array($response)) {
      throw new QuickpayException(t('Transaction could not be loaded from response: !response', array(
        '!response', print_r($response, TRUE),
      )));
    }
    $operation = $response['operations'][0];
    $this->data['id'] = $response['id'];
    $this->data['approved'] = $operation['qp_status_code'] == '20000';
    $this->data['order_id'] = $response['order_id'];
    $this->data['type'] = $operation['type'];
    $this->data['amount'] = $operation['amount'];
    $this->data['currency'] = $response['currency'];
    $this->data['created'] = $response['created_at'];
    $this->data['qp_status_code'] = $operation['qp_status_code'];
    $this->data['qp_status_msg'] = $operation['qp_status_msg'];
    $this->data['acquirer'] = $response['acquirer'];
    $this->data['aq_status_code'] = $operation['aq_status_code'];
    $this->data['aq_status_msg'] = $operation['aq_status_msg'];
    $this->loaded = TRUE;
  }

  /**
   * Magic get method.
   *
   * @param string $property
   *   The name of the property to get.
   *
   * @return mixed
   *   The value of the property, or FALSE otherwise.
   */
  public function __get($property) {
    // Make sure the transaction has been loaded.
    if (!$this->loaded) {
      $this->loadFromQuickpay();
    }
    if (isset($this->data[$property])) {
      return $this->data[$property];
    }
    return FALSE;
  }

  /**
   * Load transaction details from QuickPay.
   *
   * @throws \Drupal\quickpay\QuickpayException
   *   If the request could not be loaded.
   */
  private function loadFromQuickpay() {
    $transaction = $this->quickpay->request('https://api.quickpay.net/payments/' . $this->id);
    $this->loadFromResponse($transaction);
  }

}
