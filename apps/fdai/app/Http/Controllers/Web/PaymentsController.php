<?php namespace App\Http\Controllers\Web;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\BillingPlan;
use App\Models\User;
use Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Log;
use Redirect;
class PaymentsController extends Controller {
	/**
	 * @param Guard $auth
	 * @return RedirectResponse
	 */
	public function postSubscribe(Guard $auth){
		$post = $this->getRequest()->all();
		/** @var User $user */
		$user = Auth::user();
		/** @var Application $app */
		$app = Application::findInMemoryOrDB($post['app_id']);
		$plan = BillingPlan::findInMemoryOrDB($post['plan_id']);
		try {
			if($app->subscribed()){
				//check if user is subscribed already so we can swap between plans
				$status = $app->subscription()->swap($plan->name);
			} else{
				//user is subscribing for the first time
				$status = $app->newSubscription('main', $plan->name)->create($post['card_token'], [
						'email' => $user->user_email,
						'description' => "Application name: " . $app->app_display_name,
					]);
			}
		} catch (\Exception $e) {
			$status = false;
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			Log::error("Stripe subscription error: " . $e->getMessage());
		}
		if($status !== false){
			$this->updateApplication($app, $post);
			return Redirect::route('update/app', $app->id)->with('success', "Your subscription is successful");
		}
		return Redirect::route('update/app', $app->id)->with('error', "There was a problem with your request");
	}
	/**
	 * @param Application $app
	 * @param [] $post
	 */
	public function updateApplication(Application $app, $post){
		if($post['invoice_type'] == '#company'){
			$app->company_name = $post['company_name'];
			$app->address = $post['address'];
			$app->city = $post['city'];
			$app->state = $post['state'];
			$app->zip = $post['zip'];
			$app->country = $post['country'];
		} elseif($post['invoice_type'] == '#individual'){
			$app->country = $post['country'];
		}
		$app->plan_id = $post['plan_id'];
		$app->save();
	}
}/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */


