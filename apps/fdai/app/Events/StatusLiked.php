<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class StatusLiked implements ShouldBroadcast {
	use Dispatchable, InteractsWithSockets, SerializesModels;
	public $username;
	public $message;
	/**
	 * Create a new event instance.
	 * @param $username
	 */
	public function __construct($username){
		$this->username = $username;
		$this->message = "{$username} liked your status";
	}
	/**
	 * Get the channels the event should broadcast on.
	 * @return Channel|array
	 */
	public function broadcastOn(){
		return ['status-liked'];
	}
}
