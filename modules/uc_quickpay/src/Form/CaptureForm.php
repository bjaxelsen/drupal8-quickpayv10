<?php

/**
 * @file
 * Contains \Drupal\uc_quickpay\Form\CaptureForm.
 */

namespace Drupal\uc_quickpay\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use QuickPay\QuickPay as QuickpayClient ; // Quickpay PHP client
use Drupal\quickpay\Entity\Quickpay;

/**
 * Form builder for the capture form
 */
class CaptureForm extends FormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a CaptureForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_quickpay_capture_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form render array
   * @param \Drupal\Core\Form\FormStateInterface
   *   The form state object
   * @param int $order_id
   *   The order ID
   * @param int $payment_id
   *   The Quickpay payment ID (different from the order ID)
   * @param float $amount
   *   The amount to capture
   * @param string $quickpay_config
   *   The ID of the Quickpay configuration to use
   */
  public function buildForm(array $form, FormStateInterface $form_state, $order_id = NULL, $payment_id = NULL, $amount = NULL, $currency = NULL, $quickpay_config = NULL) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Capture'),
    ];
    $form['order_id'] = [
      '#type' => 'value',
      '#value' => $order_id
    ];
    $form['payment_id'] = [
      '#type' => 'value',
      '#value' => $payment_id
    ];
    $form['currency'] = [
      '#type' => 'value',
      '#value' => $currency
    ];
    $form['amount'] = [
      '#type' => 'value',
      '#value' => $amount
    ];
    $form['quickpay_config'] = [
      '#type' => 'value',
      '#value' => $quickpay_config
    ];
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
    $quickpay_instance = Quickpay::load($form['quickpay_config']['#value']);
    try {
      $client = new QuickpayClient(":{$quickpay_instance->api_key}");
      // Amount needs to manipulated:
      $currency_info = $quickpay_instance->currencyInfo($form['currency']['#value']);
      // Create payment
      $post_quickpay_form = array(
        'amount' => $quickpay_instance->wireAmount($form['amount']['#value'], $currency_info)
      );
      $capture = $client->request->post('/payments/' . $form['payment_id']['#value'] . '/capture', $post_quickpay_form);
      $status = $capture->http_status();

      if ($status == 202) {
        // Successful request
        // Log the capture
        \Drupal::database()
          ->insert('uc_quickpay_capture')
          ->fields([
            'payment_id' => $form['payment_id']['#value'],
            'order_id' => $form['order_id']['#value'],
            'capture_timestamp' => time(),
            'amount' => $form['amount']['#value'],
          ])
          ->execute();
        drupal_set_message(t('Capture succesful.'));
        uc_order_comment_save(
          $form['order_id']['#value'],
          \Drupal::currentUser()->id(),
          t('Order @id captured.', [
            '@id' => $form['order_id']['#value'],
          ])
        );
      }
      else {
        $response_body = var_export($capture->asArray(), TRUE);
        drupal_set_message(t('Capture unsuccesful (code: @code).', ['@code' => $status]), 'error');
        \Drupal::logger('uc_quickpay')->error(
          'Error capturing order @order, status code: @status, response body: @body',
          ['@order' => $form['order_id']['#value'], '@status' => $status, '@body' => $response_body]
        );
      }
    } catch (Exception $e) {
      drupal_set_message(t('Error communicating with Quickpay: @message', ['@message' => $e->getMessage()]));
      \Drupal::logger('uc_quickpay')->error(
        'Error communicating with Quickpay for capture of order @order: @message',
        ['@order' => $form['order_id']['#value'], '@message' => $e->getMessage()]
      );
    }

  }
}
