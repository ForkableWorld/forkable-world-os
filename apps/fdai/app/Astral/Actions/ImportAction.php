<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\Connection;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class ImportAction extends QMAction implements ShouldQueue {
	use InteractsWithQueue, Queueable;
	public $withoutConfirmation = true;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request = null){
		// https://github.com/laravel/astral-issues/issues/736
		$this->connection = config('queue.default');
		$this->confirmButtonText = "Import, damnit!";
		$this->confirmText = "It can take a while to import your data so I'll send you an email when I'm finished.";
		$this->name = "Import Data";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection|Connection[] $connections
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $connections){
		foreach($connections as $connection){
			try {
				$connection->import();
				$this->markAsFinished($connection);
			} catch (\Throwable $e) {
				$this->markAsFailed($connection, $e);
			}
		}
		return $this->respond('Imported ' . count($connections) . " connections!");
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
