<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Feed;
use App\Cards\TrackingReminderNotificationCard;
use App\Exceptions\UnauthorizedException;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\User\QMUser;
use App\Storage\Memory;
use App\Types\QMArr;
class UserFeedResponse extends QMResponseBody {
	public $cards;
	public $measurementsSaved;
	/**
	 * @var QMUser
	 */
	private $user;
	/**
	 * UserFeedResponse constructor.
	 * @param User $user
	 * @param array|null $cards
	 */
	public function __construct(User $user, array $cards = null){
		$this->user = $user;
		$this->cards = $cards ?: $this->getCards();
		parent::__construct();
		$this->summary = "Your Stream";
		$this->description = "Your Tracking Reminder Notifications, Messages, and Discoveries";
		$this->measurementsSaved = Memory::getNewMeasurementsForUserByVariable($user->id);
	}
	/**
	 * @return TrackingReminderNotificationCard[]
	 * @throws UnauthorizedException
	 */
	public function getCards(): array{
		//$introCards = IntroCard::getIntroCards();
		if($this->cards === null){
			$this->setCards();
		}
		return $this->cards;
	}

    /**
     * @return TrackingReminderNotificationCard[]
     */
	public function setCards(): array{
		$user = QMAuth::getUser('writemeasurements', true);
		$cards = $user->getTrackingRemindersNotificationCards(true, 10);
		if(!$cards){
			// TODO: Implement onboarding button stuff
			//$onboarding = AppSettings::get()->getAppDesign()->getOnboarding();
			//$cards = $onboardingCards = $onboarding->getCards();
		}
		if(!$cards){
			$v = $user->getPrimaryOutcomeQMUserVariable();
            $cards = $v->getBestStudyCards(10);
		}
		$unique = QMArr::getUniqueByProperty($cards, 'id');
		return $this->cards = $unique;
	}
}
