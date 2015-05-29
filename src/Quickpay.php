<?php
/**
 * @file
 * Contains \Drupal\quickpay\Quickpay.
 *
 * The Quickpay class abstracts a specific setup.
 */

namespace Drupal\quickpay;

use Drupal\Core\Language\LanguageInterface;
use Drupal\quickpay\QuickpayTransaction;

class Quickpay {
  public $merchant;
  public $agreement;
  public $api;
  public $private;
  public $autofee = FALSE;
  public $debug = FALSE;
  public $order_prefix = '';
  protected $accepted_cards = array('creditcard');
  public $language = LanguageInterface::LANGCODE_NOT_SPECIFIED;
  protected static $currency_info = array();


  /**
   * Constructor.
   */
  public function __construct() {
    $config = \Drupal::configFactory()->getEditable('quickpay.settings');
    $this->merchant = $config->get('merchant');
    $this->agreement = $config->get('agreement');
    $this->api = $config->get('api');
    $this->private = $config->get('private');
    if ($config->get('autofee')) {
      $this->autofee = (bool) $config->get('autofee');
    }
    if ($config->get('debug')) {
      $this->debug = (bool) $config->get('debug');
    }
    if ($config->get('order_prefix')) {
      $this->order_prefix = $config->get('order_prefix');
    }
    // @todo validate this
    if ($config->get('accepted_cards')) {
      $this->accepted_cards = $config->get('accepted_cards');
    }
    if ($config->get('language')) {
      $this->language = $config->get('language');
    }
  }

  /**
   * Get the language of the user.
   * Default to english.
   *
   * @TODO: If LanguageInterface::LANGCODE_NOT_SPECIFIED the current users language should
   * be used and not 'en'.
   *
   * @return string.
   */
  public function getLanguage() {
    $language_code = LanguageInterface::LANGCODE_NOT_SPECIFIED ? 'en' : $this->language;
    $language_codes = array(
      'da' => 'da',
      'de' => 'de',
      'en' => 'en',
      'fo' => 'fo',
      'fr' => 'fr',
      'kl' => 'gl',
      'it' => 'it',
      'nb' => 'no',
      'nn' => 'no',
      'nl' => 'nl',
      'pl' => 'pl',
      'sv' => 'se',
    );
    return isset($language_codes[$language_code]) ? $language_codes[$language_code] : 'en';
  }

  /**
   * Get information about an currency.
   *
   * @param string $code
   *   The ISO 4217 currency code.
   *
   * @return NULL|array
   *   An array with the keys 'code' and 'multiplier', or null if not found.
   */
  public function currencyInfo($code) {
    if (!array_key_exists($code, Quickpay::$currency_info)) {
      // Use a basic set.
      $base_currencies = array(
        'DKK' => array('code' => 'DKK', 'multiplier' => 100),
        'USD' => array('code' => 'USD', 'multiplier' => 100),
        'EUR' => array('code' => 'EUR', 'multiplier' => 100),
        'GBP' => array('code' => 'GBP', 'multiplier' => 100),
        'SEK' => array('code' => 'SEK', 'multiplier' => 100),
        'NOK' => array('code' => 'NOK', 'multiplier' => 100),
        'ISK' => array('code' => 'ISK', 'multiplier' => 100),
      );

      Quickpay::$currency_info += $base_currencies;
      // If still not found, throw an exception.
      if (!array_key_exists($code, Quickpay::$currency_info)) {
        throw new QuickpayException(t('Unknown currency code %currency', array('%currency' => $code)));
      }
    }
    return Quickpay::$currency_info[$code];
  }

  /**
   * Returns the amount adjusted by the multiplier for the currency.
   *
   * @param decimal $amount
   *   The amount.
   * @param array|string $currency_info
   *   An currency_info() array, or a currency code.
   */
  public function wireAmount($amount, $currency_info) {
    if (!is_array($currency_info)) {
      $currency_info = $this->currencyInfo($currency_info);
    }
    return $amount * $currency_info['multiplier'];
  }

  /**
   * Reverses wireAmount().
   *
   * @param int $amount
   *   The amount.
   * @param array|string $currency_info
   *   An currency_info() array, or a currency code.
   */
  public function unwireAmount($amount, $currency_info) {
    if (!is_array($currency_info)) {
      $currency_info = $this->currencyInfo($currency_info);
    }
    return $amount / $currency_info['multiplier'];
  }

  /**
   * Return the proper cardtypelock for the accepted cards.
   */
  public function getPaymentMethods() {
    if (is_array($this->accepted_cards)) {
      $cards = $this->accepted_cards;
      // Aren't supported in cardtypelock.
      unset($cards['ikano']);
      return join(',', $cards);
    }
    // Already set to the proper string.
    return $this->accepted_cards;
  }

  /**
   * Calculate the md5checksum for the request.
   * Read more at http://tech.quickpay.net/payments/hosted/#checksum.
   *
   * @param array $data.
   *
   * @return string.
   */
  public function getChecksum($data) {
    ksort($data);
    $base = implode(" ", $data);
    return hash_hmac("sha256", $base, $this->api);
  }

  /**
   * Build the checksum from the request callback from quickpay.
   *
   * @param object $request.
   *
   * @return string.
   */
  public function getChecksumFromRequest($request) {
    return hash_hmac("sha256", $request, $this->private);
  }
}
