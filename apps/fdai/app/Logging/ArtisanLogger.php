<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Storage\Memory;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Output\ConsoleOutput;
class ArtisanLogger {
	const COMMAND_FINISHED = "CommandFinished";
	private static CommandFinished $last;
	public static function logStarting(CommandStarting $event): void{
		if(self::shouldLogProminently($event)){
			QMLog::logStartOfProcess(self::getProcessName($event));
		}else{
			ConsoleLog::debug(self::getProcessName($event));
		}
	}
	public static function logFinished(CommandFinished $event): void{
		QMLog::titledDivider("$event->command Output");
		$output = $event->output;
		if(method_exists($output, "fetch")){
			$output = $output->fetch();
			ConsoleLog::info(trim("$event->command: $output"));
		} elseif($output instanceof ConsoleOutput) {
			//debugger();
			try {
				// TODO: figure out how to get output from ConsoleOutput
//				$stream = $output->getErrorOutput()->getStream();
//				rewind($stream);
//				$display = stream_get_contents($stream);
//				if ($normalize = true) {
//					$display = str_replace(PHP_EOL, "\n", $display);
//				}
//				$output = $display;
//				QMLog::info($output);
			} catch (\Throwable $e){
				$output = "TODO: figure out how to get output from ConsoleOutput \n\tException: ".$e->getMessage();
				ConsoleLog::error($output);
			}
		}
		Memory::add($event->command, $event,self::COMMAND_FINISHED);

		if(self::shouldLogProminently($event)){
			QMLog::logEndOfProcess(self::getProcessName($event));
		}
		self::$last = $event;
	}
	/**
	 * @param $event
	 * @return bool
	 */
	private static function shouldLogProminently($event): bool{
		$exclude = [
			"route:clear",
			"config:clear",
			"clear-compiled",
			"config:clear",
			//"clockwork:clean",
			"view:clear",
			"cache:clear"
		];
		//if(!AppMode::isUnitOrStagingUnitTest()){return true;}
		foreach($exclude as $needle){
			if(stripos(self::getProcessName($event), $needle) !== false){
				return false;
			}
		}
		return true;
	}
	/**
	 * @param $event
	 * @return string
	 */
	private static function getProcessName($event):string{
		$str = "artisan ".$event->input->__toString();
		//return $str;
		return str_replace("'", "", $str);
	}
	public static function getLastOutput(): string {
		$event = self::getLastCommandFinished();
		return $event->output->fetch();
	}
	public static function logLastOutput(): string {
		return ConsoleLog::info(self::getLastOutput());
	}
	public static function getLastCommandFinished(): CommandFinished {
		return self::$last;
	}
	/**
	 * @return array|null
	 */
	public static function getPreviousCommands(): ?array{
		$all = Memory::getAll(self::COMMAND_FINISHED);
		return $all;
	}
}
