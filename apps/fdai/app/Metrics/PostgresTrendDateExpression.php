<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Metrics;

class PostgresTrendDateExpression extends TrendDateExpression
{
    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue()
    {
        $offset = $this->offset();

        if ($offset > 0) {
            $interval = '+ interval \''.$offset.' hour\'';
        } elseif ($offset === 0) {
            $interval = '';
        } else {
            $interval = '- interval \''.($offset * -1).' HOUR\'';
        }

        switch ($this->unit) {
            case 'month':
                return "to_char({$this->wrap($this->column)} {$interval}, 'YYYY-MM')";
            case 'week':
                return "to_char({$this->wrap($this->column)} {$interval}, 'IYYY-IW')";
            case 'day':
                return "to_char({$this->wrap($this->column)} {$interval}, 'YYYY-MM-DD')";
            case 'hour':
                return "to_char({$this->wrap($this->column)} {$interval}, 'YYYY-MM-DD HH24:00')";
            case 'minute':
                return "to_char({$this->wrap($this->column)} {$interval}, 'YYYY-MM-DD HH24:mi:00')";
        }
    }
}
