<?php
namespace Tests\StagingUnitTests\C\Studies;
use App\Models\Study;
use Tests\SlimStagingTestCase;
class StudyCardTest extends SlimStagingTestCase
{
    public function testStudyCards()
    {
		$s = Study::first();
		$card = $s->getCard();
		$this->compareHtmlFragment('getTailwindCard', $card->getTailwindCard());
	    $this->compareHtmlFragment('card-getHtml', $card->getHtml());
    }
}
