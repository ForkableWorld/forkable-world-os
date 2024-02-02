<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\Models\Study;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class PublishStudy implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	/**
	 * @var Study
	 */
	protected $study;
	/**
	 * Create a new job instance.
	 * @param Study $study
	 */
	public function __construct(Study $study){
		$this->study = $study;
	}
	/**
	 * Execute the job.
	 * @return void
	 */
	public function handle(){
		$s = $this->study;
		$s->publish();
	}
	/**
	 * @return Study
	 */
	public function getStudy(): Study{
		return $this->study;
	}
}
