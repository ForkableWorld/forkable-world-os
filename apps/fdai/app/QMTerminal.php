<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App;
use App\Logging\ConsoleLog;
use TitasGailius\Terminal\Builder;
use TitasGailius\Terminal\Response;
use TitasGailius\Terminal\Terminal;
/**
 * @mixin Builder
 * @staticMixin Builder
 */
class QMTerminal extends Terminal
{
	/**
	 * @param string $cmd
	 * @return Response
	 */
	public static function run(string $cmd): Response{
		$builder = Terminal::builder();
		$builder->in(abs_path());
		$response = $builder->run($cmd, ConsoleLog::logTerminalOutput());
		return $response;
	}
}
