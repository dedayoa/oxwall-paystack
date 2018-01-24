<?php



/**
 * @author Adedayo Ayeni <dedayoa@gmail.com>
 * @package ow_plugins.billing_paystack.classes
 * @since 1.6.0
 */
class BILLINGPAYSTACK_CLASS_EventHandler
{
    /**
     * @var BILLINGPAYSTACK_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return BILLINGPAYPAL_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }
	
    
    public function addAdminNotification( BASE_CLASS_EventCollector $coll )
    {
        $billingService = BOL_BillingService::getInstance();
        
        $key = BILLINGPAYSTACK_CLASS_PaystackAdapter::GATEWAY_KEY;

        if ( !mb_strlen($billingService->getGatewayConfigValue($key, 'liveModeSK')))
        {
            $coll->add(
                OW::getLanguage()->text(
                    'billingpaystack',
                    'plugin_configuration_notice',
                    array('url' => OW::getRouter()->urlForRoute('billing_paystack_admin'))
                )
            );
        };
        if ( !mb_strlen($billingService->getGatewayConfigValue($key, 'testModeSK')) &&
        	 $billingService->getGatewayConfigValue($key, 'testMode'))
        {
        	$coll->add(
        			OW::getLanguage()->text(
        					'billingpaystack',
        					'plugin_configuration_notice',
        					array('url' => OW::getRouter()->urlForRoute('billing_paystack_admin'))
        					)
        			);
        }
    }

    public function addAccessException( BASE_CLASS_EventCollector $e )
    {
        $e->add(array('controller' => 'BILLINGPAYSTACK_CTRL_Order', 'action' => 'notify'));
    }

    public function init()
    {
        $em = OW::getEventManager();

        $em->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $em->bind('base.members_only_exceptions', array($this, 'addAccessException'));
        $em->bind('base.password_protected_exceptions', array($this, 'addAccessException'));
        $em->bind('base.splash_screen_exceptions', array($this, 'addAccessException'));
    }
}