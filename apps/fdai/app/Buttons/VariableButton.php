<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Buttons\States\OnboardingStateButton;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Types\QMArr;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
class VariableButton extends QMButton {
	public $variable;
	/**
	 * VariableButton constructor.
	 * @param Variable $v
	 */
	public function __construct($v){
		$this->variable = $v;
		parent::__construct($v->getTitleAttribute(), $v->getUrl(), Variable::COLOR, $v->getIonIcon());
		$catId = $v->getVariableCategoryId();
		/** @var QMVariableCategory $cat */
		$cats[$catId] = $cat = $cats[$catId] ?? QMVariableCategory::find($catId);
		$img = $v->getImage();
		if(!$img){
			$img = $cat->getImage();
		}
		$this->setImage($img);
		$this->setFontAwesome($cat->getFontAwesome());
		$outcomes = $v->getNumberOfGlobalVariableRelationshipsAsCause();
		$predictors = $v->getNumberOfGlobalVariableRelationshipsAsEffect();
		if($predictors && $outcomes){
			$num = $predictors + $outcomes;
			$this->setBadgeText($num);
			$this->setTooltip("$num studies on the causes or effects of " . $v->getTitleAttribute());
		} elseif($predictors){
			$this->setBadgeText($predictors);
			$this->setTooltip("$predictors studies on the causes of " . $v->getTitleAttribute());
		} elseif($outcomes){
			$this->setBadgeText($outcomes);
			$this->setTooltip("$outcomes studies on the effects of " . $v->getTitleAttribute());
		} else{
			$this->setTooltip("Studies on the causes or effects of " . $v->getTitleAttribute());
		}
	}
	/**
	 * @param Variable[]|UserVariable[]|Collection $variables
	 * @return array
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function toButtons($variables): array{
		$buttons = [];
		foreach($variables as $v){
			$b = new static($v);
			$buttons[$v->getNameAttribute()] = $b;
		}
		QMArr::sortDescending($buttons, 'badgeText');
		return $buttons;
	}
	/**
	 * @param array|null $variableCategoryIds
	 * @return static[]
	 */
	public static function getWithStudies(array $variableCategoryIds = null): array{
		/** @var Variable[] $variables */
		$variables = self::withStudies($variableCategoryIds)
			//->limit(10)
			->get();
		return static::toButtons($variables);
	}
	private static function withStudies(array $variableCategoryIds = null): Builder{
		$qb = Variable::indexQBWithCorrelations();
		if($variableCategoryIds){
			$qb->whereIn(Variable::FIELD_VARIABLE_CATEGORY_ID, $variableCategoryIds);
		}
		return $qb;
	}
	public static function chipSearch(array $notFoundButtons = null): string{
		return static::toChipSearch(Variable::getIndexButtons(), "Search for a symptom or treatment...", "Variables",
			$notFoundButtons);
	}
	public static function chipSearchForCategoryWithStudies(int $variableCategoryId,
		array $notFoundButtons = null): string{
		$cat = QMVariableCategory::find($variableCategoryId);
		return static::toChipSearch(static::getWithStudies([$cat->getId()]),
			"Search for a {$cat->getNameSingular()}...", $cat->getNameAttribute(), $notFoundButtons);
	}
	public static function chipSearchForCategory(int $variableCategoryId): string{
		$cat = QMVariableCategory::find($variableCategoryId);
		$qb = Variable::indexQBWithCorrelations();
		$qb->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $variableCategoryId);
		$models = $qb->get();
		return static::toChipSearch($models, "Search for a {$cat->getNameSingular()}...",
			$cat->getNameAttribute(), [
				Variable::getSearchAllIndexButton(),
			OnboardingStateButton::instance(),
		]);
	}
	/**
	 * @return Variable
	 */
	public function getVariable(): Variable{
		return $this->variable->getVariable();
	}
	public function getKeywords(): array{
		return $this->getVariable()->getKeyWords();
	}
}
