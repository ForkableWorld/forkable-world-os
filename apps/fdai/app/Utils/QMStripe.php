<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Logging\QMLog;
use App\Types\TimeHelper;
use Exception;
use Stripe\Customer;
use Stripe\Stripe;
/** Class QMStripe
 * @package App\Slim\Model
 */
class QMStripe {
	private $testCustomers;
	private $allCustomers;
	public function __construct(){
		Stripe::setApiKey(\App\Utils\Env::get('STRIPE_API_SECRET'));
	}
	/**
	 * @return Customer[]
	 */
	private function setAllCustomers(){
		$this->allCustomers = [];
		$last_customer = null;
		while(true){
			$currentCustomersCollection = Customer::all([
				"limit" => 100,
				"starting_after" => $last_customer,
			]);
			$customers = $currentCustomersCollection->data;
			$this->allCustomers = array_merge($this->allCustomers, $customers);
			if(!count($customers)){
				break;
			}
			if(!$currentCustomersCollection->has_more){
				break;
			}
			$last_customer = end($currentCustomersCollection->data);
			QMLog::info("Got " . count($this->allCustomers) . " customers so far. Last customer was created " .
				TimeHelper::daysAgo($last_customer->created) . " days ago");
			//break;
		}
		QMLog::info("Got " . count($this->allCustomers) . " TOTAL customers");
		return $this->allCustomers;
	}
	/**
	 * @return Customer[]
	 */
	private function getAllCustomers(){
		return $this->allCustomers ?: $this->setAllCustomers();
	}
	/**
	 * @return Customer[]
	 */
	private function setTestCustomers(){
		$last_customer = null;
		$this->testCustomers = [];
		foreach($this->getAllCustomers() as $customer){
			if(self::isTestEmail($customer->email)){
				$this->testCustomers[] = $customer;
			}
		}
		QMLog::info("Got " . count($this->testCustomers) . " TEST customers");
		return $this->testCustomers;
	}
	/**
	 * @return Customer[]
	 */
	public function getTestCustomers(){
		return $this->testCustomers ?: $this->setTestCustomers();
	}
	public function deleteTestCustomers(){
		$i = 0;
		$total = count($this->getTestCustomers());
		foreach($this->getTestCustomers() as $testCustomer){
			if(self::isTestEmail($testCustomer->email)){
				QMLog::info("Deleting customer " . $testCustomer->email . " ($i of $total)");
				try {
					$testCustomer->delete();
				} catch (Exception $e) {
					QMLog::error($e->getMessage(), ['exception' => $e]);
				}
			} else{
				QMLog::error("We shouldn't be here!");
			}
			$i++;
		}
	}
	/**
	 * @param string $email
	 * @return bool
	 */
	private static function isTestEmail($email){
		$testEmails = [
			'test@quantimo.do',
			'm@quantimo.do',
			'm@thinkbynumbers.org',
		];
		return in_array($email, $testEmails, true);
	}
}
