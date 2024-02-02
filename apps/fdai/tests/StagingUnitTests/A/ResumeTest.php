<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A;
use App\DevOps\PackageList;
use App\Reports\ResumeReport;
use Tests\SlimStagingTestCase;
class ResumeTest extends SlimStagingTestCase
{
    public function testGenerateResume(){
        $r = new ResumeReport();
        $html = $r->generatePackagesHtml();
        $this->compareHtmlFragment('body', $html);
        $yml = $r->generateYAML();
        $this->compareStringFixture('libraries.yaml', $yml);
    }
    public function testGithubNamesParser(){
        $this->checkGithubUrlParser("https://gitlab.com/php-ai/php-ml.git",
            "php-ai", "php-ml");
        $this->checkGithubUrlParser("https://api.github.com/repos/mickelindahl/datatables.net-bs4",
            "mickelindahl", "datatables.net-bs4");
        $this->checkGithubUrlParser("https://github.com/material-components/material-components-web#readme",
            "material-components", "material-components-web");
    }
    /**
     * @param string $url
     * @param string $owner
     * @param string $repo
     */
    public function checkGithubUrlParser(string $url, string $owner, string $repo): void{
        $actual = PackageList::getGithubOwner($url);
        $this->assertEquals($owner, $actual);
        $actual = PackageList::getGithubRepoName($url);
        $this->assertEquals($repo, $actual);
    }
}
