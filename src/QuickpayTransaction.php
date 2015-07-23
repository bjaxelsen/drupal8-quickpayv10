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
  protected $data = [];

  /**
   * Create a transaction object.
   */
  public function __construct($quickpay) {
    $arguments = func_get_args();
    // If the first argument is an object, then create the transaction.
    if (isset($arguments[1])) {
      if (is_object($arguments[1])) {
        $request = $arguments[1];
        $content = json_decode($request->getContent());
        // We just authorized, so its always the first operation we work on.
        $operation = $content->operations[0];
        $this->data['id'] = $content->id;
        $this->data['approved'] = $operation->qp_status_code == '20000';
        $this->data['order_id'] = $content->order_id;
        $this->data['type'] = $operation->type;
        $this->data['amount'] = $operation->amount;
        $this->data['currency'] = $content->currency;
        $this->data['created'] = $content->created_at;
        $this->data['qp_status_code'] = $operation->qp_status_code;
        $this->data['qp_status_msg'] = $operation->qp_status_msg;
        $this->data['acquirer'] = $content->acquirer;
        $this->data['aq_status_code'] = $operation->aq_status_code;
        $this->data['aq_status_msg'] = $operation->aq_status_msg;
      }
      elseif (is_numeric($arguments[1])) {
        $this->data['id'] = $arguments[1];
      }
    }
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
    $client = \Drupal::httpClient();
die(print_r($client));
    $response = $client->get('https://api.quickpay.net/payments/91514', [
        'auth' => ['', '0aafb6882488d290d5cf64768528ede0eb581ab4409a4a3240abc5339539f4d0'],
        'headers' => [
          'Accept-Version' => 'v10',
        ],
      ]
    );
    die(print_r($response->getBody()));
die('tid: ' .$this->id);
    if (!is_array($request_data)) {
      $request_data = (array) $request_data;
    }

    $request_data['protocol'] = QUICKPAY_VERSION_API;
    $request_data['msgtype'] = $type;
    $request_data['merchant'] = $this->merchant;
    $request_data['md5check'] = $this->md5checksum($request_data);

    if ($this->debug) {
      debug($request_data, 'Quickpay request parameters');
    }

    if (!$this->validate_request($request_data)) {
      throw new QuickpayException(t("Request message didn't pass validation."));
    }
    $request_options = array(
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
      'method' => 'POST',
      'data' => http_build_query($request_data, FALSE, '&'),
      'max_redirects' => 0,
    );
    /*
     * We're using curl as drupal_http_request cannot be used in unit tests.
     */
    if (!function_exists('curl_init')) {
      throw new QuickpayException(t("cURL not found."));
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://secure.quickpay.dk/api');
    curl_setopt($ch, CURLOPT_POST, count($request_data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_data, FALSE, '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($ch);


    if ($this->debug) {
      debug($response, 'Quickpay response');
    }

    if (!$response) {
      throw new QuickpayException(t('Server returned non-success code or empty result'));
    }
    $transaction->load_response($this->response($response));

    return $transaction;
  }
}
