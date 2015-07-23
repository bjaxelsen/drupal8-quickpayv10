<?php
/**
 * @file
 * Contains \Drupal\quickpay\QuickpayTransaction.
 */

namespace Drupal\quickpay;

/**
 * Abstracts a transaction.
 */
class QuickpayTransaction {
  /**
   * The QuickPay instance related for the transaction.
   */
  protected $quickpay;

  /**
   * @var array
   */
  protected $data = [];

  /**
   * Create a transaction object.
   */
  public function __construct($quickpay, $id = FALSE) {
    $this->quickpay = $quickpay;
    if ($id) {
      $this->data['id'] = $id;
    }
  }

  public function loadFromResponse($response) {
    $response = (array) $response;
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
  }

  /**
   * Magic get method.
   *
   * @return mixed
   *   The value of the property, or FALSE otherwise.
   */
  public function __get($property) {
    if (isset($this->data[$property])) {
      return $this->data[$property];
    }
    return FALSE;
  }


  public function status() {
    $url = 'https://api.quickpay.net/payments/' . $this->id;
    $client = \Drupal::httpClient();
    $response = $client->get($url, [
        'auth' => ['', $this->quickpay->get('api_key')],
        'headers' => [
          'Accept-Version' => QUICKPAY_VERSION,
        ],
      ]
    );
    try {
      $this->loadFromResponse($response->json());
    }
    catch (Exception $e) {
      die(print_r($e->getMessage()));
    }
  }
}
