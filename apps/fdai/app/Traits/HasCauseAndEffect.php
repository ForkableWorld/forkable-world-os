<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Buttons\States\StudyJoinStateButton;
use App\Buttons\States\StudyStateButton;
use App\Buttons\States\VariableStates\VariableSettingsVariableNameStateButton;
use App\Buttons\Vote\VoteDownButton;
use App\Buttons\Vote\VoteUpButton;
use App\Cards\ParticipantInstructionsQMCard;
use App\Cards\QMCard;
use App\Cards\StudyCard;
use App\Charts\BarChartButton;
use App\VariableRelationships\QMVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Logging\ConsoleLog;
use App\Models\UserVariable;
use App\Models\Vote;
use App\DataSources\QMClient;
use App\Logging\QMLog;
use App\Menus\DynamicMenu;
use App\Menus\QMMenu;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Studies\StudyImages;
use App\Studies\StudyLinks;
use App\Studies\StudySharing;
use App\Studies\StudyText;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use ArrayAccess;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use App\Fields\Text;
trait HasCauseAndEffect {
	use HasCategories;
	/**
	 * @return array
	 */
	protected function getStudyKeywords(): array{
		$words = [];
		$c = $this->getCauseVariable();
		$e = $this->getEffectVariable();
		$words = array_merge($words, $c->getSynonymsAttribute());
		$words = array_merge($words, $e->getSynonymsAttribute());
		$words[] = $c->getVariableCategoryName();
		$words[] = $e->getVariableCategoryName();
		$words[] = $this->getTitleAttribute();
		return $words;
	}
	/**
	 * @param ArrayAccess $before
	 * @param string $title
	 * @return array
	 */
	public static function logCauseAndEffectNames(ArrayAccess $before, string $title): array{
		$arr = self::toCauseAndEffectNames($before);
		QMLog::info(count($arr) . " records $title");
		QMLog::print($arr, $title);
		return $arr;
	}
	/**
	 * @param $before
	 * @return array
	 */
	public static function toCauseAndEffectNames($before): array{
		$arr = [];
		/** @var HasCauseAndEffect $c */
		foreach($before as $c){
			$arr[] = [
				'cause' => $c->getCauseVariableName(),
				'effect' => $c->getEffectVariableName(),
			];
		}
		return $arr;
	}
	public function getUrlParams(): array{
		return $this->getJoinUrlParams();
	}
	public function hasBoringCategory(): bool{
		if($this->getCauseVariableCategoryId() === SoftwareVariableCategory::ID){
			return true;
		}
		if($this->getEffectVariableCategoryId() === PhysicalActivityVariableCategory::ID){
			return true;
		}
		return false;
	}
	/**
	 * @return string
	 */
	public function getStudyQuestion(): string{
		$causeName = $this->getCauseVariableDisplayName();
		$effectName = $this->getEffectVariableDisplayName();
		$question = QMStr::isPlural($causeName) ? "Do " : "Does ";
		//$question .= strtolower($causeName) ." affect " . strtolower($this->getEffectVariableDisplayName()) . "?";
		$question .= $causeName . " affect " . $effectName . "?";
		return $question;
	}
	/**
	 * @return bool
	 */
	protected function isStupidVariableCategoryPair(): bool{
		$causeCategory = $this->getCauseQMVariableCategory();
		return self::stupidCategoryPair($causeCategory->getNameAttribute(), $this->getEffectQMVariableCategory()->getNameAttribute());
	}
	/**
	 * @return float|int
	 */
	public function getInterestingFactor(){
		$cause = $this->getCauseVariable();
		$effect = $this->getEffectVariable();
		$interestingFactor = $cause->getInterestingFactor() * $effect->getInterestingFactor();
		if(!$cause->isPredictor()){
			$interestingFactor /= 2;
		}
		if(!$effect->isOutcome()){
			$interestingFactor /= 2;
		}
		if($this->isStupidVariableCategoryPair()){
			$interestingFactor /= 10;
		}
		return $interestingFactor;
	}
	/**
	 * @return string
	 */
	public function getCauseAndEffectString(): string{
		return $this->causeNameWithSuffix() . ' and ' . $this->effectNameWithSuffix();
	}
	public function getCauseVariableImage(): string{
		return $this->getCauseVariable()->getImage();
	}
	public function getImage(): string{
        return $this->getStudyImages()->getImage();
    }
	public function getNumberOfUpVotesButtonHtml(): string{
		/** @noinspection DuplicatedCode */
		$upVotes = $this->getNumberOfUpVotes();
		$downVotes = $this->getNumberOfDownVotes();
		$width = 98;
		if($upVotes && $downVotes){
			$width = 60 + 38 * $upVotes / ($upVotes + $downVotes);
		}
		$params = $this->getUniqueParams();
		$params['vote'] = Vote::UP;
		//$left = "$upVotes People Consider Plausible";
		$left = "Seems right?";
		$right = "Up Vote ➤";
		/** @noinspection PhpUnusedLocalVariableInspection */
		if($useSubmitForm = false){ // TODO: Fix me
			$html = BarChartButton::getPostFormButton($left, $width, UrlHelper::getApiUrlForPath('votes'),
				QMColor::HEX_DARK_GRAY, Vote::THUMB_UP_BLACK_IMAGE, $right, $this->getUpVotesSentence(), $params);
		} else{
			$html = BarChartButton::getHtmlWithRightText($left, $width, $this->getInteractiveStudyUrl(),
				QMColor::HEX_DARK_GRAY, Vote::THUMB_UP_BLACK_IMAGE, $right, $this->getUpVotesSentence());
		}
		return $html;
	}
	public function getNumberOfDownVotesButtonHtml(): string{
		/** @noinspection DuplicatedCode */
		$upVotes = $this->getNumberOfUpVotes();
		$downVotes = $this->getNumberOfDownVotes();
		$width = 98;
		if($upVotes && $downVotes){
			$width = 60 + 38 * $downVotes / ($upVotes + $downVotes);
		}
		$params = $this->getUniqueParams();
		$params['vote'] = Vote::DOWN;
		$useSubmitForm = false; // TODO: Fix me
		//$left = "$downVotes People Consider Coincidental";
		$left = "Seems coincidental?";
		$right = "Down Vote ➤";
		/** @noinspection PhpConditionAlreadyCheckedInspection */
		if($useSubmitForm){
			$html = BarChartButton::getPostFormButton($left, $width, UrlHelper::getApiUrlForPath('votes'),
				QMColor::HEX_DARK_GRAY, Vote::THUMB_DOWN_BLACK_IMAGE, $right, $this->getDownVotesSentence(), $params);
		} else{
			$html = BarChartButton::getHtmlWithRightText($left, $width, $this->getInteractiveStudyUrl(),
				QMColor::HEX_DARK_GRAY, Vote::THUMB_DOWN_BLACK_IMAGE, $right, $this->getDownVotesSentence());
		}
		return $html;
	}
	/**
	 * @return string
	 */
	public function getPlausibilitySectionHtml(): string{
		$upText = $this->getUpVotesSentence();
		$downText = $this->getDownVotesSentence();
//		$upButton = $this->getNumberOfUpVotesButtonHtml();
//		$downButton = $this->getNumberOfDownVotesButtonHtml();
		$html = "
            <h4 class=\"text-2xl font-semibold\">Plausibility</h4>
            <p>A plausible bio-chemical mechanism between cause and effect is critical.  This is where human brains excel. </p>
            <p>Based on our responses so far, </p>
            <p>$upText</p>
            <p>$downText</p>";
		if(AppMode::isTestingOrStaging()){
			HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
		}
		return $html;
	}
	/**
	 * @return string
	 */
	public function getUpVotesSentence(): string{
		return $this->getNumberOfUpVotes() . ' humans feel that there is a plausible mechanism ' .
			'of action for a relationship between ' . $this->getCauseAndEffectString() . '. ';
	}
	/**
	 * @return string
	 */
	public function getDownVotesSentence(): string{
		return $this->getNumberOfDownVotes() . ' humans feel that any relationship observed between ' .
			$this->getCauseAndEffectString() . ' is coincidental. ';
	}
	/**
	 * @return QMButton[]
	 */
	public function getCategoryButtons(): array{
		return [
			$this->getCauseQMVariableCategory()->getButton(),
			$this->getEffectQMVariableCategory()->getButton(),
		];
	}
	public function studyLinkField(string $title = "More Details"): Text{
		return Text::make($title, function($resource){
			//if(!$this->attributes){return null;}
			return $resource->getStudyLinkHtml();
		})->asHtml();
	}
	/**
	 * @return string $studyLinkStatic
	 */
	public function getStudyLinkStatic(array $params = []): string{
		return StudyLinks::generateStudyLinkStatic($this->getStudyId(), $params);
	}
	/**
	 * @return string
	 */
	public function getStudyLinkHtml(): string{
		return HtmlHelper::getTailwindLink($this->getStudyUrl(), "View Study", "See charts and stuff",
			QMButton::TARGET_BLANK);
	}
	public function getStudyUrl(array $params = []): string{
		return $this->getStudyLinkStatic($params);
		//        return StudyLinks::generateStudyUrlDynamic($this->getCauseVariableId(),
		//            $this->getEffectVariableId(), $this->getUserId(), $this->getStudyId());
	}
	/**
	 * @return float|int
	 */
	public function calculateWeightedAverageVote(): float{
		return (1 + $this->getVoteSum()) / (1 + $this->getVoteCount());
	}
	/**
	 * @param Builder|\Illuminate\Database\Eloquent\Builder|HasMany $qb
	 * @return \Illuminate\Database\Eloquent\Builder|Builder|HasMany
	 * @noinspection PhpUnused
	 */
	public static function excludeAppsWebsitesLocationsPayments($qb){
		$qb->whereNotIn(GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
			VariableCategory::getAppsLocationsWebsiteIds());
		return $qb;
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getJoinUrl(): string{
		return $this->getJoinButton()->getUrl();
	}
	/**
	 * @return array
	 */
	public function getJoinUrlParams(): array{
		$params = [];
		if($this->getCauseVariableId()){
			$params['causeVariableId'] = $this->getCauseVariableId();
		}
		if($this->getCauseVariableName()){ // Need name for populating text
			$params['causeVariableName'] = $this->getCauseVariableName();
		}
		if($this->getEffectVariableId()){
			$params['effectVariableId'] = $this->getEffectVariableId();
		}
		if($this->getEffectVariableName()){ // Need name for populating text
			$params['effectVariableName'] = rawurlencode($this->getEffectVariableName());
		}
		if($this->getStudyId()){
			$params['studyId'] = rawurlencode($this->getStudyId());
		}
		return $params;
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getJoinButtonHTML(): string{
		return $this->getJoinButton()->getCenteredRoundOutlineWithIcon();
	}
	/**
	 * @return StudyJoinStateButton
	 */
	public function getJoinButton(): StudyJoinStateButton{
		return new StudyJoinStateButton($this);
	}
	public function getVariablesMenu(): DynamicMenu{
		return (new DynamicMenu())->setButtons([$this->getCauseButton(), $this->getEffectButton()])
			->setTitle("Variables");
	}
	public function getCategoriesMenu(): DynamicMenu{
		return (new DynamicMenu())->setButtons([$this->getCauseCategoryButton(), $this->getEffectCategoryButton()])
			->setTitle("Categories");
	}
	/**
	 * @return QMMenu[]
	 **/
	public function getStudySideMenus(): array{
		return [
			$this->getVariablesMenu(),
			$this->getCategoriesMenu(),
			$this->getStudyActionsMenu(),
		];
	}
	public function getStudyActionsMenu(): ?QMMenu{
		$menu = new DynamicMenu();
		$menu->setTitle("Actions");
		$menu->addButton($this->getJoinButton());
		$menu->addButton($this->getUserStudyButton()->setImage(ImageUrls::PERSON)->setFontAwesome(FontAwesome::USER)
			->setTextAndTitle("Your Data"));
		return $menu;
	}
	public function getUserStudyParams(): array{
		$params = $this->getCauseEffectParams();
		$params[Study::FIELD_TYPE] = StudyTypeProperty::TYPE_INDIVIDUAL;
		return $params;
	}
	public function getCauseEffectParams(): array{
		return [
			'cause_variable_name' => $this->getCauseVariableName(),
			'effect_variable_name' => $this->getEffectVariableName(),
			Study::FIELD_CAUSE_VARIABLE_ID => $this->getCauseVariableId(),
			Study::FIELD_EFFECT_VARIABLE_ID => $this->getEffectVariableId(),
		];
	}
	public function getUserStudyButton(): StudyStateButton{
		return new StudyStateButton($this->getUserStudyParams());
	}
	public function validateVariableIds(){
		if($this->getCauseVariableId() === $this->getEffectVariableId()){
			$this->exceptionIfNotProductionAPI("cause should not be the same as effect!");
		}
	}
	public function getVariableSettingsLink(): string {
		$cause = $this->getCauseQMVariable();
		$effect = $this->getEffectQMVariable();
		return $cause->getVariableSettingsLink()."\n".$effect->getVariableSettingsLink();
	}
	/**
	 * @return QMButton[]
	 */
	public function getActionSheetButtons(): array{
		$buttons = [];
		$buttons[] = $this->getUpVoteButton();
		$buttons[] = $this->getDownVoteButton();
		$buttons[] = new VariableSettingsVariableNameStateButton($this->getCauseVariableName());
		$buttons[] = new VariableSettingsVariableNameStateButton($this->getEffectVariableName());
		$buttons[] = $this->getRecalculateStudyButton();
		$buttons = array_merge($this->getStudySharing()->getSharingButtons(), $buttons);
		$buttons[] = $this->getFullStudyLinkButton();
		$buttons = array_filter($buttons);
		return $buttons;
	}
	/**
	 * @return QMButton[]
	 */
	public function getCardActionSheetButtons(): array{
		$buttons = [];
		$buttons[] = $this->getUpVoteButton();
		$buttons[] = $this->getDownVoteButton();
		$sharing = $this->getStudySharing()->getSharingButtons();
		$buttons = array_merge($sharing, $buttons);
		$buttons[] = $this->getFullStudyLinkButton();
		$buttons = array_filter($buttons);
		return $buttons;
	}
	/**
	 * @return VoteUpButton
	 */
	private function getUpVoteButton(): VoteUpButton{
		$text = "Seems Plausible";
		$url = $this->getStudyLinks()->getUpVoteUrl();
		$params = $this->getStudyLinks()->getStudyUrlParams();
		$button = new VoteUpButton($url, $params, $this->getCauseVariableDisplayNameWithSuffix(false),
			$this->getEffectVariableDisplayNameWithSuffix(false), $text);
		return $button;
	}
	/**
	 * @return StudyLinks
	 */
	public function getStudyLinks(): StudyLinks{
		return new StudyLinks($this);
	}
	/**
	 * @return VoteDownButton
	 */
	private function getDownVoteButton(): VoteDownButton{
		$text = "Seems Coincidental";
		$url = $this->getStudyLinks()->getDownVoteUrl();
		$params = $this->getStudyLinks()->getStudyUrlParams();
		$button = new VoteDownButton($url, $params, $this->getCauseVariableDisplayNameWithSuffix(false),
			$this->getEffectVariableDisplayNameWithSuffix(false), $text);
		return $button;
	}
	/**
	 * @return array|QMButton[]
	 */
	public function getButtons(): array{
		$buttons = [];
		if(!$this->getJoined()){$buttons[] = $this->getJoinButton();}
		$buttons[] = $this->getFullStudyLinkButton();
		$buttons = array_merge($buttons, $this->getVoteButtons());
		$buttons = array_filter($buttons);
		return $buttons;
	}
	/**
	 * @return QMButton[]
	 */
	public function getVoteButtons(): array{
		$buttons = [];
		if($this->getHasCorrelationCoefficientIfSet()){
			$buttons[] = $this->getUpVoteButton();
			$buttons[] = $this->getDownVoteButton();
		}
		return $buttons;
	}
	/**
	 * @return bool|QMButton
	 */
	public function getFullStudyLinkButton(){
		$c = $this->getHasCorrelationCoefficientIfSet();
		if(!$c){
			return false;
		}
		return $c->getFullStudyLinkButton();
	}
	/**
	 * @return bool|QMButton
	 */
	public function getRecalculateStudyButton(){
		if(!$this->getHasCorrelationCoefficientIfSet() || !QMAuth::getQMUserIfSet()){
			return false;
		}
		$link = $this->getStudyLinks()->getRecalculateStudyUrl();
		$button = new QMButton("Re-Analyze Data", null, null, IonIcon::refresh);
		$button->setUrl($link);
		$button->setAdditionalInformationAndTooltip("Re-analyze variables with new data/parameters");
		return $button;
	}
	/**
	 * @return string
	 */
	public function getDataQuantityOrTrackingInstructionsHTML(): string{
		return $this->getCauseQMVariable()->getDataQuantityOrTrackingInstructionsHTML() .
			$this->getEffectQMVariable()->getDataQuantityOrTrackingInstructionsHTML();
	}
	public function getCard(): StudyCard {
		return new StudyCard($this);
	}
	/**
	 * @return StudySharing
	 */
	public function getStudySharing(): StudySharing {
		return new StudySharing($this);
	}
	/**
	 * @return ParticipantInstructionsQMCard|StudyCard
	 */
	public function getStudyCard(): QMCard{
		if(!$this->getHasCorrelationCoefficientIfSet()){
			return $this->getParticipantInstructions()->getCard();
		}
		return new StudyCard($this);
	}
	/**
	 * @return QMVariable|QMUserVariable
	 */
	public function getEffectQMVariable(): QMVariable {
		if($this->typeIsIndividual()){
			return $this->getEffectQMUserVariable();
		} else {
			return $this->getEffectQMCommonVariable();
		}
	}
	/**
	 * @return QMVariable|QMUserVariable
	 */
	public function getCauseQMVariable(): QMVariable {
		if($this->typeIsIndividual()){
			return $this->getCauseQMUserVariable();
		} else {
			return $this->getCauseQMCommonVariable();
		}
	}
	/**
	 * @return int
	 */
	public function getCauseVariableId(): int {
		return $this->getAttribute('cause_variable_id');
	}
	/**
	 * @return int
	 */
	public function getEffectVariableId(): int {
		if(!$this->effectVariableId){
			$this->effectVariableId = $this->getOrSetEffectQMVariable()->getVariableIdAttribute();
		}
		return $this->effectVariableId;
	}
	public function getCauseVariable(): Variable{
		/** @var Variable $v */
		$v = $this->getRelationIfLoaded('cause_variable');
		if(!$v){$v = Variable::findInMemoryOrDB($this->getCauseVariableId());}
		if(!$v){le("Could not find cause variable id {$this->getCauseVariableId()}\n"
		           ."\nthis: " .$this->getDataLabShowUrl()
		           ."\nvariables: " .Variable::generateAstralIndexUrl()
		);}
		if($v){$this->setRelationAndAddToMemory('cause_variable', $v);}
		return $v;
	}
	public function getCauseQMVariableCategory(): QMVariableCategory{
		$id = $this->getCauseVariableCategoryId();
		if(!$id){
			$id = $this->getCauseVariableCategoryId();
			le("no cause category id!");
		}
		return QMVariableCategory::find($id);
	}
	public function getCauseDropdown(): string{
		return $this->getCauseVariable()->getDataLabImageNameDropDown();
	}
	public function getCauseChip(): string{
		$html = $this->getCauseVariable()->getDataLabMDLChipHtml();
		return $html;
	}
	public function getCauseNameLink(): string{
		$name = $this->getCauseVariableDisplayName();
		return HtmlHelper::generateLink($name, $this->getCauseUrl(), true, "See $name Details");
	}
	/**
	 * @return string
	 */
	public function getCauseVariableName(): string{
		if(property_exists($this, 'causeVariableName')){
			if($n = $this->causeVariableName){
				return $n;
			}
		}
		if(method_exists($this, 'getOrSetCauseQMVariable')){
			if($v = $this->getOrSetCauseQMVariable()){
				return $v->name;
			}
		}
		return $this->getCauseVariable()->name;
	}
	/**
	 * @return string
	 */
	public function getCauseVariableNameLink(): string{
		return $this->getCauseVariable()->getButton()->getLink();
	}
	/**
	 * @param HasCauseAndEffect[]|Collection $correlations
	 * @param string|int $category
	 * @return HasCauseAndEffect[]|Collection
	 */
	public static function filterByCauseCategory($correlations, $category){
		$category = QMVariableCategory::find($category);
		return $correlations->filter(function($c) use ($category){
			/** @var HasCauseAndEffect $c */
			return $c->getCauseVariableCategoryId() == $category->id;
		});
	}
	/**
	 * @return QMUnit
	 */
	public function getCauseVariableCommonUnit(): QMUnit{
		if(property_exists($this, 'causeVariable') && $this->causeVariable){
			$cv = $this->causeVariable;
		} else{
			$cv = $this->getCauseVariable();
		}
		return $cv->getCommonUnit();
	}
	public function getCauseUrl(): string{
		if(method_exists($this, 'getCauseUserVariable')){
			return UserVariable::generateShowUrl($this->getCauseUserVariableId());
		}
		return Variable::generateShowUrl($this->getCauseVariableId());
	}
	/**
	 * @return string
	 */
	public function getCauseVariableDisplayName(): string{
		return QMStr::displayName($this->getCauseVariableName());
	}
	/**
	 * @return string
	 */
	public function causeNameWithSuffix(): string{
		$without = $this->getCauseNameWithoutCategoryOrUnit();
		return VariableNameProperty::addSuffix($without, $this->getCauseVariableCommonUnit(), true,
			$this->getCauseQMVariableCategory());
	}
	/**
	 * @return string
	 */
	public function getCauseNameWithoutCategoryOrUnit(): string{
		$original = $this->getCauseVariableName();
		return VariableNameProperty::removeSuffix($original, $this->getCauseVariableCommonUnit(), true);
	}
	/**
	 * @return string
	 */
	public function getCauseVariableCommonUnitAbbreviatedName(): string{
		return $this->getCauseVariableCommonUnit()->getAbbreviatedName();
	}
	public function getCauseButton(): QMButton{ return $this->getCauseVariable()->getButton(); }
	public function getCauseCategoryButton(): QMButton{ return $this->getCauseVariableCategory()->getButton(); }
	public function getCauseVariableCategory(): VariableCategory{
		return VariableCategory::findInMemoryOrDB($this->getCauseVariableCategoryId());
	}
	/**
	 * @return QMUnit
	 */
	public function getEffectVariableCommonUnit(): QMUnit{
		if(property_exists($this, 'effectVariable') && $this->effectVariable){
			$cv = $this->effectVariable;
		} else{
			$cv = $this->getEffectVariable();
		}
		return $cv->getCommonUnit();
	}
	/**
	 * @return string
	 */
	public function getEffectVariableValence(): string{
		if(property_exists($this, 'effectVariableValence') && $this->effectVariableValence){
			return $this->effectVariableValence;
		}
		$valence = $this->getEffectVariable()->valence;
		if($valence){
			return $valence;
		}
		$cat = $this->getEffectQMVariableCategory();
		return $cat->getValence();
	}
	/**
	 * @return string
	 */
	public function getEffectVariableName(): string{
		if(property_exists($this, 'effectVariableName')){
			if($n = $this->effectVariableName){
				return $n;
			}
		}
		if(method_exists($this, 'getOrSetEffectQMVariable')){
			if($v = $this->getOrSetEffectQMVariable()){
				return $v->name;
			}
		}
		return $this->getEffectVariable()->getNameAttribute();
	}
	/**
	 * @param HasCauseAndEffect[]|Collection $correlations
	 * @param string|int $category
	 * @return HasCauseAndEffect[]|Collection
	 */
	public static function filterByEffectCategory($correlations, $category){
		$category = QMVariableCategory::find($category);
		return $correlations->filter(function($c) use ($category){
			/** @var HasCauseAndEffect $c */
			return $c->getEffectQMVariableCategory()->id === $category->id;
		});
	}
	public function getEffectUrl(): string{
		if(method_exists($this, 'getEffectUserVariable')){
			return UserVariable::generateShowUrl($this->getEffectUserVariableId());
		}
		return Variable::generateShowUrl($this->getEffectVariableId());
	}
	public function getEffectNameLink(): string{
		$name = $this->getEffectVariableDisplayName();
		return HtmlHelper::generateLink($name, $this->getEffectUrl(), true, "See $name Details");
	}
	public function getEffectVariable(): Variable{
		/** @var Variable $v */
		$v = $this->getRelationIfLoaded('effect_variable');
		if(!$v){$v = Variable::findInMemoryOrDB($this->getEffectVariableId());}
		if($v){$this->setRelationAndAddToMemory('effect_variable', $v);}
		return $v;
	}
	/**
	 * @return bool
	 */
	public function effectValenceIsNegative(): bool{
		return $this->getEffectVariableValence() === BaseValenceProperty::VALENCE_NEGATIVE;
	}
	/**
	 * @return string
	 */
	public function getEffectVariableDisplayName(): string{
		return QMStr::displayName($this->getEffectVariableName());
	}
	/**
	 * @return string
	 */
	public function effectNameWithSuffix(): string{
		return VariableNameProperty::addSuffix($this->getEffectNameWithoutCategoryOrUnit(),
			$this->getEffectVariableCommonUnit(), true, $this->getEffectQMVariableCategory());
	}
	public function getEffectVariableImage(): string{
		return $this->getEffectVariable()->getImage();
	}
	public function getEffectQMVariableCategory(): QMVariableCategory{
		return QMVariableCategory::find($this->getEffectVariableCategoryId());
	}
	/**
	 * @return string
	 */
	public function getEffectNameWithoutCategoryOrUnit(): string{
		return VariableNameProperty::removeSuffix($this->getEffectVariableName(), $this->getEffectVariableCommonUnit(),
			true);
	}
	/**
	 * @return string
	 */
	public function getEffectVariableCommonUnitAbbreviatedName(): string{
		return $this->getEffectVariableCommonUnit()->getAbbreviatedName();
	}
	/**
	 * @return \App\Buttons\QMButton|\App\Buttons\VariableButton
	 */
	public function getEffectButton(){ return $this->getEffectVariable()->getButton(); }
	public function getEffectCategoryButton(): QMButton{ return $this->getEffectVariableCategory()->getButton(); }
	public function getEffectVariableCategory(): VariableCategory{
		return VariableCategory::findInMemoryOrDB($this->getEffectVariableCategoryId());
	}
	/**
	 * @return string
	 */
	public function getGaugeAndImagesWithTagLine(): string{
		$html = $this->getStudyHtml()->getGaugeAndImagesWithTagLine();
		return $html;
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getTitleGaugesTagLineHeader(bool $arrows = false, bool $hyperLinkNames = false): string{
		$html = '
            <div style="text-align: center;">
                ' . $this->getTitleHtml($arrows, $hyperLinkNames) . '
            </div>';
		$html .= $this->getGaugeAndImagesWithTagLine();
		if(AppMode::isTestingOrStaging()){$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);}
		return $html;
	}
	public function getTitleHtml(bool $arrows = false, bool $hyperLinkNames = false): string{
		return $this->getStudyHtml()->getTitleHtml($arrows, $hyperLinkNames);
	}
	/**
	 * @return StudyText
	 */
	public function getStudyText(): StudyText{
		$c = $this->getHasCorrelationCoefficientIfSet();
		if(!$c){$c = null;}
		return new StudyText($c, $c ?? $this);
	}
	public function getCauseQMCommonVariable(): QMCommonVariable{
		return $this->getCauseVariable()->getQMCommonVariable();
	}
	public function getEffectQMCommonVariable(): QMCommonVariable{
		return $this->getEffectVariable()->getQMCommonVariable();
	}
	/**
	 * @param bool $allowDbQueries
	 * @return string
	 */
	public function getCauseVariableDisplayNameWithSuffix(bool $allowDbQueries = true): string{
		return $this->getCauseVariable()->getDisplayNameWithCategoryOrUnitSuffix();
	}
	/**
	 * @param bool $allowDbQueries
	 * @return string
	 */
	public function getEffectVariableDisplayNameWithSuffix(bool $allowDbQueries = true): string{
		return $this->getEffectVariable()->getDisplayNameWithCategoryOrUnitSuffix();
	}
	/**
	 * @return QMVariableRelationship|GlobalVariableRelationship|\App\Models\UserVariableRelationship|null
	 */
	public function getHasCorrelationCoefficientIfSet(){
		if($this instanceof QMVariableRelationship){
			return $this;
		}
		if($this instanceof UserVariableRelationship || $this instanceof GlobalVariableRelationship){
			return $this;
		}
		if(property_exists($this, 'statistics')){
			return $this->statistics;
		}
		return null;
	}
	public function getCauseVariableCategoryId(): int{
		return $this->getCauseVariable()->getVariableCategoryId();
	}
	public function getEffectVariableCategoryId(): int{
		return $this->getEffectVariable()->getVariableCategoryId();
	}
	/**
	 * @return \Illuminate\Support\Collection|Vote[]
	 */
	public function getVotes():Collection{
		$this->l()->loadMissing('votes');
		return $this->l()->votes;
	}
	/**
	 * @return float
	 */
	public function getVoteSum(): float {
		$sum = 0;
		if(!$this->hasId()){return 0;}
		$votes = $this->getVotes();
		if(!$votes){
			return 0;
		}
		foreach($votes as $vote){
			if(isset($vote->value)){
				$sum += $vote->value;
			}
		}
		return $sum;
	}
	/**
	 * @return int
	 */
	public function getNumberOfUpVotes(): int {
		$votes = $this->getVotes();
		$count = 0;
		foreach($votes as $vote){if($vote->value){$count++;}}
		return $count;
	}
	/**
	 * @return int
	 */
	public function getNumberOfDownVotes(): int {
		$votes = $this->getVotes();
		$count = 0;
		foreach($votes as $vote){if(!$vote->value){$count++;}}
		return $count;
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 */
	protected function causeValueCommonUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		$v = $this->getCauseVariable();
		return $v->getQMUnit()->getValueAndUnitString($inCommonUnit, false, $precision);
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 */
	protected function effectValueCommonUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		$v = $this->getEffectVariable();
		return $v->getQMUnit()->getValueAndUnitString($inCommonUnit, false, $precision);
	}
	/**
	 * @return string
	 */
	public function getParticipantInstructionsHtml(): string{
		$cause = $this->getCauseVariable()->getTrackingInstructionsHtml();
		$effect = $this->getEffectVariable()->getTrackingInstructionsHtml();
		return $cause."<br>".$effect;
	}
	/**
	 * @return string
	 */
	public function getSharingDescription(): string{
		return $this->getStudyQuestion();
	}
	public function getIsPublic(): bool{
        return $this->getAttribute('is_public') ?? false;
    }
	abstract public function getId();
	public function getIonIcon(): string{
        return $this->l()->getIonIcon();
    }
	/**
	 * @return string
	 */
	public function getSharingTitle(): string{
		return $this->getStudyQuestion();
	}
	/**
	 * @return StudyImages
	 */
	public function getStudyImages(): StudyImages{
		return new StudyImages(null,
			$this);
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getStudyTitle(bool $arrows = false, bool $hyperLinkNames = false): string{
		$c = $this->findHasCorrelationCoefficient();
		if($c && $c->getCorrelationCoefficient() !== null){
			$title = $c->getPredictorExplanationTitle($arrows, $hyperLinkNames);
			return StudyText::formatTitle($c, $title, $arrows);
		}
		return $this->getStudyQuestion();
	}
	/**
	 * @return string
	 */
	public function getStudyAbstract(): string{
		return $this->getParticipantInstructionsHtml();
	}
	public function hasCauseVariable(): ?QMVariable{
		if(!property_exists($this, 'causeVariable')){return null;}
		return $this->causeVariable;
	}
	public function hasEffectVariable(): ?QMVariable{
		if(!property_exists($this, 'effectVariable')){return null;}
		return $this->effectVariable;
	}
}
