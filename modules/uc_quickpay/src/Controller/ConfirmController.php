<?php

namespace Drupal\uc_quickpay\Controller;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\uc_cart\CartManagerInterface;
use Drupal\uc_order\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for handling confirmation. We need an extra step here to 
 * handle the confirmation page. This is because Quickpay makes
 * an asynchroneous callback, hence we need to take care of that
 * Ubercart stores order status as session variable, and we need to change this
 * before user hits the completion page, otherwise the user will be
 * redirected to the cart view
 */
class ConfirmController extends ControllerBase {
  /**
   * The cart manager.
   *
   * @var \Drupal\uc_cart\CartManager
   */
  protected $cartManager;

  /**
   * Constructs a TwoCheckoutController.
   *
   * @param \Drupal\uc_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   */
  public function __construct(CartManagerInterface $cart_manager) {
    $this->cartManager = $cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uc_cart.manager')
    );
  }

  /**
   * Finalizes transaction.

   */
  public function complete() {
    $session = \Drupal::service('session');

    if (!$session->has('cart_order')) {
      drupal_set_message($this->t('Thank you for your order! A technical problem has occured. Please contact us.'));
      \Drupal::logger('uc_quickpay')->error('Missing cart order session variable for completion page.');
      return $this->redirect('uc_cart.cart');
    }

    if (!($order = Order::load($session->get('cart_order')))) {
      drupal_set_message($this->t('Thank you for your order! A technical problem has occured when displaying your order. Please contact us.'));
      \Drupal::logger('uc_quickpay')
        ->error('Missing cart order entity for order @order_id.', ['@id' => $session->get('cart_order')]);
      return $this->redirect('uc_cart.cart');
    }

    // It is a bit tricky that Quickpay works asynchroneously,
    // try up to 10 times / 1 second to wait for a change
    $counter = 0;
    while ($order->getStateId() == 'in_checkout') {
      $counter++;
      sleep(1);
      // Reload from DB, not from cache
      $order = \Drupal::entityTypeManager()->getStorage('uc_order')->loadUnchanged($session->get('cart_order'));
      if ($counter == 10 && ($order->getStateId() == 'in_checkout')) {
        \Drupal::logger('uc_quickpay')
          ->error('User got to completion page for order @order_id still in checkout.', ['@order_id' => $order->id()]);
        return ['#plain_text' => $this->t('An error has occurred during payment. Please try to reload this page or contact us to ensure your order has submitted.')];
      }
    }

    \Drupal::logger('uc_quickpay')->notice('Processing completion page for order @order_id.', ['@order_id' => $order->id()]);
    $session->set('uc_checkout_complete_' . $order->id(), TRUE);
    return $this->redirect('uc_cart.checkout_complete');
  }
  
  public function completeTitle() {
    return $this->t('Almost complete');
  }
}
