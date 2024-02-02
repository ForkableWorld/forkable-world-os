<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Widgets;
use App\Models\BaseModel;
use App\Storage\QueryBuilderHelper;
use Arrilot\Widgets\AbstractWidget;
use App\Exceptions\UnauthorizedException;
use App\Slim\Middleware\QMAuth;
use App\Utils\AppMode;
use App\Storage\DB\QMDB;
use App\UI\HtmlHelper;
use App\UI\QMColor;
use App\UI\CssHelper;
use App\Types\QMStr;
class BaseWidget extends AbstractWidget
{
    public $chartElementId = 'StringHelper::slugify($fieldTitle)."-chart-container"';
    public $color = 'blue';
    public $description = null;
    public $fieldForChartXAxis = null;
    public $footer = null;
    public $icon = null;
    public $table = null;
    public $title = null;
    public $url = null;
    public $queryParams = [];
    /**
     * @var int
     */
    public $tableCount;
    /**
     * @var int
     */
    public $queryCount;
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = []){
        if(AppMode::isApiRequest() && !QMAuth::getQMUser()){throw new UnauthorizedException();}
        if(isset($config['params'])){
            foreach($config['params'] as $key => $value){
                if($value !== null){$this->$key = $value;}
            }
        }
        foreach($config as $key => $value){
            if($value !== null){$this->$key = $value;}
        }
        parent::__construct($config);
    }
    /**
     * Placeholder for async widget.
     * You can customize it by overwriting this method.
     *
     * @return string
     */
    public function placeholder(): string{
        $this->populateFromConfigData();
        $txt = $this->getLoadingText();
        return HtmlHelper::generateLoaderInfoBoxHtml($txt, $this->getColor(), $this->getIcon());
    }
    /**
     * Async and reloadable widgets are wrapped in container.
     * You can customize it by overriding this method.
     *
     * @return array
     */
    public function container(): array{
        return [
            'element'       => 'div',
            'attributes'    => 'style="display:inline" class="arrilot-widget-container"',
        ];
    }
    public function getTitleAttribute(): string{
        $this->populateFromConfigData();
        if(!$this->title){
            return $this->title = QMStr::tableToTitle($this->table);
        }
        return $this->title;
    }
    public function getLoadingText():string{
        $title = $this->getTitleAttribute();
        return "Loading $title...";
    }
    public function getColor():string{
        $this->populateFromConfigData();
        return $this->color;
    }
    public static function getWidgetName(): string{
        $class = QMStr::toShortClassName(static::class);
        return lcfirst($class);
    }
    public static function getComponentViewParams(array $config): array{
        $merged = array_merge(static::getDefaults(), $config);
        return [
            'name' => static::getWidgetName(),
            'params' => $merged
        ];
    }
    public function toArray(): array {
        $this->populateFromConfigData();
        $arr = [];
        foreach($this as $key => $value){
            $arr[$key] = $value;
        }
        return $arr;
    }
    public static function getDefaults():array {
        return (new static())->toArray();
    }
    /**
     * @return array
     */
    public function populateFromConfigData(): array{
        $config = $this->config;
        foreach($config as $key => $value){
            if($value !== null){
                $this->$key = $value;
            }
        }
        if(!$this->url && $this->table){
            $this->url = BaseModel::generateDataLabIndexUrl($this->queryParams, $this->table);
        }
        $data = json_decode(json_encode($this), true);
        $data['config'] = $this->config;
        return $data;
    }
    /**
     * @return string
     */
    public function getIcon(): string {
        $this->populateFromConfigData();
        if(!$this->icon){
            return BaseModel::FONT_AWESOME;
        }
        return $this->icon;
    }
    /**
     * @return string
     */
    public function getHumanizedWhereString(): string{
        $this->populateFromConfigData();
        $whereString = QMDB::paramsToHumanizedWhereClauseString($this->queryParams, $this->getTable());
        return $whereString;
    }
    /**
     * @return string
     */
    public function getBootstrapColorClass(): string{
        $bootstrapColor = QMColor::toBootstrap($this->getColor());
        return $bootstrapColor;
    }
    /**
     * @return string
     */
    public function getColorString(): string{
        $bootstrapColor = QMColor::toString($this->getColor());
        return $bootstrapColor;
    }
    public function getTableCount(): int {
        if($this->tableCount !== null){
            return $this->tableCount;
        }
        return $this->tableCount = QMDB::count($this->table);
    }
    public function getQueryCount(): int {
        if($this->queryCount !== null){
            return $this->queryCount;
        }
        return $this->queryCount = QMDB::count($this->table, $this->queryParams);
    }
    /** @noinspection PhpUnused */
    public static function getWidgetParams(string $table = null): array {
        $m = new static();
        if($table){$m->table = $table;}
        $m->queryParams = array_merge($m->queryParams, QueryBuilderHelper::getParamsFromRequest());
        return static::getComponentViewParams($m->toArray());
    }
    /**
     * @return array
     */
    public function getQueryParams(): array{
        $this->populateFromConfigData();
        return $this->queryParams;
    }
    public function getUrl(array $params = []): string {
        return $this->url;
    }
    public function getSubtitleAttribute(): string {
        $m = $this->getClassTitlePlural();
        return $m ." ". $this->getHumanizedWhereString();
    }
    public function getGradientColorCss(): string {
        return CssHelper::generateGradientBackground($this->getColor());
    }
    /**
     * @return string
     */
    public function getTable(): string {
        $this->populateFromConfigData();
		if(!$this->table){le("No Table!");}
        return $this->table;
    }
    public function getHtml(): string {
        return $this->run();
    }
    public function getModel(): BaseModel{
        return BaseModel::getInstanceByTable($this->getTable());
    }
    public function getClassTitlePlural(): string {
        return $this->getModel()->getClassTitlePlural();
    }
    /**
     * @param string $table
     * @return BaseWidget
     */
    public function setTable(string $table): self{
        $this->table = $table;
        return $this;
    }
}
