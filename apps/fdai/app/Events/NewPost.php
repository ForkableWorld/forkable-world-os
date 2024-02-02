<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Events;
use App\Models\WpPost;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class NewPost implements ShouldBroadcast {
	use Dispatchable, InteractsWithSockets, SerializesModels;
	public $post;
	/**
	 * NewPost constructor.
	 * @param WpPost $post
	 */
	public function __construct($post){
		$this->post = $post;
	}
	/**
	 * Get the channels the event should broadcast on.
	 * @return Channel|array
	 */
	public function broadcastOn(){
		return new Channel('posts');
	}
	public function broadcastWith(){
		return [
			'title' => $this->post->post_title,
		];
	}
}
