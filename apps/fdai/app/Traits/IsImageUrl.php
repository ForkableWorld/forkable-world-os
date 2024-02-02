<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Charts\HighchartExport;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringException;
use App\Fields\Avatar;
use App\Fields\Field;
use App\Fields\Image;
use App\Models\BaseModel;
use App\Properties\Base\BaseImageUrlProperty;
use App\Traits\PropertyTraits\IsUrl;
use App\Types\QMStr;
use App\UI\ImageUrls;

trait IsImageUrl {
	use IsUrl;
	public function getHardCodedValue(): ?string{
		$val = $this->getDBValue();
		if(!$val){
			return $val;
		}
		$const = ImageUrls::findConstantNameWithValue($val);
		if(!$const){
			ImageUrls::generateAndAddConstant($val);
		} else{
			return ImageUrls::class . "::" . $const;
		}
		return "'$val'";
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function assertIsImageUrl(string $url, string $type){
		QMStr::assertStringContainsOneOf($url, [
            '.' . HighchartExport::PNG,
            '.' . HighchartExport::JPG,
            '.' . HighchartExport::SVG,
            '.gif',
            'image',
            "googleusercontent",
            "icon",
            "avatar",
            "img",
            "picture", // i.e. https://graph.facebook.com/1362500640434190/picture?type=square
        ], $type);
		QMStr::assertStringDoesNotContain($url, [
            'ionic/Modo/www',
        ], $type);
		QMStr::assertIsUrl($url, $type);
	}
	/**
	 * @return HasImage|BaseModel
	 */
	abstract public function getParentModel(): BaseModel;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return Avatar::make($name ?? str_repeat(' ', 8), function($model){
			/** @var HasImage $model */
			return $model->getImage();
		})->disk($this->getParentModel()->getDisk())->path('images/' . $this->table)->maxWidth(50)->disableDownload()
			->squared()->thumbnail(function($model){
				if(is_string($model)){
					return $model;
				}
				/** @var HasImage $this */
				return $model->getImage();
			})->preview(function($model){
				if(is_string($model)){
					return $model;
				}
				/** @var HasImage $this */
				return $model->getImage();
			});
	}
	public function showOnIndex(): bool{ return false; }
	public function showOnDetail(): bool{ return true; }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->avatarField();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->avatarField();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->avatarField();
	}
	/**
	 * @throws InvalidAttributeException
	 */
	private function validateImageUrl(){
		try {
			$this->validateURL();
		} catch (InvalidStringException $e) {
			$this->throwException(__METHOD__.": ".$e->getMessage());
		}
		if($this->isImageUrl){
			try {
				BaseImageUrlProperty::assertIsImageUrl($this->getDBValue(), $this->name);
			} catch (InvalidStringException $e) {
				$this->throwException(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	public function validate(): void {
		if(!$this->shouldValidate()){
			return;
		}
		$this->validateImageUrl();
	}
	/**
	 * @return Avatar
	 */
	public function avatarField(): Avatar{
		return Avatar::make('', function(){
			return $this->getDBValue();
		})->disk('public')->maxWidth(50)->disableDownload()->thumbnail(function(){
			return $this->getDBValue();
		})->preview(function(){
			return $this->getDBValue();
		});
	}
	/**
	 * @return Image
	 */
	public function imageField(): Image{
		return Image::make('', function(){
			return $this->getDBValue();
		})->disk('public')->disableDownload()->thumbnail(function(){
			return $this->getDBValue();
		})->preview(function(){
			return $this->getDBValue();
		});
	}
}
