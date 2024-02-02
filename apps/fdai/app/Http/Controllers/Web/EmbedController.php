<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMultipleClassDeclarationsInspection */
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\View\Factory as ViewFactory;
class EmbedController extends Controller {
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function commonRelationships(ViewFactory $view): View{
		return $view->make('web/embed/common-relationships');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function userRelationships(ViewFactory $view): View{
		return $view->make('web/embed/user-relationships');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function apiExplorer(ViewFactory $view): View{
		return $view->make('docs/api-explorer');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function variables(ViewFactory $view): View{
		return $view->make('web/embed/variables');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function connectors(ViewFactory $view): View{
		return $view->make('web/embed/connectors');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function reminders(ViewFactory $view): View{
		return $view->make('web/embed/reminders');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function manageReminders(ViewFactory $view): View{
		return $view->make('web/embed/manage-reminders');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function track(ViewFactory $view): View{
		return $view->make('web/embed/track-factors');
	}
	/**
	 * @param $category
	 * @param ViewFactory $view
	 * @return View
	 */
	public function trackCategory($category, ViewFactory $view): View{
		return $view->make('web/embed/track-factors', compact('category'));
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function history(ViewFactory $view): View{
		return $view->make('web/embed/history');
	}
	/**
	 * @param ViewFactory $view
	 * @return View
	 */
	public function historyMoods(ViewFactory $view): View{
		return $view->make('web/embed/history-moods');
	}
}
