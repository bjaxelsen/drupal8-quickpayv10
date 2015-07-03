<?php
/**
 * @file
 * Contains \Drupal\quickpay\QuickpayTransaction.
 */

namespace Drupal\quickpay;

use Drupal\quickpay\QuickpayException;

/**
 * Abstracts a transaction.
 */
class QuickpayTransaction {
  protected $loaded = FALSE;
  protected $data = [];

  /**
   * Create a transaction object.
   */
  public function __construct() {
    $arguments = func_get_args();
    // If the first argument is an object, then create the transaction.
    if (is_object($arguments[0])) {
      $request = $arguments[0];
      // We just authroized, so its always the first operation we work on.
      $operation = $request->operations[0];
      $this->data['approved'] = $operation->qp_status_code == '20000';
      $this->data['order_id'] = $request->order_id;
      $this->data['type'] = $operation->type;
      $this->data['amount'] = $operation->amount;
      $this->data['currency'] = $request->currency;
      $this->data['created'] = $request->created_at;
      $this->data['qp_status_code'] = $operation->qp_status_code;
      $this->data['qp_status_msg'] = $operation->qp_status_msg;
      $this->data['acquirer'] = $request->acquirer;
      $this->data['aq_status_code'] = $operation->aq_status_code;
      $this->data['aq_status_msg'] = $operation->aq_status_msg;
    }
    // Try to load a transaction.
    else {
      // @TODO
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

}
