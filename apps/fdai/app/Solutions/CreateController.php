<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerStub;
class CreateController extends CreateClass {
	public function getSolutionActionDescription(): string{
		return "Generate a new exception class for you to rename and add a solution. Then catch and replace the original exception.";
	}
	public function getSolutionDescription(): string{
		return "Please implement this controller";
	}
	public function getBaseClassName(): string{ return Controller::class; }
	public function getStubClassName(): string{ return ControllerStub::class; }
}
