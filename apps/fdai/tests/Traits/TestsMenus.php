<?php /** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\Traits;
use App\Logging\QMLog;
use App\Menus\QMMenu;
trait TestsMenus {
	/**
	 * @param array $expectedTitles
	 * @param QMMenu|string $menuClass
	 */
	public function checkMenuButtonTitlesAndUrls(array $expectedTitles, string $menuClass){
		$m = $menuClass::get();
		$buttons = $m->getButtons();
		self::assertButtonTitles($expectedTitles, $buttons);
		return; // Todo - check urls
		foreach($buttons as $button){
			try {
				$button->testUrl();
			} catch (\Throwable $e){
				if($button->requiresAuthentication()){
					QMLog::info("TODO: handle authentication in test buttons. ".$e->getMessage());
				} else{
					throw $e;
				}
			}
		}
	}
}
