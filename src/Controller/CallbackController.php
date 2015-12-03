<?php
/**
 * @file
 * Contains \Drupal\quickpay\Controller\CallbackController.
 */

namespace Drupal\quickpay\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\quickpay\Entity\Quickpay;
use Drupal\quickpay\QuickpayTransaction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * Endpoints for the routes defined.
 */
class CallbackController extends ControllerBase {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Callback from Quickpay.
   *
   * @param $order_id
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function callback($order_id, Request $request) {
    $response = new Response();
    $response->setStatusCode(500);
    try {
      $content = json_decode($request->getContent());
      $quickpay = Quickpay::loadFromRequest($content);
      if ($quickpay) {
        $transaction = new QuickpayTransaction($quickpay, $content);
        // Invoke hook_quickpay_callback.
        $this->moduleHandler->invokeAll('quickpay_callback', array($order_id, $transaction));
        $response->setStatusCode(200);
      }
    }
    catch (Exception $e) {
      \Drupal::logger('quickpay')->error('Could not create transaction from request: !request.', array('!request' => print_r($request, TRUE)));
    }
    return $response;
  }

}
