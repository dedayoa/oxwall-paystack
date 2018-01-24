<?php

/**
 * Copyright (c) 2016, Tekart Ltd
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$billingService = BOL_BillingService::getInstance();

$billingService->deleteConfig('billingpaystack', 'liveModeSK');
$billingService->deleteConfig('billingpaystack', 'testModeSK');
$billingService->deleteConfig('billingpaystack', 'testMode');

$billingService->deleteGateway('billingpaystack');