<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Wiki;
use Illuminated\Wikipedia\MediaWiki;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
class QMMediaWiki {
	protected $pages;
	protected string $url;
	/**
	 * @var \App\Wiki\MediaWikiBot
	 */
	private MediaWikiBot $bot;
	private array $grabbed;
	private MediaWiki $grabber;
	private array $previews;
	private MediawikiFactory $services;
	/**
	 * @throws \Mediawiki\Api\UsageException
	 */
	public function __construct(){
		$this->url = 'https://mediawiki.CrowdsourcingCures.org/w/api.php';
		$api = new MediawikiApi($this->url);
		$this->bot = new MediaWikiBot();
		$api->login( new ApiUser( $this->bot->getUser(), $this->bot->getPass() ) );
		$this->services = new MediawikiFactory( $api );
		$this->grabber = new MediaWiki($this->url);
	}
	/**
	 * @return \Illuminated\Wikipedia\Grabber\Page[]
	 */
	public function getGrabbedPages(): array{
		return $this->grabbed;
	}
	/**
	 * @return \Illuminated\Wikipedia\Grabber\Preview[]
	 */
	public function getPreviews(): array{
		return $this->previews;
	}
	public function getAllPages(): array{
		if($this->pages){return $this->pages;}
		$svc = $this->services;
		$bot = $this->bot;
		$titles = $bot->getAllPagesTitles();
		foreach($titles as $title){
			$this->pages[$title] = $svc->newPageGetter()->getFromTitle($title);
			$this->grabbed[$title] = $this->grabber->page($title);
			$this->previews[$title] = $this->grabber->preview($title);
		}
		return $this->pages;
	}
	public function syncToWikiJs(){
		$pages = $this->getAllPages();
		foreach($pages as $title => $page){
			$this->getWikiJs();
		}
	}
}
