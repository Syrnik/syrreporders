<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 *
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 2.3.0
 *
 * 2.1.0 - Filter by orders states. Fixed bug with last day of period
 * 2.2.0 - Average Ticket price graph added
 * 2.3.0 - Filters moved to localStorage; data loaded via AJAX on page load
 */

/**
 * Report Action
 *
 * @package webasyst.shop.plugin.syrreporders.controller
 */
class shopSyrrepordersPluginBackendReportAction extends waViewAction
{

    /** @var array Graphs shown by default */
    private $default_graphs = array(
        'count'            => 1,
        'totals'           => 1,
        'shipping'         => 1,
        'discount'         => 1,
        'tax'              => 1,
        'avg_ticket_price' => 1
    );

    public function execute()
    {
        $timeframe = shopReportsSalesAction::getTimeframeParams();

        $currency = wa()->getConfig()->getCurrency();
        $workflow = shopWorkflow::getConfig();

        $this->view->assign('currency',           $currency);
        $this->view->assign('group_by',           $timeframe[2]);
        $this->view->assign('orders_graph',       $this->default_graphs);
        $this->view->assign('orderStates',        $workflow['states']);
        $this->view->assign('initial_timeframe',  $this->buildInitialTimeframe($timeframe));
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
