<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Docker;
class Docker {
	/**
	 * Checks if given output array contains given string
	 * @param $output
	 * @param $string
	 * @return bool
	 */
	private function outputContains($output, $string){
		foreach($output as $line){
			if(strpos($line, $string) !== false){
				return true;
			}
		}
		return false;
	}
	/**
	 * Executes docker command with given args
	 * @param array|string $args
	 * @return array
	 */
	public function executeCommand($args = []){
		// we needs the "args" argument to be an array
		if(!is_array($args)){
			$args = [$args];
		}
		$outputBuffer = [];
		exec('docker ' . implode(' ', $args), $outputBuffer);
		return $outputBuffer;
	}
	/**
	 * Returns list of available docker container
	 * @param bool $all
	 * @return Container[]
	 */
	public function ps($all = false){
		$fieldMap = [
			'id' => '{{.ID}}',
			'image' => '{{.Image}}',
			'command' => '{{.Command}}',
			'createdAt' => '{{.CreatedAt}}',
			'runningFor' => '{{.RunningFor}}',
			'ports' => '{{.Ports}}',
			'status' => '{{.Status}}',
			'size' => '{{.Size}}',
			'names' => '{{.Names}}',
			'labels' => '{{.Labels}}',
			'mounts' => '{{.Mounts}}',
			'networks' => '{{.Networks}}',
		];
		// run docker ps to receive list of docker container.
		// we do this
		$cellGlue = '##---##';
		$format = "table " . implode($cellGlue, $fieldMap);
		$containerRaw = $this->executeCommand([
			'ps',
			($all ? ' --all' : ''),
			'--format "' . $format . '"',
		]);
		// first line ouf output is just table header
		$containerList = [];
		if(count($containerRaw) > 1){
			$containerFields = array_keys($fieldMap);
			for($index = 1; $index < count($containerRaw); $index++){
				$line = $containerRaw[$index];
				$parsedOutputLine = explode($cellGlue, $line);
				// fill container object using analysed output line
				$container = new Container();
				foreach($parsedOutputLine as $key => $value){
					$container->{$containerFields[$key]} = $value;
				}
				// we return this generated container object later
				$containerList[] = $container;
			}
		}
		return $containerList;
	}
	/**
	 * Checks if there is a container running under the given name
	 * @param $name
	 * @return bool
	 */
	public function isContainerRunning($name){
		foreach($this->ps() as $container){
			if(in_array($name, $container->getNames())){
				return true;
			}
		}
		return false;
	}
	/**
	 * Checks if there is a container existing under the given name
	 * @param $name
	 * @return bool
	 */
	public function isContainerExisting($name){
		foreach($this->ps(true) as $container){
			if(in_array($name, $container->getNames())){
				return true;
			}
		}
		return false;
	}
	/**
	 * Returns container object for given container name
	 * @param $name
	 * @return null|Container
	 */
	public function getContainerInfo($name){
		foreach($this->ps(true) as $container){
			if(in_array($name, $container->getNames())){
				return $container;
			}
		}
		return null;
	}
	/**
	 * Starts container with given name. Return if container could be started or not.
	 * Will return true if container is already running.
	 * @param $name
	 * @return bool
	 */
	public function start($name){
		if(!$this->isContainerRunning($name)){
			$output = $this->executeCommand([
				'start',
				$name,
			]);
			return $output[0] === $name;
		}
		return true;
	}
	/**
	 * Stops container with given name. Returns if container could be stopped or not.
	 * Will return true if container is not running.
	 * @param $name
	 * @return bool
	 */
	public function stop($name){
		if($this->isContainerRunning($name)){
			$output = $this->executeCommand([
				'stop',
				$name,
			]);
			return $output[0] === $name;
		}
		return true;
	}
	/**
	 * Kills existing docker container. Returns if container could be killed.
	 * Will return true if docker is not running.
	 * @param $name
	 * @return bool
	 */
	public function kill($name){
		if($this->isContainerRunning($name)){
			$this->executeCommand([
				'kill',
				$name,
			]);
			return $this->isContainerRunning($name);
		}
		return true;
	}
	/**
	 * Removes container with given name. Returns if container could be removed.
	 * Will return true if container is not existing.
	 * @param $name
	 * @param bool $killIfRunning
	 * @return bool
	 */
	public function remove($name, $killIfRunning = true){
		if($this->isContainerExisting($name)){
			// running container can not be removed
			if($this->isContainerRunning($name) && !$killIfRunning){
				return false;
			}
			$this->kill($name);
			$this->executeCommand([
				'rm',
				$name,
			]);
			return !$this->isContainerExisting($name);
		}
		return true;
	}
	/**
	 * Runs container under given name with given arguments. Returns if container could be run.
	 * Returns false if container is already running.
	 * @param $name
	 * @param array $args
	 * @return bool
	 */
	public function run($name, $args = []){
		if(!$this->isContainerExisting($name)){
			$this->executeCommand(array_merge([
					'run',
					'--name ' . $name,
				], $args));
			return $this->isContainerRunning($name);
		}
		return false;
	}
	/**
	 * Checks if docker is installed correctly or not
	 * (needs to be available over PATH)
	 * @return bool
	 */
	public function isInstalled(){
		return $this->outputContains($this->executeCommand('--version'), 'Docker version');
	}
}
