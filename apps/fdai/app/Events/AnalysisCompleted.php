<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Events;
use App\Models\BaseModel;
use App\Models\User;
use App\Properties\User\UserIdProperty;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
class AnalysisCompleted implements ShouldBroadcast {
	/**
	 * @var BaseModel
	 */
	public $model;
	/**
	 * Create a new event instance.
	 * @param BaseModel $model
	 */
	public function __construct(BaseModel $model){
		$this->model = $model;
	}
	/**
	 * Get the channels the event should broadcast on.
	 * @return PrivateChannel
	 */
	public function broadcastOn(): PrivateChannel{
		if($debug = true){
			return new PrivateChannel(User::generateBroadcastChannelName(UserIdProperty::USER_ID_MIKE));
		}
		return new PrivateChannel(User::generateBroadcastChannelName($this->model->getUserId()));
	}
	/**
	 * The event's broadcast name.
	 * @return string
	 */
	public function broadcastAs(): string{
		return \App\Events\AnalysisCompleted::class;
	}
	/**
	 * Get the data to broadcast.
	 * @return array
	 */
	public function broadcastWith(): array{
		return [
			'title' => $this->model->getTitleAttribute() . " Analysis Completed",
			'url' => $this->model->getUrl(),
			'icon' => $this->model->getFontAwesome(),
			'confirmButtonText' => "View Analysis",
		];
	}
}
