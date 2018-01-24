<?php

/**
 * Copyright (c) 2016, Tekart Ltd.
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$billingService = BOL_BillingService::getInstance();

$gateway = new BOL_BillingGateway();
$gateway->gatewayKey = 'billingpaystack';
$gateway->adapterClassName = 'BILLINGPAYSTACK_CLASS_PaystackAdapter';
$gateway->active = 0;
$gateway->mobile = 1;
$gateway->recurring = 0;
$gateway->currencies = 'NGN';

$billingService->addGateway($gateway);


$billingService->addConfig('billingpaystack', 'liveModeSK', '');
$billingService->addConfig('billingpaystack', 'testModeSK', '');
$billingService->addConfig('billingpaystack', 'testMode', '0');


OW::getPluginManager()->addPluginSettingsRouteName('billingpaystack', 'billing_paystack_admin');

$path = OW::getPluginManager()->getPlugin('billingpaystack')->getRootDir() . 'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'billingpaystack');