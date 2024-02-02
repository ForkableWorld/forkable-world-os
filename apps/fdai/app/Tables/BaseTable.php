<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Exceptions\NotEnoughDataException;
use App\Traits\HasClassName;
use App\Types\BoolHelper;
use Jupitern\Table\Table;
abstract class BaseTable extends Table {
	use HasClassName;
	const DESC = 'desc';
	const ASC = 'asc';
	protected $id;
	protected $orderColumnIndex = 0;
	protected $orderDirection = self::DESC;
	protected $pageLength = 10; // Keep this short so we can see header row
	protected $searching = true;
	protected $paging = true;
	protected $info = true;
	/**
	 * BaseTable constructor.
	 */
	public function __construct(){
		parent::__construct();
		$this->attr('table', 'id', $this->getId())
			->attr('table', 'class', 'table table-bordered table-striped table-hover')
			->attr('table', 'cellspacing', '0')->attr('table', 'width', '100%');
	}
	/**
	 * @param int $orderColumnIndex
	 */
	public function setOrderColumnIndex(int $orderColumnIndex): void{
		$this->orderColumnIndex = $orderColumnIndex;
	}
	/**
	 * @param string $orderDirection
	 */
	public function setOrderDirection(string $orderDirection): void{
		$this->orderDirection = $orderDirection;
	}
	/**
	 * @param string $id
	 */
	public function setId(string $id): void{
		$this->id = $id;
	}
	/**
	 * @param bool $returnOutput
	 * @return string
	 */
	public function render($returnOutput = true): string{
		$html = parent::render($returnOutput);
		$initialization = $this->getInitializationScript();
		return $html . $initialization;
	}
	abstract protected function getTitleAttribute(): string;
	abstract protected function getSubtitleAttribute(): string;
	/**
	 * @param bool $returnOutput
	 * @return string
	 */
	public function renderWithTitle(bool $returnOutput = true): string{
		$html = $this->render($returnOutput);
		$title = $this->getTitleAttribute();
		$desc = $this->getSubtitleAttribute();
		$id = $this->getId();
		return "
<div id=\"$id-container\" style=\"padding-top: 1rem; padding-bottom: 1rem;\">
<h2 id=\"$id-heading\" class=\"text-4xl\">$title</h2>
<p>
$desc
</p>
$html
</div>
";
	}
	/**
	 * @param bool $returnOutput
	 * @return string
	 */
	public function renderWithCard(bool $returnOutput = true): string{
		$html = $this->render($returnOutput);
		$title = $this->getTitleAttribute();
		$desc = $this->getSubtitleAttribute();
		$id = $this->getId();
		try {
			$html = view('tailwind-card', [
				'content' => $html,
				'title' => $title,
				'subtitle' => $desc,
			])->render();
		} catch (\Throwable $e) {
			le($e);
		}
		return "
<div id=\"$id-container\">
$html
</div>
";
	}
	private function getRowCount(): int{
		$data = $this->getData();
		return count($data);
	}
	/**
	 * @param bool $searching
	 */
	public function setSearching(bool $searching): void{
		$this->searching = $searching;
	}
	/**
	 * @param bool $paging
	 */
	public function setPaging(bool $paging): void{
		$this->paging = $paging;
	}
	/**
	 * @param bool $info
	 */
	public function setInfo(bool $info): void{
		$this->info = $info;
	}
	/**
	 * @param int $pageLength
	 */
	public function setPageLength(int $pageLength): void{
		$this->pageLength = $pageLength;
	}
	/**
	 * @return string
	 */
	public function isSearching(): string{
		return BoolHelper::toString($this->searching);
	}
	/**
	 * @return string
	 */
	public function isInfo(): string{
		return BoolHelper::toString($this->info);
	}
	/**
	 * @return string
	 */
	public function isPaging(): string{
		return BoolHelper::toString($this->paging);
	}
	/**
	 * @return string
	 */
	protected function getInitializationScript(): string{
		$count = $this->getRowCount();
		if($count < 3){
			$this->setSearching(false);
		}
		if($count < $this->pageLength){
			$this->setPaging(false);
			$this->setInfo(false);
		}
		$searching = $this->isSearching();
		$paging = $this->isPaging();
		$info = $this->isInfo();
		$id = $this->getId();
		$initialization = "
<script>
    $(document).ready(function () {
        $('#$id').DataTable({
            \"searching\": $searching,
            \"paging\": $paging,
            \"info\": $info,
            \"pageLength\": $this->pageLength,
            \"order\": [[$this->orderColumnIndex, \"$this->orderDirection\"]]
        });
    });
</script>
        ";
		return $initialization;
	}
	/**
	 * @return mixed
	 */
	public function getId(): string{
		return $this->id;
	}
	/** @noinspection PhpUnused */
	public static function generateHtml(...$args): ?string{
		try {
			$m = new static(...$args);
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (NotEnoughDataException $e) {
			return null;
		}
		return $m->renderWithTitle(true);
	}
}
