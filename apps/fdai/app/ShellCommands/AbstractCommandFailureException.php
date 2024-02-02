<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands;
use App\Computers\JenkinsSlave;
use Exception;
use Symfony\Component\Process\Process;
use Throwable;
class AbstractCommandFailureException extends Exception {
	/**
	 * @var \Symfony\Component\Process\Process
	 */
	private Process $process;
	/**
	 * @param \App\Computers\JenkinsSlave $c
	 * @param null $e
	 */
	public function __construct(JenkinsSlave $c, $e = null){
		$process = $c->getLastCommandProcess();
		$this->process = $process;
		$code = $process->getExitCode();
		$message = "";
		if($input = $process->getInput()){$message .= "INPUT: $input\n\t";}
		$message .= "$c: EXIT CODE: $code\n\tERROR: ";
		if($e && is_string($e)){
			$message .= $e;
		} else if($e instanceof Throwable){
			$message .= trim(__METHOD__.": ".$e->getMessage());
		} else {
			$message .= $this->getOutput();
		}
		parent::__construct($message, 500);
	}
	/**
	 * @return \Symfony\Component\Process\Process
	 */
	public function getProcess(): Process{
		return $this->process;
	}
	/**
	 * @return string
	 */
	protected function getOutput(): string {
		$process = $this->getProcess();
		$out = $process->getErrorOutput();
		if(empty($out)){
			$out = $process->getOutput();
		} else{
			$out = DynamicCommand::stripManInMiddleWarning($out);
		}
		return trim($out);
	}
}
