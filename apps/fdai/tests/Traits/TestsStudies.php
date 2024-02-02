<?php
namespace Tests\Traits;
use App\Types\QMStr;
trait TestsStudies {
	/**
	 * @param string $html
	 * @param string $key
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function compareStudyHtml(string $html, string $key = 'fullStudyHtml'): void{
		$html = QMStr::replace_between($html,
		                               'Experiment Duration (days)
                    </td>
                    <td class="text-left">',
		                               "</td>",
		                               "3102");
		$html = QMStr::replace_between($html,
		                               'Individual User Studies</p>
                            <h3 class="card-title">',
		                               "</h3>",
		                               "123");
		$this->compareHtmlPage($key, $html, true);
	}
}
