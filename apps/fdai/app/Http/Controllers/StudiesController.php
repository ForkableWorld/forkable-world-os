<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Buttons\Auth\AuthButton;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Study;
use App\Models\User;
use App\Properties\Study\StudyIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Studies\QMStudy;
use App\Utils\UrlHelper;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\Factory as ViewFactory;
use Throwable;
class StudiesController extends Controller {
	/**
	 * @return \Illuminate\Contracts\Foundation\Application|Factory|RedirectResponse|Response|Redirector|\Illuminate\View\View|\Response
	 */
	public function index(\Illuminate\Http\Request $request) {
		if($id = StudyIdProperty::fromRequest()){
			return $this->show($id);
		}
		$studies = Study::index($request);
		return view('studies-index', ['studies' => $studies]);
	}
	/**
	 * @param string|null $query
	 * @return \Illuminate\Contracts\Foundation\Application|Factory|RedirectResponse|Response|Redirector|\Illuminate\View\View|\Response
	 * @throws UnauthorizedException
	 */
	public function show(string $query = null){
		if(!$query){
			$query = StudyIdProperty::fromRequest();
		}
		if(!$query){
			throw new BadRequestException("Please provide a study id or go to the studies index page at "
			                              .UrlHelper::getAppUrl()."/studies");
		}
		try {
			$s = QMStudy::find($query);
		} catch (\App\Exceptions\UnauthorizedException $e) {
			QMLog::error("Could not show $query study because of an authorization error: ".$e->getMessage());
			if(QMAuth::getUser()){
				throw new UnauthorizedException("You are not authorized to view this study.  Ask the study owner to add you as a collaborator.");
			}
			return AuthButton::getRedirect();
		}
		try {
			$s->getOrSetCharts();
		} catch (NotEnoughDataException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
		} catch (Throwable $e) {
			le($e);
		}
		return $s->getShowView();
	}
	/**
	 * @param ViewFactory $view
	 * @param $clientId
	 * @return View|RedirectResponse
	 */
	public function getLandingPage(ViewFactory $view, $clientId){
		$studyApplication = Application::whereClientId($clientId)->where('study', 1)->first();
		if(empty($studyApplication)){
			return Redirect::route('account')->with('error', "Study doesn't exist.");
		}
		return $view->make('web.study.landing', [
			'study' => $studyApplication,
			'meta' => [
				'title' => $studyApplication->app_display_name,
				'app_description' => $studyApplication->app_description,
				'image' => $studyApplication->getImage(),
			],
		]);
	}
	public function demo(){
		$s = User::demo()->studies()->first();
		return $s->getShowView();
	}
}
