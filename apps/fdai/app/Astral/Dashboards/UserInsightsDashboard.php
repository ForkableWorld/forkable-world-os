<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Dashboards;
use App\Models\User;
use App\Astral\Metrics\AnalysisProgressPartition;
use App\Astral\Metrics\NewUsersPerDayTrend;
use App\Astral\Metrics\NewUsersValue;
use App\Astral\Metrics\TotalUsersSharingDataValue;
use App\Dashboard;
class UserInsightsDashboard extends Dashboard {
	/**
	 * Get the cards for the dashboard.
	 * @return array
	 */
	public function cards(): array{
		return [
			new TotalUsersSharingDataValue(User::class),
			new NewUsersPerDayTrend(User::class),
			new NewUsersValue(User::class),
			new AnalysisProgressPartition(User::class),
		];
	}
	/**
	 * Get the URI key for the dashboard.
	 * @return string
	 */
	public static function uriKey(): string{
		return 'user-insights';
	}
	/**
	 * Get the displayable name of the dashboard.
	 * @return string
	 */
	public static function label(): string{
		return 'User Insights';
	}
}
