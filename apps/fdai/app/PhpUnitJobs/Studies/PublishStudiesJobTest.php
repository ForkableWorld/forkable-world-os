<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Studies;
use App\Files\FileFinder;
use App\Models\User;
use App\Repos\StudiesRepo;
use App\Files\FileHelper;
use App\PhpUnitJobs\JobTestCase;
/** @package App\PhpUnitJobs\Studies
 */
class PublishStudiesJobTest extends JobTestCase {
    public function testPublishStudiesJob(): void{
        StudiesRepo::clonePullAndOrUpdateRepo();
        $mike = User::mike();
        //$unpublished = $mike->unPublishUnVotedStudies();
        //if($unpublished){StudiesRepo::stashPullAddCommitAndPush('unPublishUnVotedStudies');}
        $published = $mike->publishUpVotedStudies();
        if($published){StudiesRepo::stashPullAddCommitAndPush('publishUpVotedStudies');}
        //$mike->publishIndividualCaseStudies(); // Makes Jekyll build too slow!
        //$mike->publishPrimaryOutcomeStudies(10);
        //GlobalVariableRelationship::publishUpVotedStudies();  These are kind of crappy
        //GlobalVariableRelationship::publishAllUnpublishedStudies();  // Too many!
        $this->assertTrue(true);
    }
    public function testConvertJekyllToBlades(){
        $htmlFiles = FileFinder::listFiles('resources/views/so-simple', true, '.html');
        foreach($htmlFiles as $file){
            $contents = FileHelper::getContents($file);
            $contents = str_replace('{% endcomment %}', '--}}', $contents);
            $contents = str_replace('{% comment %}', '{{--', $contents);
            $contents = str_replace('{% assign ', '@php($', $contents);
            $contents = str_replace('{%- for ', '@foreach(', $contents);
            $contents = str_replace('{%- endfor -%}', '@endforeach', $contents);
            $contents = str_replace('{% if ', '@if($', $contents);
            $contents = str_replace('{% endif %}', '@endif', $contents);
            $contents = str_replace('{% else %}', '@else', $contents);
            $contents = str_replace('{% elseif ', '@elseif(', $contents);
            $contents = str_replace('{%- case ', '@switch(', $contents);
            $contents = str_replace('{%- endcase -%}', '@endswitch', $contents);
            $contents = str_replace('{%- when ', '@case(', $contents);
            $contents = str_replace(' | relative_url ', '', $contents);
            $contents = str_replace('{% include ', "@include('so-simple/_include/", $contents);
            $contents = str_replace('.html %}', "')", $contents);
            $contents = str_replace("| default:", ' ?? ', $contents);
            $contents = str_replace("{{ content }}", "@yield('content')", $contents);
            $contents = str_replace("%}", ')', $contents);
            $filename = str_replace(".html", '.blade.php', $file);
            FileHelper::writeByFilePath($filename, $contents);
        }
    }
}
