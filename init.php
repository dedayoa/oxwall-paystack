<?php

/**
 * Copyright (c) 2016, Tekart Ltd
 * All rights reserved.

 */

OW::getRouter()->addRoute(new OW_Route('billing_paystack_order_form', 'billing-paystack/order', 'BILLINGPAYSTACK_CTRL_Order', 'form'));
OW::getRouter()->addRoute(new OW_Route('billing_paystack_notify', 'billing-paystack/order/notify', 'BILLINGPAYSTACK_CTRL_Order', 'notify'));
OW::getRouter()->addRoute(new OW_Route('billing_paystack_completed', 'billing-paystack/order/completed/', 'BILLINGPAYSTACK_CTRL_Order', 'completed'));
OW::getRouter()->addRoute(new OW_Route('billing_paystack_canceled', 'billing-paystack/order/canceled/', 'BILLINGPAYSTACK_CTRL_Order', 'canceled'));
OW::getRouter()->addRoute(new OW_Route('billing_paystack_admin', 'admin/billing-paystack', 'BILLINGPAYSTACK_CTRL_Admin', 'index'));

BILLINGPAYSTACK_CLASS_EventHandler::getInstance()->init();