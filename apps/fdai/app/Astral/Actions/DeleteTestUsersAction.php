<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\User;
use App\Types\TimeHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Actions\DestructiveAction;
use App\Fields\ActionFields;
use App\Fields\Date;
class DeleteTestUsersAction extends DestructiveAction {
	use InteractsWithQueue, Queueable, SerializesModels;
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		DeleteTestUsersAction::deleteOldTestUsers($fields->created_before);
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [
			Date::make('Created Before'),
		];
	}
	public static function deleteOldTestUsers(string $before = null){
		if(!$before){
			$before = time() - 86400;
		}
		$carbon = TimeHelper::toCarbon($before);
		QMLog::infoWithoutContext('=== ' . __FUNCTION__ . ' ===');
		$rows = User::query()->where(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%testuser%")
			->orWhere(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%test-user%")->get();
		$toDelete = [];
		foreach($rows as $u){
			if($u->created_at > $carbon){
				$u->logInfo("Skipping because was created " . $u->created_at->diffForHumans());
				continue;
			}
			if($u->ID < 19313){
				$u->logInfo("Skipping because ID is small so it might be a permanent test user. 19312 is Fehim and we need to keep 18535 ");
				continue;
			}
			$toDelete[] = $u;
		}
		$count = count($toDelete);
		QMLog::infoWithoutContext("Deleting $count test users...");
		$soFar = 0;
		foreach($toDelete as $u){
			$soFar++;
			ConsoleLog::info("Deleting $soFar of $count test users");
			$u->hardDeleteWithRelations("is a test user");
		}
	}
}
