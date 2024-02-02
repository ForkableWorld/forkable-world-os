<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\DevOps\PackageList;
use App\Models\UserVariable;
use App\Repos\IonicRepo;
use App\Repos\JsSdkRepo;
use App\Repos\QMAPIRepo;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use Illuminate\View\View;
use App\DataSources\Connectors\RescueTimeConnector;
use App\DataSources\Connectors\Responses\Github\Repo;
use App\VariableCategories\SoftwareVariableCategory;
use Symfony\Component\Yaml\Yaml;
/** Class ResumeReport
 * @package App\Reports
 */
class ResumeReport extends AnalyticalReport
{
    /**
     * @var
     */
    protected $jsRepos;
    protected $phpRepos;
    /**
     * @return string
     */
    public function generateBodyHtml(): string{
        $html = $this->generatePackagesHtml();
        return $html;
    }
    public function generateYAML():string {
        $arr = self::generatePackagesArray();
        return Yaml::dump($arr, 999);
    }
    public function generatePackagesArray():array{
        //$scalar = ArrayHelper::scalarOnly($arr);
        return ['libraries' => [
            PackageList::reposToArray($this->getPhpRepos(), "PHP Experience", ImageUrls::PHP),
            PackageList::reposToArray($this->getJsRepos(), "JavaScript Experience", ImageUrls::JS_SVG),
        ]];
    }
    /**
     * @return string
     */
    public function generatePackagesHtml():string{
        $phpChips = PackageList::reposToChips($this->getPhpRepos(), "PHP Experience", ImageUrls::PHP);
        $jsChips = PackageList::reposToChips($this->getJsRepos(), "JavaScript Experience", ImageUrls::JS_SVG);
        return "
<section id=\"portfolio\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 text-center\">
                <h2>PHP Experience</h2>
                <hr class=\"star-primary\">
            </div>
        </div>
        <br>
        <div class=\"row\">
             $phpChips
        </div>
    </div>
        <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 text-center\">
                <h2>JavaScript Experience</h2>
                <hr class=\"star-primary\">
            </div>
        </div>
        <br>
        <div class=\"row\">
             $jsChips
        </div>
    </div>
</section>
";
    }
    /**
     * @return string
     */
    public function getRescueTimeHtml():string{
        $rescuetime = RescueTimeConnector::getByUserId(230);
        //$rescuetime->importData()
        $html = '';
        $limit = 5;
        $qb = UserVariable::whereNameLike("%phpstorm%", 230);
        $variables = $qb->get();
    }
    /**
     * @return Repo[]
     */
    public function getPhpRepos(): array {
        if($this->phpRepos){return $this->phpRepos;}
        $this->addPhpRepos(QMAPIRepo::composerJson()->getRepositories());
        //$this->addPhpRepos(QMWPPluginRepo::composerJson()->getRepositories());
        return $this->phpRepos;
    }
    /**
     * @param Repo[] $repos
     * @return void
     */
    public function addPhpRepos(array $repos): void {
        foreach($repos as $repo){
            $this->phpRepos[$repo->name] = $repo;
        }
    }
    /**
     * @param Repo[] $repos
     * @return void
     */
    public function addJsRepos(array $repos): void {
        foreach($repos as $repo){
            $this->jsRepos[$repo->name] = $repo;
        }
    }
    /**
     * @return Repo[]
     */
    public function getJsRepos(): array {
        if($this->jsRepos){return $this->jsRepos;}
        if(!AppMode::isUnitOrStagingUnitTest()){
            $this->addJsRepos(IonicRepo::packageJson()->getRepositories());
            $this->addJsRepos(JsSdkRepo::packageJson()->getRepositories());
        }
        $this->addJsRepos(QMAPIRepo::packageJson()->getRepositories());
        return $this->jsRepos;
    }
    /**
     * @return array
     */
    protected function getSpreadsheetRows(): array{
        // TODO: Implement getSpreadsheetRows() method.
    }
    /**
     * @return null
     */
    public function getSourceObject(){
        // TODO: Implement getSourceObject() method.
    }
    public function generateEmailBody(): string{
        return CssHelper::inlineCss($this->getBody());
    }
    public function getCoverImage(): string{
        // TODO: Implement getCoverImage() method.
    }
    public static function getDemoReport(): AnalyticalReport{
        // TODO: Implement getDemoReport() method.
    }
    public function getCategoryName(): string{
        return SoftwareVariableCategory::NAME;
    }
    public function getShowContentView(array $params = []): View{
        return HtmlHelper::getReportViewWithoutTailwind($this->getBody(), $this, $this->getShowParams($params));
    }
    protected function getShowPageView(array $params = []): View{
        return HtmlHelper::getReportViewWithoutTailwind($this->getShowContent(), $this, $this->getShowParams($params));
    }
}
