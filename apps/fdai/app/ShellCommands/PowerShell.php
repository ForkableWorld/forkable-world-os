<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\ShellCommands;

require_once "vendor/autoload.php";

use mikehaertl\shellcommand\Command;

class PowerShell
{
  // PowerShell Executable
  private $_psExec = null;

  // PowerShell Module Dir
  private $_psModuleDir = null;

  // Get SERVER Variable
  public static function ENV($key)
  {
    $serverVariables = $_SERVER;
    foreach ($serverVariables as $k => $v) $serverVariables[strtoupper($k)] = $v;

    if (!$key || !is_string($key) || !isset($serverVariables[strtoupper($key)])) return null;
    return $serverVariables[strtoupper($key)];
  }

  // Public Construct
  public function __construct($psPath = null)
  {
    // Set Ps Executable Path
    if ($psPath)
      $this->_psExec = $psPath;
    else
      $this->_psExec = 'powershell.exe';
    // Set Ps Module Dir
    $this->_psModuleDir = self::ENV('PSMODULEPATH');

    // Validation
    // if (!file_exists($this->_psExec)) throw new Exception('PowerShell executable not found');
  }

  // Parse argument array. 'key => value' or 'numeric => value'
  private function parseArguments($args = []): string{
    $parsedArguments = [];
    if (!is_array($args)) $args = [];
    foreach ($args as $key => $value)
    {
      if (!is_numeric($key))
        $parsedArguments[] = '-' . $key . ' ' . $value;
      else
        $parsedArguments[] = $value;
    }
    return implode(' ', $parsedArguments);
  }

  // Execute Command
  public function execute($command, $args = [])
  {
    if (!is_string($command) || (!is_array($args) && !is_string($args))) return null;
    if (is_array($args) && $args = $this->parseArguments($args)) if (!empty($args)) $command = $command . ' ' . $args;

    $c = new Command();
    if(strpos($command, "-") === 0){$c->escapeArgs = false;}
    $c->setCommand($this->_psExec);
    $c->addArg($command);
    if ($c->execute())
      $stdOut = $c->getOutput();
    else
      throw new Exception($c->getError(), $c->getExitCode());

    if (is_string($stdOut) && trim($stdOut) == "True") $stdOut = true;
    if (is_string($stdOut) && trim($stdOut) == "False") $stdOut = false;

    $jsonArr = json_decode($stdOut, true);
    if (json_last_error() == JSON_ERROR_NONE) $stdOut = $jsonArr;

    return ['output' => $stdOut, 'Executed Command' => $c->getExecCommand()];
  }
}
