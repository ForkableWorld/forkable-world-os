<?php
namespace Tests\UnitTests\WP;
use App\Models\WpPost;
use App\Models\WpTermTaxonomy;
use App\Storage\DB\TestDB;
use App\Types\QMStr;
use Corcel\Model\Taxonomy;
use Corcel\Model\Term;
use Tests\UnitTestCase;
class WpPostTest extends UnitTestCase
{
    public function testCreateWpCategory(){
        $this->skipTest('Not implemented');
        TestDB::deleteWpData();
        $u = $this->getOrSetAuthenticatedUser(1);
        $lUser = $u->l();
        $p = $u->firstOrCreateWpPost();
        $categories = $p->getCategories();
        /** @var WpTermTaxonomy|Taxonomy $category */
        $category = $categories->first();
        $description = $category->description;
        $userDescription = $lUser->getCategoryDescription();
        $this->assertEquals($userDescription, $description);
        /** @var Term $category */
        $this->assertEquals(WpPost::CATEGORY_SCIENTISTS, $p->main_category_name);
        $this->assertEquals(QMStr::slugify(WpPost::CATEGORY_SCIENTISTS), $p->main_category_slug);
    }

}
