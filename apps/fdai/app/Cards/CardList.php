<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\UI\HtmlHelper;
class CardList {
	public $cards;
	public $title;
	/**
	 * @return QMCard[]
	 */
	public function getCards(): array{
		return $this->cards;
	}
	/**
	 * @param QMCard[] $cards
	 */
	public function setCards(array $cards): void{
		$this->cards = $cards;
	}
	public function getToDoList(): string{
		return HtmlHelper::renderView(view('todo-list', ['cardList' => $this]));
	}
}
