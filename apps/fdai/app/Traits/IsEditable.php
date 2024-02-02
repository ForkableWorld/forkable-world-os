<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Buttons\Value\ChangeTextValueButton;
use App\Buttons\Value\DeleteValueButton;
use App\Types\QMStr;
trait IsEditable {
	abstract public function getEditButton(): QMButton;
	abstract public function getUrlParams(): array;
	public function getEditUrl(): string{
		$b = $this->getEditButton();
		return $b->getUrl();
	}
	public function getEditApiUrl(array $params = []): ?string{
		//$camel = StringHelper::camelize(static::TABLE);
		$camel = static::TABLE;
		return url("api/v6/$camel/" . $this->getId(), $params);
	}
	public function getChangeValueButton(string $field): QMButton{
		$value = $this->getAttribute($field) ?? $field;
		$text = QMStr::snakeToTitle($field);
		$class = $this->getClassNameTitle();
		$b = new ChangeTextValueButton($field, $value, $this->getEditApiUrl(), "Change $class $text from $value");
		return $b;
	}
	public function getDeleteValueButton(string $field): QMButton{
		$b = new DeleteValueButton($field, $this->getAttribute($field), $this->getEditApiUrl(), $this->getNameAttribute());
		$slug = $this->getUniqueIndexIdsSlug();
		$id = "delete-$slug-$field-button";
		$b->setId($id);
		$b->setUrl("#" . $id);
		return $b;
	}
}
