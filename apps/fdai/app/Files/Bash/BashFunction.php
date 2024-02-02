<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Bash;
class BashFunction {
	public $name;
	public $content;
	protected string $body;
	private BashScriptFile $file;
	/**
	 * @param                $name
	 * @param BashScriptFile $file
	 * @param string $body
	 */
	public function __construct($name, BashScriptFile $file, string $body){
		$this->name = $name;
		$this->file = $file;
		$this->body = $body;
	}
	/**
	 * @return mixed
	 */
	public function getNameAttribute(){
		return $this->name;
	}
	/**
	 * @param mixed $name
	 */
	public function setName($name): void{
		$this->name = $name;
	}
	/**
	 * @return BashScriptFile
	 */
	public function getFile(): BashScriptFile{
		return $this->file;
	}
	/**
	 * @return string
	 */
	public function getBody(): string{
		return $this->body;
	}
	/**
	 * @param string $body
	 */
	public function setBody(string $body): void{
		$this->body = $body;
	}
	public function getReference(): string{
		return 'function ' . $this->name . ' () { ' . $this->name . ' "$@" }';
	}
}
