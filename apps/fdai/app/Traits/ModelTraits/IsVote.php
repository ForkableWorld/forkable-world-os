<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Models\Vote;
use App\Models\GlobalVariableRelationship;
use App\Studies\StudyLinks;
use App\Traits\HasCauseAndEffect;
use App\UI\CssHelper;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
trait IsVote {
	use HasCauseAndEffect;
	abstract public function upVoted(): bool;
	abstract public function downVoted(): bool;
	public function getIonIcon(): string{
		if($this->upVoted()){
			return IonIcon::thumbsup;
		}
		if($this->downVoted()){
			return IonIcon::thumbsdown;
		}
		return IonIcon::help;
	}
	public function getTagLine(): string{
		return $this->getSubtitleAttribute();
	}
	public function getImage(): string{
		if($this->upVoted()){
			return Vote::THUMB_UP_BLACK_IMAGE;
		}
		if($this->downVoted()){
			return Vote::THUMB_DOWN_BLACK_IMAGE;
		}
		return ImageUrls::QUESTION_MARK;
	}
	public function getAgreeDisagreeOrUnsure(): string{
		if($this->upVoted()){
			return "AGREES";
		}
		if($this->downVoted()){
			return "DISAGREES";
		}
		return "UNSURE";
	}
	public function getNameLink(array $params = [], int $maxLength = 50): string{
		$str = $this->getCauseVariable()->getDataLabDisplayNameLink() . " affects " .
			$this->getEffectVariable()->getDataLabDisplayNameLink();
		$url = $this->getUrl();
		$img = $this->getImageLink($params, "height: 16px; cursor: pointer;");
		if($this->upVoted()){
			return "$img<a href='$url'>AGREES</a> <span style='margin: auto;'> that </span> $str";
		}
		if($this->downVoted()){
			return "$img<a href='$url'>DISAGREES</a> <span style='margin: auto;'> that </span> $str";
		}
		return "$img<a href='$url'>Has not voted</a> on whether $str";
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->getAgreeDisagreeOrUnsure() . " <span style='margin: auto;'> that </span> " .
			$this->getCauseVariable()->getNameAttribute() . " <span style='margin: auto;'> affects </span> " .
			$this->getEffectVariable()->getNameAttribute();
	}
	public function getDescriptionHtml(): string{
		return $this->getDataLabMDLChipHtml() . " <span style='margin: auto;'> that </span> " .
			$this->getCauseVariable()->getDataLabMDLChipHtml() . " <span style='margin: auto;'> affects </span> " .
			$this->getEffectVariable()->getDataLabMDLChipHtml();
	}
	public function getColorGradientCss(): string{
		if($this->upVoted()){
			return CssHelper::generateGradientBackground(QMColor::HEX_GREEN);
		}
		if($this->downVoted()){
			return CssHelper::generateGradientBackground(QMColor::HEX_RED);
		}
		return CssHelper::generateGradientBackground(QMColor::HEX_BLUE);
	}
	public function getImageLink(array $params = [], string $style = null): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE;
		}
		$name = $this->getAgreeDisagreeOrUnsure();
		$img = $this->getImage();
		$url = $this->getUrl($params);
		$tooltip = $this->getSubtitleAttribute();
		$colorStyle = $this->getColorGradientCss();
		return "
            <a href=\"$url\" title='$tooltip'>
                <!-- Contact Chip -->
                <span class=\"mdl-chip mdl-chip--contact\" style='$colorStyle; margin: 0; padding: 0; max-width: 32px;'>
                    <img class=\"mdl-chip__contact\" src=\"$img\" alt=\"$name\" style='padding: 3px;'>
                </span>
            </a>
        ";
	}
	public function getColor(): string{
		if($this->upVoted()){
			return QMColor::HEX_GREEN;
		}
		if($this->downVoted()){
			return QMColor::HEX_RED;
		}
		return QMColor::HEX_YELLOW;
	}
	public function getEditUrl(array $params = []): string{
		return StudyLinks::generateStudyUrlDynamic($this->getCauseVariableId(), $this->getEffectVariableId());
	}
	public function global_variable_relationship(): BelongsTo{
		return $this->belongsTo(GlobalVariableRelationship::class, self::FIELD_AGGREGATE_CORRELATION_ID,
			GlobalVariableRelationship::FIELD_ID);
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	/**
	 * @return Vote|Builder|\Illuminate\Database\Query\Builder
	 */
	public static function whereUpVoted(){
		return static::whereValue(Vote::UP_VALUE);
	}
	/**
	 * @return string
	 */
	public function __toString(){
		if($this->relationLoaded('cause_variable') && $this->relationLoaded('effect_variable')){
			return $this->getTitleAttribute() . " " . static::getClassNameTitle();
		}
		return static::getClassNameTitle() . " with ID " . $this->id;
	}
	/**
	 * @return bool
	 */
	public function isUpVote(): bool {
		return $this->getValue() === Vote::UP_VALUE;
	}
	/**
	 * @return bool
	 */
	public function isDownVote(): bool {
		return $this->getValue() === Vote::DOWN_VALUE;
	}
	/**
	 * @return bool
	 */
	public function isNoVote(): bool {
		return !$this->isUpVote() && !$this->isDownVote();
	}
}
