<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 2.3.0
 *
 * 2.3.0 - Filters moved to localStorage; data loaded via AJAX on page load
 */

/**
 * Report Weekdays Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendReportweekdaysAction extends waViewAction
{
    public function execute()
    {
        $timeframe = shopReportsSalesAction::getTimeframeParams();

        $currency = wa()->getConfig()->getCurrency();
        $workflow = shopWorkflow::getConfig();

        $this->view->assign('currency',          $currency);
        $this->view->assign('all_order_states',  $workflow['states']);
        $this->view->assign('initial_timeframe', $this->buildInitialTimeframe($timeframe));
    }

    private function buildInitialTimeframe(array $timeframe): array
    {
        $req    = $timeframe[3];
        $tf_val = isset($req['timeframe']) ? $req['timeframe'] : null;
        if ($tf_val !== 'all' && $tf_val !== 'custom' && !ctype_digit((string)$tf_val)) {
            $tf_val = '30';
        }
        $result = ['timeframe' => (string)$tf_val, 'groupby' => $timeframe[2]];
        if ($tf_val === 'custom') {
            if (!empty($req['from'])) $result['from'] = date('Y-m-d', $req['from']);
            if (!empty($req['to']))   $result['to']   = date('Y-m-d', $req['to']);
        }
        return $result;
    }
}
