<?php

/**
 * @file
 * Contains \Drupal\quickpay\QuickpayInterface.
 */

namespace Drupal\quickpay;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a quickpay entity.
 */
interface QuickpayInterface extends ConfigEntityInterface {

  /**
   * Get the language of the user.
   *
   * @return string
   *   The language code. Defaults to 'en'.
   */
  public function getLanguage();

  /**
   * Get information about an currency.
   *
   * @param string $code
   *   The ISO 4217 currency code.
   *
   * @return mixed
   *   An array with the keys 'code' and 'multiplier'.
   *
   * @throws \Drupal\quickpay\QuickpayException
   *   If the currency code could not be parsed.
   */
  public function currencyInfo($code);

  /**
   * Returns the amount adjusted by the multiplier for the currency.
   *
   * @param float $amount
   *   The amount to wire.
   * @param array $currency_info
   *   An currency_info() array.
   *
   * @return string
   *   The adjusted amount.
   */
  public function wireAmount($amount, array $currency_info);

  /**
   * Reverses wireAmount().
   *
   * @param int $amount
   *   The amount to un-wire.
   * @param array $currency_info
   *   An currency_info() array.
   *
   * @return float|string
   *   The unadjusted amount.
   */
  public function unwireAmount($amount, array $currency_info);

  /**
   * Return the proper cardtypelock for the accepted cards.
   *
   * @return string
   *   The cards accepted in string format.
   */
  public function getPaymentMethods();

  /**
   * Calculate the md5checksum for the API request.
   *
   * @param array $data
   *   The data to send in the API request to Quickpay.
   *
   * @return string
   *   The checksum.
   */
  public function getApiChecksum(array $data);

  /**
   * Calculate the md5checksum for the payment window form
   *
   * @param array $data
   *   The data to POST to Quickpay.
   *
   * @return string
   *   The checksum.
   */
  public function getPaymentWindowChecksum(array $data);

  /**
   * Build the checksum from the request callback from quickpay.
   *
   * @param string $request
   *   The request in JSON format.
   *
   * @return string
   *   The checksum.
   */
  public function getChecksumFromRequest($request);

  /**
   * Request a QuickPay service.
   *
   * @param string $url
   *   The URL for the service.
   *
   * @return string
   *   The response in JSON.
   *
   * @throws \Drupal\quickpay\QuickpayException
   *   If the service could not be requested.
   */
  public function request($url);

  /**
   * Initialize a Quickpay instance from a request.
   *
   * @param object $request
   *   Request content in an object.
   *
   * @return mixed
   *   Instance of Quickpay on success, FALSE otherwise.
   */
  public static function loadFromRequest($request);

}
