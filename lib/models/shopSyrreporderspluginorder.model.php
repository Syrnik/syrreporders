<?php
/**
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * 
 * @license http://www.webasyst.com/terms/#eula Webasyst Commercial
 * @version 1.0.0
 */

/**
 * shopOrder model extension
 *
 * @package webasyst.shop.plugin.syrreporders.model
 */
class shopSyrreporderspluginorderModel extends shopOrderModel
{
    /**
     * Почти полная копия getSales(), с тем отличием, что запрос по дате создания,
     * а не по дате оплаты
     * 
     * @param array $conditions
     * @return array
     */
    public function getOrderStats(array $conditions=array())
    {
        $defaults = array('start_date'=>NULL, 'end_date'=>NULL, 'group'=>'days');
        
        $conditions = array_merge($defaults, $conditions);
        
        $date_col = ($conditions['group'] == 'months') ? "DATE_FORMAT(o.create_datetime, '%Y-%m-01')" : 'DATE_FORMAT(o.create_datetime, "%Y-%m-%d")';
        $create_date_sql = self::getDateSql('o.create_datetime', $conditions['start_date'], $conditions['end_date']);
        
        $sql = "SELECT
                    {$date_col} AS `date`,
                    SUM(o.total*o.rate) AS total,
                    SUM(o.shipping*o.rate) AS shipping,
                    SUM(o.discount*o.rate) AS discount,
                    SUM(o.tax*o.rate) AS tax,
                    COUNT(*) AS `count`
                FROM {$this->table} o
                WHERE {$create_date_sql}
                GROUP BY {$date_col}";

        // All rows from DB
        $min_date = null;
        $result = array(); // YYYY-MM-DD => array(...)
        foreach($this->query($sql) as $row) {
            if (!$min_date || strcmp($min_date, $row['date']) > 0) {
                $min_date = $row['date'];
            }
            $result[$row['date']] = $row;
        }

        // Add empty rows
        if ($conditions['start_date']) {
            $start_ts = strtotime($conditions['start_date']);
        } else if ($min_date) {
            $start_ts = strtotime(ifempty($min_date, date('Y-m-d'))) - 48*3600;
        } else {
            $start_ts = strtotime(date('Y-m-01', strtotime("-1 months")));
        }

        $end_ts = strtotime(ifempty($conditions['end_date'], date('Y-m-d')));
        
        for ($t = $start_ts; $t <= $end_ts; $t += 3600*24) {
            $date = date(($conditions['group'] == 'months') ? 'Y-m-01' : 'Y-m-d', $t);
            if (empty($result[$date])) {
                $result[$date] = array(
                    'date' => $date,
                    'total' => 0,
                    'count' => 0,
                    'shipping' => 0,
                    'discount' => 0,
                    'tax' => 0
                );
            }
            $result[$date]['total'] = (float) $result[$date]['total'];
        }
        ksort($result);

        return $result;
    }
    
    public function getOrderStatsByDow(array $conditions = array())
    {
        $defaults = array('start_date'=>NULL, 'end_date'=>NULL, 'group'=>'days');        
        $conditions = array_merge($defaults, $conditions);
        $create_date_sql = self::getDateSql('o.create_datetime', $conditions['start_date'], $conditions['end_date']);
        
        $sql = "SELECT WEEKDAY(o.create_datetime) AS dow, AVG(o.total*o.rate) AS total, COUNT(*) AS `count` "
                . "FROM {$this->table} o "
                . "WHERE $create_date_sql "
                . "GROUP BY WEEKDAY(o.create_datetime) "
                . "ORDER BY dow";
        
        $result = $this->query($sql)->fetchAll('dow');
        
        for($i=0;$i<7;$i++) {
            if(!isset($result[$i])) {
                $result[$i] = array('dow'=>"$i", 'total'=>"0", 'count'=>"0");
            }
        }
        
        ksort($result);
        
        return $result;
    }
    
    private function countDays($day, $start, $end)
    {        
        
        //get the day of the week for start and end dates (0-6)
        $w = array(date('w', $start), date('w', $end));

        //get partial week day count
        if ($w[0] < $w[1])
        {            
            $partialWeekCount = ($day >= $w[0] && $day <= $w[1]);
        }else if ($w[0] == $w[1])
        {
            $partialWeekCount = $w[0] == $day;
        }else
        {
            $partialWeekCount = ($day >= $w[0] || $day <= $w[1]);
        }

        //first count the number of complete weeks, then add 1 if $day falls in a partial week.
        return floor( ( $end-$start )/60/60/24/7) + $partialWeekCount;
    }

}
