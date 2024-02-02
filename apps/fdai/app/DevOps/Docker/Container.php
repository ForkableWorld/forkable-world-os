<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Docker;
class Container {
	public $id;
	public $image;
	public $command;
	public $createdAt;
	public $runningFor;
	public $ports;
	public $status;
	public $size;
	public $names;
	public $labels;
	public $mounts;
	public $networks;
	/**
	 * @return mixed
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @return mixed
	 */
	public function getImage(){
		return $this->image;
	}
	/**
	 * @return mixed
	 */
	public function getCommand(){
		return $this->command;
	}
	/**
	 * @return mixed
	 */
	public function getCreatedAt(){
		return $this->createdAt;
	}
	/**
	 * @return mixed
	 */
	public function getRunningFor(){
		return $this->runningFor;
	}
	/**
	 * @return string[]
	 */
	public function getPorts(){
		return explode(',', $this->ports);
	}
	/**
	 * @return mixed
	 */
	public function getStatus(){
		return $this->status;
	}
	/**
	 * @return mixed
	 */
	public function getSize(){
		return $this->size;
	}
	/**
	 * @return string[]
	 */
	public function getLabels(){
		return explode(',', $this->labels);
	}
	/**
	 * @return string[]
	 */
	public function getMounts(){
		return explode(',', $this->mounts);
	}
	/**
	 * @return string[]
	 */
	public function getNetworks(){
		return explode(',', $this->networks);
	}
	/**
	 * @return string[]
	 */
	public function getNames(){
		return explode(',', $this->names);
	}
}
