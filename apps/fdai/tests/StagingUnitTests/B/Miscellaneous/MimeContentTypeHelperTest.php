<?php
namespace Tests\StagingUnitTests\B\Miscellaneous;
use App\Files\MimeContentTypeHelper;
use Tests\SlimStagingTestCase;
class MimeContentTypeHelperTest extends SlimStagingTestCase {
    public function testGuessMimeContentTypeBasedOnFileName() {
        $result = MimeContentTypeHelper::guessMimeContentTypeBasedOnFileName("IMG_0375.JPG");
        $this->assertEquals(MimeContentTypeHelper::JPG, $result);
    }
}
