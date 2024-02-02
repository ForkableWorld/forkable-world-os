<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Logging\QMLog;
use App\Models\WpPost;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class MaterialPostsEmail extends Mailable {
	use Queueable, SerializesModels;
	public $posts;
	/**
	 * @var string
	 */
	public $previewText;
	/**
	 * @var string
	 */
	public $viewOnlineUrl;
	/**
	 * Create a new message instance.
	 * @param WpPost[]|Collection $posts
	 * @param string $previewText
	 * @param string $viewOnlineUrl
	 */
	public function __construct($posts, string $previewText, string $viewOnlineUrl = null){
		/** @var WpPost $one */
		$one = $posts->first();
		\App\Logging\ConsoleLog::info("Creating material post email with " . $posts->count() . " posts for " .
			$one->getUser()->display_name);
		$this->posts = $posts;
		$this->previewText = $previewText;
		$this->viewOnlineUrl = $viewOnlineUrl;
	}
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		return $this->view('email.material-email.material-email-container', ['posts' => $this->posts]);
	}
}
