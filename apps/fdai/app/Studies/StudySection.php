<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\QMButton;
use App\Utils\AppMode;
use App\UI\HtmlHelper;
use App\Types\QMStr;
class StudySection {
    public $title;
    public $body;
    public $buttons = [];
    public $image;
    /**
     * @var string
     */
    public $id;
    /**
     * @param string $titleText
     * @param string $bodyHtml
     * @param string $image
     */
    public function __construct(string $titleText, string $bodyHtml, string $image){
        $this->title = $titleText; // Wrapped with <h2> so don't include HTML tags
        $this->body = $bodyHtml; // Can include divs and stuff so don't wrap with <p> tag below
        $this->image = $image;
        $this->id = QMStr::slugify($titleText);
        if(AppMode::isTestingOrStaging()){HtmlHelper::checkForMissingHtmlClosingTags($bodyHtml, $titleText);}
    }
    /**
     * @return QMButton[]
     */
    public function getButtons():array{
        return $this->buttons;
    }
    public function getHtml():string{
        return HtmlHelper::renderView(view('study-section', ['section' => $this]));
    }
	/**
	 * @param array $buttons
	 */
	public function setButtons(array $buttons): void{
		$this->buttons = $buttons;
	}
}
