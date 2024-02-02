<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Http\Livewire;
use App\Models\Variable;
use Livewire\Component;
class SearchVariables extends Component {
	public $searchTerm;
	public $variables;
	public function render(){
		//if($this->readyToLoad){
		$this->variables = Variable::search($this->searchTerm ?? "");
		//}
		return view('livewire.search-variables');
	}
}
