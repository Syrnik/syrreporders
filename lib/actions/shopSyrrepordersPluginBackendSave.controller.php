<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 */

/**
 * Settings Save Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendSaveController extends waJsonController
{
    protected shopSyrrepordersPlugin $plugin;

    protected function preExecute()
    {
        parent::preExecute();

        $this->plugin = wa('shop')->getPlugin('syrreporders');
    }

    /**
     * @return void
     * @throws waException
     */
    public function execute(): void
    {
        $settings = waRequest::post("shop_syrreporders");

        $this->plugin->saveSettings($settings);

        $this->response['message'] = _wp("Saved");
    }
}
