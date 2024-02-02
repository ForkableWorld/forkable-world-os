<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
class DeleteRemindersSolution extends BaseRunnableSolution {
	private int $userId;
	/**
	 * DeleteRemindersSolution constructor.
	 */
	public function __construct(int $userId){
		$this->userId = $userId;
	}
	public function getSolutionTitle(): string{
		return "Delete Reminders for User";
	}
	public function getSolutionDescription(): string{
		return "Delete Reminders for User";
	}
	public function run(array $parameters = []){
		$userId = $parameters[TrackingReminder::FIELD_USER_ID] ?? $this->userId;
		TrackingReminderNotification::whereUserId($userId)->forceDelete();
		TrackingReminder::whereUserId($userId)->forceDelete();
	}
	public function getSolutionActionDescription(): string{
		return "SolutionActionDescription: Delete Reminders for User";
	}
	public function getRunButtonText(): string{
		return "Delete Reminders for User";
	}
	public function getRunParameters(): array{
		return [TrackingReminder::FIELD_USER_ID => $this->getUserId()];
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	public function getDocumentationLinks(): array{
		return [
			"Reminders" => TrackingReminder::getDataLabIndexUrl([TrackingReminder::FIELD_USER_ID => $this->getUserId()]),
			"User" => $this->getUser()->getDataLabShowUrl(),
		];
	}
	public function getUser(): User{
		return User::withTrashed()->find($this->getUserId());
	}
}
