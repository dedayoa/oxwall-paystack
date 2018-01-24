<?php



/**
 * Paystack billing gateway adapter class.
 *
 * @author Adedayo Ayeni <dedayoa@gmail.com>
 * @package ow.ow_plugins.billing_paystack.classes
 * @since 1.0
 */

require 'paystack-php/autoload.php';

class BILLINGPAYSTACK_CLASS_PaystackAdapter implements OW_BillingAdapter
{
    const GATEWAY_KEY = 'billingpaystack';

    /**
     * @var BOL_BillingService
     */
    private $billingService;

    public function __construct()
    {
        $this->billingService = BOL_BillingService::getInstance();
    }

    public function prepareSale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    public function verifySale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }
    

    public function getSecretKey()
    {
    	if ($this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testMode') &&
    			mb_strlen($this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testModeSK')))
    	{
    		return $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testModeSK');
    	}
    	 
    	 
    	else if(!$this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testMode') &&
    			mb_strlen($this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'liveModeSK')))
    	{
    		return $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'liveModeSK');
    	}
    	 
    	else{
    		return null;
    	}
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getFields($params)
     */
    public function getFields( $params = null, $mobile = false )
    {
        $router = OW::getRouter();
        
        $arr = array(
        	'return_url' => $router->urlForRoute('billing_paystack_completed'),
        	'cancel_return_url' => $router->urlForRoute('billing_paystack_canceled'),
        	'notify_url' => OW::getRouter()->urlForRoute('billing_paystack_notify'),
        	'formActionUrl' => $this->getOrderFormActionUrl(),
        	'secret_key' => $this->getSecretKey()
        );
        	
        return $arr;
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getOrderFormUrl()
     */
    public function getOrderFormUrl()
    {
        return OW::getRouter()->urlForRoute('billing_paystack_order_form');
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getLogoUrl()
     */
    public function getLogoUrl($mobile = false)
    {
        $plugin = OW::getPluginManager()->getPlugin('billingpaystack');

        if($mobile)
        {
            return $plugin->getStaticUrl() . 'img/paystack.png';
        }
        else
        {
            return $plugin->getStaticUrl() . 'img/paystack.png';

        }

    }

    /**
     * 
     * @return string
     */
    private function getOrderFormActionUrl()
    {
        //$testMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testMode');

        //return $testMode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
        
    	// doesn't matter whether test or live
    	return 'https://api.paystack.co/transaction/initialize';
    }

    
    public function runTransactionVerify($reference)
    {
    	if ($this->getSecretKey() != null)
    	{
    		$paystack = new \Yabacon\Paystack($this->getSecretKey());
    	}
    	else{
    		die('Secret Key cannot be null');
    	}
    	
    	$responseObj = $paystack->transaction->verify(["reference"=>$reference]);
    	
    	return $responseObj;
    }
}