<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/26/2018
 * Time: 10:42 PM
 */

namespace Safaricom\Mpesa\Controller\Mpesa;

use \Magento\Framework\App\Action\Context;
use \Safaricom\Mpesa\Model\Mpesac2b;
use \Safaricom\Mpesa\Model\Mpesac2bFactory;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Safaricom\Mpesa\Model\Stkpush;

class ConfirmPayment extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Mpesac2b $mpesa,
        \Safaricom\Mpesa\Model\Stkpush $stkpush,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Safaricom\Mpesa\Helper\Data $mpesadata,
        Mpesac2bFactory $mpesaFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mpesa = $mpesa;
        $this->cart = $cart;
        $this->_stkpush = $stkpush;
        $this->_mpesadata = $mpesadata;
        $this->catalogSession = $catalogSession;
        $this->checkoutSession = $checkoutSession;
        $this->_mpesaFactory = $mpesaFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $amount = $this->cart->getQuote()->getGrandTotal();
        $ref = $this->cart->getQuote()->getId();

        $m_id = $this->getRequest()->getParam('m_id');
        $c_id = $this->getRequest()->getParam('c_id');

        //As we wait for the CallBack Response we Send a default value
        //$code    = null;
        //$success = false;
        //$message = 'Waiting for Transaction Response. Please Wait....';

        //Fetch the record from the stk table using the merchant_request_id

        $record  = $this->_stkpush->load($m_id, 'merchant_request_id');

        if (!empty($record->getResultDesc())) {

            switch ($record->getResultCode()) {

                case (0):

                    $code    = $record->getResultCode();
                    $message = 'Payment was completed successfull. Thank you';
                    $success = true;
                    //construct the response
                    $response = json_encode([
                        'success'        => $success,
                        'code' => $code,
                        'message'        => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);

                    break;

                case (1):
                    $code    = $record->getResultCode();
                    $message = 'Your Mpesa balance is not enough to Pay KES ' . $amount;
                    $success = false;
        
                    $response = json_encode([
                        'success'        => $success,
                        'code' => $code,
                        'message'        => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);
                    break;

                case (2001):
                    $code    = $record->getResultCode();
                    $message = 'You entered a wrong Mpesa PIN, please try place the order again';
                    $success = false;
                    
                    $response = json_encode([
                        'success'        => $success,
                        'code' => $code,
                        'message'    => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);
                    break;

                case (1037):
                    $code    = $record->getResultCode();
                    $message = 'Timeout Occured. Please Retry Again';
                    $success = false;
   
                    $response = json_encode([
                        'success'        => $success,
                        'code' => $code,
                        'message'    => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);

                    break;

                case (1032):
                    $code    = $record->getResultCode();
                    $message = 'You cancelled the payment on device, Please retry';
                    $success = false;
                
                    $response = json_encode([
                        'success'        => $success,
                        'code' => $code,
                        'message'    => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);
                    break;

                case ('SFC_IC0003'):
                    //MPESA operator doesnt exist..
                    $code    = $record->getResultCode();
                    $message = 'MPESA number does not exist, please check the provided number';
                    $success = false;

                    $response = json_encode([
                        'success'        => $success,
                        'code' => $code,
                        'message'    => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);
                    break;

                default:
                    $code    = $record->getResultCode();
                    $message = $record->getResultDesc();
                    $success = false;

                    $response = json_encode([
                        'success'        => $success,
                        'code'           => $code,
                        'message'        => $message,
                        'm_id'           => $m_id,
                        'c_id'           => $c_id,
                        'ref'            => $ref,
                        'amount'         => $amount
                    ]);
                    break;
            }
        } else {
            $response = json_encode(
                [
                    'success'   => false,
                    'code'     => null,
                    'message' => 'Please check your device and enter PIN, waiting...'
                ]
            );
        }

        

        $record->setStatus(1);
        $record->save();


        echo $response;
    }
}
