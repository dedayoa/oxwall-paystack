<?php


/**
 * PayPal admin controller
 *
 * @author Adedayo Ayeni <dedayoa@gmail.com>
 * @package ow.ow_plugins.billing_paystack.controllers
 * @since 1.0
 */
class BILLINGPAYSTACK_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $billingService = BOL_BillingService::getInstance();
        $language = OW::getLanguage();

        $paystackConfigForm = new PaystackConfigForm();
        $this->addForm($paystackConfigForm);

        if ( OW::getRequest()->isPost() && $paystackConfigForm->isValid($_POST) )
        {
            $res = $paystackConfigForm->process();
            OW::getFeedback()->info($language->text('billingpaystack', 'settings_updated'));
            $this->redirect();
        }

        $adapter = new BILLINGPAYSTACK_CLASS_PaystackAdapter();
        $this->assign('logoUrl', $adapter->getLogoUrl());

        $gateway = $billingService->findGatewayByKey(BILLINGPAYSTACK_CLASS_PaystackAdapter::GATEWAY_KEY);
        $this->assign('gateway', $gateway);

        $this->assign('activeCurrency', $billingService->getActiveCurrency());

        $supported = $billingService->currencyIsSupported($gateway->currencies);
        $this->assign('currSupported', $supported);

        $this->setPageHeading(OW::getLanguage()->text('billingpaystack', 'config_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_app');
    }
}

class PaystackConfigForm extends Form
{

    public function __construct()
    {
        parent::__construct('paystack-config-form');

        $language = OW::getLanguage();
        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGPAYSTACK_CLASS_PaystackAdapter::GATEWAY_KEY;

        $livemode_sk = new TextField('liveModeSK');
        $livemode_sk->setValue($billingService->getGatewayConfigValue($gwKey, 'liveModeSK'));
        $this->addElement($livemode_sk);
		
        $testmode_sk = new TextField('testModeSK');
        $testmode_sk->setValue($billingService->getGatewayConfigValue($gwKey, 'testModeSK'));
        $this->addElement($testmode_sk);
        
        $testmode = new CheckboxField('testMode');
        $testmode->setValue($billingService->getGatewayConfigValue($gwKey, 'testMode'));
        $this->addElement($testmode);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('billingpaystack', 'btn_save'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();

        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGPAYSTACK_CLASS_PaystackAdapter::GATEWAY_KEY;

        $billingService->setGatewayConfigValue($gwKey, 'liveModeSK', $values['liveModeSK']);
        $billingService->setGatewayConfigValue($gwKey, 'testModeSK', $values['testModeSK']);
        $billingService->setGatewayConfigValue($gwKey, 'testMode', $values['testMode']);
    }
}