<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Dashboards;
use App\Repos\MoneyModoRepo;
use App\Dashboard;
class TqqqDashboard extends Dashboard {
	/**
	 * Get the cards for the dashboard.
	 * @return array
	 */
	public function cards(): array{
		$uv = MoneyModoRepo::tqqqUserVariable();
		return [
			$uv->getTrendMetric(),
		];
	}
	/**
	 * Get the URI key for the dashboard.
	 * @return string
	 */
	public static function uriKey(): string{
		return 'tqqq-dashboard';
	}
	public static function label(): string{
		return "TQQQ";
	}
}
