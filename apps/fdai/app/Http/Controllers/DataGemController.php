<?php
namespace App\Http\Controllers;
use App\Models\UserVariable;
class DataGemController extends NftController
{
	protected function getTokenizableClass(): string|null{
		return UserVariable::class;
	}
}
