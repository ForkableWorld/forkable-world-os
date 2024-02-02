<?php
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\VariableCategories\SocialInteractionsVariableCategory;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\FacebookPagesLikedCommonVariable;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\FacebookPostsMadeCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class FacebookVariablesTest extends SlimStagingTestCase {
    public function testFacebookVariables(){
        $v = QMCommonVariable::findByNameOrId(FacebookPagesLikedCommonVariable::NAME);
        $this->assertEquals(SocialInteractionsVariableCategory::NAME, $v->variableCategoryName);
        $v = QMCommonVariable::findByNameOrId(FacebookPostsMadeCommonVariable::NAME);
        $this->assertEquals(SocialInteractionsVariableCategory::NAME, $v->variableCategoryName);
    }
}
