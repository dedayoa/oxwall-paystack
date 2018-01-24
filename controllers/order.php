<?php


/**
 * Paystack order pages controller
*
* @author Adedayo Ayeni <dedayoa@gmail.com>
* @package ow.ow_plugins.billing_paystack.controllers
* @since 1.0
*/

require dirname( dirname(__FILE__) ).'/classes/paystack-php/autoload.php';

class BILLINGPAYSTACK_CTRL_Order extends OW_ActionController
{

	public function form()
	{
			
		$billingService = BOL_BillingService::getInstance();
		$adapter = OW::getClassInstance("BILLINGPAYSTACK_CLASS_PaystackAdapter");
		$secret_key = $adapter->getSecretKey();
			
		if ($secret_key != null)
		{
			$paystack = new \Yabacon\Paystack($secret_key);
		}
		else{
			die('Secret Key cannot be null');
		}


		$userService = BOL_UserService::getInstance();
		$lang = OW::getLanguage();

		$sale = $billingService->getSessionSale();

		if ( !$sale )
		{
			$url = $billingService->getSessionBackUrl();
			if ( $url != null )
			{
				OW::getFeedback()->warning($lang->text('base', 'billing_order_canceled'));
				$billingService->unsetSessionBackUrl();
				$this->redirect($url);
			}
			else
			{
				$this->redirect($billingService->getOrderFailedPageUrl());
			}
		}

		$formId = uniqid('order_form-');
		$this->assign('formId', $formId);

		$js = '$("#' . $formId . '").submit()';
		OW::getDocument()->addOnloadScript($js);

		$fields = $adapter->getFields();

		if ( $billingService->prepareSale($adapter, $sale) )
		{
			$totalAmount = floatval($sale->totalAmount * 100); //convert to kobo
			$saleHash = $sale->hash;
			$userEmail = $userService->findUserById($sale->userId)->getEmail();
			$metadata = array(
					'itemName' => $sale->entityDescription,
					'itemID' => $sale->entityKey,
					'userID' => $sale->userId,
			);

			$response = $paystack->transaction->initialize([
					'reference' => $saleHash,
					'amount' => $totalAmount, // in kobo
					'email' => $userEmail,
					'callback_url' => $fields['notify_url'],
					'metadata' => json_encode($metadata),
			]);

			$url = $response->data->authorization_url;
			
			$masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('blank');
			OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);

			header('Location: '.$url);
				
			$billingService->unsetSessionSale();
				
		}
		else
		{
			$productAdapter = $billingService->getProductAdapter($sale->entityKey);


			if ( $productAdapter )
			{
				$productUrl = $productAdapter->getProductOrderUrl();
			}

			OW::getFeedback()->warning($lang->text('base', 'billing_order_init_failed'));
			$url = isset($productUrl) ? $productUrl : $billingService->getOrderFailedPageUrl();

			$this->redirect($url);
		}
	}

	public function notify()
	{
		$billingService = BOL_BillingService::getInstance();
		$logger = OW::getLogger('billingpaystack');
		$adapter = OW::getClassInstance("BILLINGPAYSTACK_CLASS_PaystackAdapter");
		
		$hash = !empty($_GET['trxref']) ? $_GET['trxref'] : $_GET['reference'];
		$sale = $billingService->getSaleByHash($hash);
		
		if (!$sale)
		{
			$logger->addEntry('Empty sale object', 'paystack.data-array');
			$logger->writeLog();
			exit;
		}		
		// check for transaction ref		
		if (!mb_strlen($hash))
		{
			$logger->addEntry('Empty Reference Code', 'paystack.data-array');
			$logger->writeLog();
			exit;
		}
		
		//run verify immediately after 
		$result = $adapter->runTransactionVerify($hash);
		$amount = $result->data->amount/100;
		
		$logger->addEntry(print_r($result, true), 'paystack.data-array');
		$logger->writeLog();
		
		if ($sale->totalAmount != $amount)
		{
			$logger->addEntry("Wrong amount: " . $amount , 'notify.amount-mismatch');
			$logger->writeLog();
			exit;
		}
		
		switch ($result->data->status)
		{
			case 'success':
				if ( $sale->status != BOL_BillingSaleDao::STATUS_DELIVERED )
				{
					$sale->transactionUid = $result->data->reference;
					
					if ( $billingService->verifySale($adapter, $sale))
					{
						$sale = $billingService->getSaleById($sale->id);
						$productAdapter = $billingService->getProductAdapter($sale->entityKey);
						
						if ( $productAdapter )
						{
							$billingService->deliverSale($productAdapter, $sale);
							$this->completed($hash);
						}
						else
						{
							$logger->addEntry('Empty product adapter object', 'paystack.data-array');
							$logger->writeLog();
						}
					}
					else
					{
						$logger->addEntry('Unverified sale', 'paystack.data-array');
						$logger->writeLog();
					}
				}
				else
				{
					$logger->addEntry('Sale not delivered', 'paystack.data-array');
					$logger->writeLog();
				}
				break;
		}
		
		
	}

	public function completed($hash=null)
	{
		//callback on payment completed
		//$hash = !empty($_REQUEST['trxref']) ? $_REQUEST['trxref'] : $_REQUEST['reference']; //@todo
		$this->redirect(BOL_BillingService::getInstance()->getOrderCompletedPageUrl($hash));
	}

	public function canceled()
	{
		//callback on payment cancelled
		$this->redirect(BOL_BillingService::getInstance()->getOrderCancelledPageUrl());
	}


}