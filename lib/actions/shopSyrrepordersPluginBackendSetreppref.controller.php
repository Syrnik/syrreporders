<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.0.0
 */

/**
 * Report Preferences save
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendSetrepprefController extends waJsonController
{
    /** @var waAppSettingsModel */
    protected $Setting;

    public function execute()
    {
        $this->Setting = new waAppSettingsModel();

        try {
            $report = waRequest::post('report');
            if(empty($report) || !in_array($report, array('orders', 'weekdays'))) {
                throw new waException("Report ID is invalid or absent");
            }

            $graph_settings = waRequest::post('orders_graph');
            $orders_states = waRequest::post('orders_state');
            $weekdays_state = waRequest::post('weekdays_state');

            if($graph_settings) {
                $this->Setting->set(array('shop', 'syrreporders'), 'orders_graph', serialize($graph_settings));
            }

            if($orders_states) {
                $this->Setting->set(array('shop', 'syrreporders'), 'orders_states', serialize($orders_states));
            }
            
            if($weekdays_state) {
                $this->Setting->set(array('shop', 'syrreporders'), 'weekdays_states', serialize($weekdays_state));
            }

        } catch (waException $ex) {
            $this->setError($ex->getMessage());
        }

        /** @todo Remove after pull request #58 (webasyst/webasyst-framework) merge */
        $this->getResponse()->addHeader('Content-type', 'application/json');
    }

    protected function preExecute()
    {
        $this->Setting = new waAppSettingsModel();
        parent::preExecute();
    }
}
