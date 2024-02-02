<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\SlimTests\Variables;
use App\Exceptions\UserVariableNotFoundException;
use App\Storage\Memory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\NutrientsCommonVariables\MagnesiumCommonVariable;
use App\Variables\CommonVariables\SoftwareCommonVariables\AppUsageCommonVariable;
use App\Variables\CommonVariables\SymptomsCommonVariables\BackPainCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\FiveHtpCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
class UserTagsTest extends \Tests\SlimTests\SlimTestCase
{
    /**
     * @return QMUserVariable
     */
    private function getAppUsageQMUserVariable(): QMUserVariable{
        $userTagVariable = QMUserVariable::getOrCreateById(1,AppUsageCommonVariable::ID);
        return $userTagVariable;
    }
    /**
     * @return QMUserVariable
     */
    private function get5HtpQMUserVariable(): QMUserVariable{
        return QMUserVariable::getOrCreateById(1,FiveHtpCommonVariable::ID);
    }
    public function getVariablesMatchingSearchTermThatAreEligibleToBeTagsOfTaggedVariableId(){
        $this->setAuthenticatedUser(1);
        $potentialTagVariablesForBmiMatchingSearchTerm = $this->searchForEligibleTagUserVariables
        ($this->getAppUsageQMUserVariable()->getVariableName(), $this->get5HtpQMUserVariable()->getVariableIdAttribute());
        $this->assertCount(1, $potentialTagVariablesForBmiMatchingSearchTerm);
    }
	private function searchForEligibleTagUserVariables(string $q, int $variableIdToBeTagged){
		return $this->searchVariables($q, ['userTaggedVariableId' => $variableIdToBeTagged]);
	}
    public function getVariablesMatchingSearchTermThatAreEligibleToBeTaggedByTagVariableId(){
        $this->setAuthenticatedUser(1);
        $gottenVariables = $this->searchVariables($this->get5HtpQMUserVariable()->getVariableName(),
            ['userTagVariableId' => $this->getAppUsageQMUserVariable()->getVariableIdAttribute()]);
        $this->assertCount(1, $gottenVariables);
    }
    public function testAddMagnesiumAsIngredientOfBupropion(){
        $this->setAuthenticatedUser(1);
		$mag = $this->getMagnesiumQMUserVariable();
		$bupropion = $this->getBupropionQMUserVariable();
	    $potentialTagVariablesForBmiMatchingSearchTerm = $this->searchForEligibleIngredients(
			$mag->getVariableName(),
				$bupropion->getVariableId());
	    $this->assertCount(2, $potentialTagVariablesForBmiMatchingSearchTerm);
        $this->assertQueryCountLessThan(16);
        $this->getVariablesMatchingSearchTermThatAreEligibleToBeTaggedByTagVariableId();
        $this->assertQueryCountLessThan(43);
        $conversionFactor = 10;
        $response = $this->postApiV3('userTags',
            [
                'userTagVariableId'    => $this->getMagnesiumQMUserVariable()->getVariableIdAttribute(),
                'userTaggedVariableId' => $this->getBupropionQMUserVariable()->getVariableIdAttribute(),
                'conversionFactor'     => $conversionFactor
            ]);
        $this->assertQueryCountLessThan(72);
        $response = json_decode($response->getBody(), true);
        $this->assertNotNull($response['data']['userTagVariable']);
        $this->assertNotNull($response['data']['userTaggedVariable']);
        $this->makeSureWeGetMagnesiumBupropionTagFromApi();
        $this->makeSureMagnesiumDoesNotComeInEligibleTagVariableSearchForBupropionAnymore();
        $this->deleteTag();
        $this->getVariablesMatchingSearchTermThatAreEligibleToBeTagsOfTaggedVariableId();
        $this->getVariablesMatchingSearchTermThatAreEligibleToBeTaggedByTagVariableId();
        $this->assertQueryCountLessThan(149);
    }
    private function makeSureWeGetMagnesiumBupropionTagFromApi(){
        $conversionFactor = 10;
        $gottenTags = $this->getUserTags();
        $this->assertCount(1, $gottenTags, "We should have gotten 1 user tag!");
        $gottenTag = $gottenTags[0];
        $this->assertEquals($this->getMagnesiumQMUserVariable()->getVariableIdAttribute(), $gottenTag->userTagVariableId);
        $this->assertEquals($this->getBupropionQMUserVariable()->getVariableIdAttribute(), $gottenTag->userTaggedVariableId);
        $this->assertEquals($conversionFactor, $gottenTag->conversionFactor);
    }
    private function makeSureMagnesiumDoesNotComeInEligibleTagVariableSearchForBupropionAnymore(){
        $this->setAuthenticatedUser(1);
        $gottenVariables = $this->searchForEligibleTagUserVariables($this->getMagnesiumQMUserVariable()->getVariableName(),
            $this->getBupropionQMUserVariable()->getVariableIdAttribute());
        $this->assertCount(0, $gottenVariables, "We should not get any eligible tag variables");
        $this->setAuthenticatedUser(1);
        $gottenVariables = $this->searchVariables($this->getBupropionQMUserVariable()->getVariableName(),
            ['userTagVariableId' => $this->getMagnesiumQMUserVariable()->getVariableIdAttribute()]);
        $this->assertCount(0, $gottenVariables);
    }
    private function deleteTag(){
        $postData = [
            'userTagVariableId'    => $this->getMagnesiumQMUserVariable()->getVariableIdAttribute(),
            'userTaggedVariableId' => $this->getBupropionQMUserVariable()->getVariableIdAttribute(),
        ];
        $this->postApiV3('userTags/delete', json_encode($postData), 204);
        $gottenTags = $this->getUserTags();
        $this->assertCount(0, $gottenTags);
    }
    protected function getUserTags(array $params = []){
        $this->setAuthenticatedUser(1);
        return $this->getApiV3('userTags', $params);
    }
    /**
     * @return QMUserVariable
     */
    private function getMagnesiumQMUserVariable(): QMUserVariable{
        return QMUserVariable::getOrCreateById(1, MagnesiumCommonVariable::ID);
    }
    /**
     * @return QMUserVariable
     */
    private function getBupropionQMUserVariable(): QMUserVariable{
        return QMUserVariable::getOrCreateById(1,BupropionSrCommonVariable::ID);
    }
    public function testJoinMoodAndBackPain(){
	    $userId = 1;
	    $this->setAuthenticatedUser($userId);
        $currentVariableId = BackPainCommonVariable::ID; // Body Mass Index Or BMI
        $currentVariableName = BackPainCommonVariable::NAME;
        $joinedUserTagVariableId = OverallMoodCommonVariable::ID; // App Usage
        $joinedUserTagVariableName = OverallMoodCommonVariable::NAME;
		$uv = $this->getBackPainUserVariable();
	    $this->createMeasurement(BackPainCommonVariable::NAME, 1);
	    $this->assertUserVariables($userId, array (
		    0 => 'Back Pain',
		    1 => 'Bupropion Sr',
		    2 => 'Overall Mood',
	    ));
	    $conversionFactor = 1;
        $this->verifyNoTags($joinedUserTagVariableName, $currentVariableId);
        $this->verifyNoTags($currentVariableName, $joinedUserTagVariableId);
	    $gottenVariables = $this->searchForJoinableVariables( BackPainCommonVariable::NAME, BackPainCommonVariable::ID);
	    $this->assertNames([], $gottenVariables, "We should not get any joinable variables when searching for the current variable name.");
	    $gottenVariables = $this->searchForJoinableVariables($joinedUserTagVariableName, $joinedUserTagVariableId);
	    $this->assertNames([], $gottenVariables);
        $response = $this->slimPost('api/v3/variables/join', [
            'joinedUserTagVariableId' => $joinedUserTagVariableId,
            'currentVariableId'       => $currentVariableId,
            'conversionFactor'        => $conversionFactor
        ]);
        $response = json_decode($response->getBody(), true);
        $this->assertNotNull($response['data']['joinedUserTagVariable']);
        $this->assertNotNull($response['data']['currentVariable']);
        $this->checkJoinedVariable($joinedUserTagVariableName);
        $this->checkJoinedVariable($currentVariableName);
        $gottenVariables = $this->searchForJoinableVariables($joinedUserTagVariableName, $currentVariableId);
        $this->assertCount(0, $gottenVariables);
        $gottenVariables = $this->searchForJoinableVariables($currentVariableName, $joinedUserTagVariableId);
        $this->assertCount(0, $gottenVariables);
        $this->setAuthenticatedUser($userId);
        // delete tag
        $this->slimPost('api/v3/variables/join/delete', [
            'joinedUserTagVariableId' => $joinedUserTagVariableId,
            'currentVariableId'       => $currentVariableId,
        ], false,204);
        $gottenTags = $this->getUserTags();
        $this->assertCount(0, $gottenTags);
        $gottenVariables = $this->searchForJoinableVariables($joinedUserTagVariableName, $currentVariableId);
        $this->assertCount(1, $gottenVariables);
        $gottenVariables = $this->searchForJoinableVariables($currentVariableName, $joinedUserTagVariableId);
        $this->assertCount(1, $gottenVariables);
        $gottenVariables = $this->searchVariables($joinedUserTagVariableName, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($gottenVariables[0]);
        $gottenVariables = $this->searchVariables($currentVariableName, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($gottenVariables[0]);
    }
    public function testPostUserParent(){
        $this->setAuthenticatedUser(1);
        $childUserTagVariableId = 1919; // Body Mass Index Or BMI
        $childVariableName = BackPainCommonVariable::NAME;
        $parentUserTagVariableId = 1398; // App Usage
        $parentVariableName = OverallMoodCommonVariable::NAME;
        $gottenVariables = $this->searchVariables($parentVariableName, ['childUserTagVariableId' => $childUserTagVariableId]);
        $v = $gottenVariables[0];
        $v = QMUserVariable::getByNameOrId(1, $v->variableId, ['includeTags' => true]);
        $this->assertCount(1, $gottenVariables);
        $this->assertCount(0, $v->childUserTagVariables);
        $this->assertCount(0, $v->parentUserTagVariables);
        $this->assertCount(0, $v->joinedUserTagVariables);
        $this->assertCount(0, $v->parentUserTagVariables);
        $this->assertCount(0, $v->childUserTagVariables);
        $this->assertCount(0, $v->ingredientOfUserTagVariables);
        $this->assertCount(0, $v->ingredientUserTagVariables);
        $gottenVariables = $this->searchVariables($childVariableName, ['parentUserTagVariableId' => $parentUserTagVariableId]);
        $this->assertCount(1, $gottenVariables);
        $v = $gottenVariables[0];
        $v = QMUserVariable::getByNameOrId(1, $v->variableId, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($v);
        $gottenVariables = $this->searchVariables($childVariableName, ['childUserTagVariableId' => $childUserTagVariableId]);
        $this->assertCount(0, $gottenVariables);
        $gottenVariables = $this->searchVariables($parentVariableName, ['parentUserTagVariableId' => $parentUserTagVariableId]);
        $this->assertCount(0, $gottenVariables);
        $postedParents = [
            'parentUserTagVariableId' => $parentUserTagVariableId,
            'childUserTagVariableId'  => $childUserTagVariableId
        ];
        $response = $this->postAndGetDecodedBody('/api/v1/userTags/parent', $postedParents);
        $data = $response->data;
        $this->assertNotNull($data->childUserTagVariable);
        $this->assertNotNull($data->parentUserTagVariable);
        $gottenParents = $this->getUserTags();
        $this->assertCount(1, $gottenParents);
        $gottenParent = $gottenParents[0];
        $this->assertEquals($parentUserTagVariableId, $gottenParent->userTagVariableId);
        $this->assertEquals($childUserTagVariableId, $gottenParent->userTaggedVariableId);
        $this->assertEquals(1, $gottenParent->conversionFactor);
        $gottenVariables = $this->searchVariables($parentVariableName, ['includeTags' => true]);
        $v = $gottenVariables[0];
        $this->assertCount(1, $v->userTaggedVariables);
        $this->assertCount(0, $v->userTagVariables);
        $this->assertCount(0, $v->joinedUserTagVariables);
        $this->assertCount(0, $v->parentUserTagVariables);
        $this->assertCount(1, $v->childUserTagVariables);
        $this->assertCount(0, $v->ingredientOfUserTagVariables);
        $this->assertCount(0, $v->ingredientUserTagVariables);
        $gottenVariables = $this->searchVariables($childVariableName, ['includeTags' => true]);
        $v = $gottenVariables[0];
        $this->assertCount(0, $v->userTaggedVariables);
        $this->assertCount(1, $v->userTagVariables);
        $this->assertCount(0, $v->joinedUserTagVariables);
        $this->assertCount(1, $v->parentUserTagVariables);
        $this->assertCount(0, $v->childUserTagVariables);
        $this->assertCount(0, $v->ingredientOfUserTagVariables);
        $this->assertCount(0, $v->ingredientUserTagVariables);
        $gottenVariables = $this->searchVariables($parentVariableName, ['childUserTagVariableId' => $childUserTagVariableId]);
        $this->assertCount(0, $gottenVariables);
        $gottenVariables = $this->searchVariables($childVariableName, ['parentUserTagVariableId' => $parentUserTagVariableId]);
        $this->assertCount(0, $gottenVariables);
        $gottenParents = $this->getUserTags();
        // delete parent
        $postData = [
            'parentUserTagVariableId' => $parentUserTagVariableId,
            'childUserTagVariableId'  => $childUserTagVariableId,
        ];
        $this->postApiV3('userTags/parent/delete', json_encode($postData), 204);
        $gottenParents = $this->getUserTags();
        $this->assertCount(0, $gottenParents);
        $gottenVariables = $this->searchVariables($parentVariableName, ['childUserTagVariableId' => $childUserTagVariableId]);
        $this->assertCount(1, $gottenVariables);
        $gottenVariables = $this->searchVariables($childVariableName, ['parentUserTagVariableId' => $parentUserTagVariableId]);
        $this->assertCount(1, $gottenVariables);
        $gottenVariables = $this->searchVariables($parentVariableName, ['includeTags' => true]);
        $v = $gottenVariables[0];
        $this->verifyNoTagsOnVariable($v);
        $gottenVariables = $this->searchVariables($childVariableName, ['includeTags' => true]);
        $v = $gottenVariables[0];
        $this->verifyNoTagsOnVariable($v);
    }
    /**
     * @param string $currentVariableName
     */
    private function checkJoinedVariable(string $currentVariableName){
        $gottenVariables = $this->searchVariables($currentVariableName, ['includeTags' => true]);
        $v = $gottenVariables[0];
        $this->assertCount(0, $v->ingredientOfUserTagVariables);
        $this->assertCount(0, $v->ingredientUserTagVariables);
        $this->assertCount(1, $v->joinedUserTagVariables);
        $this->assertCount(1, $v->userTagVariables);
        $this->assertCount(1, $v->userTaggedVariables);
        $this->assertCount(0, $v->childUserTagVariables);
        $this->assertCount(0, $v->parentUserTagVariables);
    }
    /**
     * @param string $joinedUserTagVariableName
     * @param int $currentVariableId
     * @throws UserVariableNotFoundException
     */
    private function verifyNoTags(string $joinedUserTagVariableName, int $currentVariableId){
	    $gottenVariables = $this->searchForJoinableVariables($joinedUserTagVariableName, $currentVariableId);
        $this->assertCount(1, $gottenVariables);
        $variable = QMUserVariable::getByNameOrId(1, $gottenVariables[0]->id, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($variable);
    }
    /**
     * @param QMUserVariable|object $v
     */
    private function verifyNoTagsOnVariable($v): void{
        $this->assertCount(0, $v->ingredientOfUserTagVariables);
        $this->assertCount(0, $v->ingredientUserTagVariables);
        $this->assertCount(0, $v->joinedUserTagVariables);
        $this->assertCount(0, $v->ingredientUserTagVariables);
        $this->assertCount(0, $v->ingredientOfUserTagVariables);
        $this->assertCount(0, $v->childUserTagVariables);
        $this->assertCount(0, $v->parentUserTagVariables);
    }
    protected function deleteIngredientTagAndCheckSearchResults(): void{
        $ingredient = $this->getMagnesiumQMUserVariable();
        $ingredientOf = $this->getBupropionQMUserVariable();
        $this->postAndGetDecodedBody('api/v3/userTags/ingredient/delete', [ // delete tag
            'ingredientUserTagVariableId'   => $ingredient->getVariableIdAttribute(),
            'ingredientOfUserTagVariableId' => $ingredientOf->getVariableIdAttribute(),
        ], false, 204);
        $gottenTags = $this->getUserTags();
        $this->assertCount(0, $gottenTags);
        $this->getEligibleIngredients($ingredient, $ingredientOf, 1);
        $eligibleIngredients =
            $this->searchVariables(
                $ingredientOf->getVariableName(),
                ['ingredientUserTagVariableId' => $ingredient->getVariableIdAttribute()]);
        $this->assertCount(1, $eligibleIngredients);
        $eligibleIngredients =
            $this->searchVariables(
                $ingredient->getVariableName(),
                ['includeTags' => true]);
        $eligibleIngredientVariable = $eligibleIngredients[0];
        $eligibleIngredientVariable =
            QMUserVariable::getByNameOrId(1, $eligibleIngredientVariable->variableId, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($eligibleIngredientVariable);
        $eligibleIngredients =
            $this->searchVariables(
                $ingredientOf->getVariableName(),
                ['includeTags' => true]);
        $eligibleIngredientVariable = $eligibleIngredients[0];
        $eligibleIngredientVariable =
            QMUserVariable::getByNameOrId(1, $eligibleIngredientVariable->variableId, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($eligibleIngredientVariable);
    }
    /**
     * @throws UserVariableNotFoundException
     */
    protected function createIngredientTagAndCheckSearchResults(): void{
        $conversionFactor = 10;
        $ingredient = $this->getMagnesiumQMUserVariable();
        $ingredientOf = $this->getBupropionQMUserVariable();
        $this->setAuthenticatedUser(1);
        $response = $this->postAndGetDecodedBody('/api/v1/userTags/ingredient', [
            'ingredientUserTagVariableId' => $ingredient->getVariableIdAttribute(),
            'ingredientOfUserTagVariableId' => $ingredientOf->getVariableIdAttribute(),
            'conversionFactor' => $conversionFactor
        ]);
        $d = $response->data;
        $this->assertNotNull($d->ingredientUserTagVariable);
        $this->assertNotNull($d->ingredientOfUserTagVariable);
        $gottenTags = $this->getUserTags();
        $this->assertCount(1, $gottenTags);
        $gottenTag = $gottenTags[0];
        $this->assertEquals($ingredient->getVariableIdAttribute(), $gottenTag->userTagVariableId);
        $this->assertEquals($ingredientOf->getVariableIdAttribute(), $gottenTag->userTaggedVariableId);
        $this->assertEquals($conversionFactor, $gottenTag->conversionFactor);
        $response = $this->searchVariables($ingredient->getVariableName(),
                ['includeTags' => true]);
        $eligibleIngredientVariable = $response[0];
        $eligibleIngredientVariable = QMUserVariable::getByNameOrId(1, $eligibleIngredientVariable->variableId, ['includeTags' => true]);
        $this->assertCount(1, $eligibleIngredientVariable->ingredientOfUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->ingredientUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->joinedUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->ingredientUserTagVariables);
        $this->assertCount(1, $eligibleIngredientVariable->ingredientOfUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->childUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->parentUserTagVariables);
        $eligibleIngredients = $this->searchVariables(
            $ingredientOf->getVariableName(),
            ['includeTags' => true]);
        $eligibleIngredientVariable = $eligibleIngredients[0];
        $eligibleIngredientVariable =
            QMUserVariable::getByNameOrId(1, $eligibleIngredientVariable->variableId, ['includeTags' => true]);
        $this->assertCount(0, $eligibleIngredientVariable->ingredientOfUserTagVariables);
        $this->assertCount(1, $eligibleIngredientVariable->ingredientUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->joinedUserTagVariables);
        $this->assertCount(1, $eligibleIngredientVariable->ingredientUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->ingredientOfUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->childUserTagVariables);
        $this->assertCount(0, $eligibleIngredientVariable->parentUserTagVariables);
        $this->getEligibleIngredients($ingredient, $ingredientOf, 0);
        $eligibleIngredients = $this->searchVariables($ingredientOf->getVariableName(),
            ['ingredientUserTagVariableId' => $ingredient->getVariableIdAttribute()]);
        $this->assertCount(0, $eligibleIngredients);
    }
    /**
     * @param QMUserVariable $ingredient
     * @param QMUserVariable $ingredientOf
     * @param int $expectedCount
     * @return object[]|QMUserVariable[]|QMVariable[]
     */
    protected function getEligibleIngredients(QMUserVariable $ingredient, QMUserVariable $ingredientOf, int $expectedCount): array {
        Memory::flush();
        $eligibleIngredients = QMUserVariable::getUserVariables(1, [
            'ingredientOfUserTagVariableId' => $ingredientOf->getVariableIdAttribute(),
            'searchPhrase'                  => $ingredient->getVariableName()
        ]);
        $this->assertCount($expectedCount, $eligibleIngredients);
        $eligibleIngredients = $this->searchForEligibleIngredients($ingredient->getVariableName(),
                                                                   $ingredientOf->getVariableIdAttribute());
        $this->assertCount($expectedCount, $eligibleIngredients);
        return $eligibleIngredients;
    }
    protected function checkIngredientSearchResultsBeforeCreatingTag(): void{
        $magnesiumQMUserVariable = $this->getMagnesiumQMUserVariable();
        $bupropionQMUserVariable = $this->getBupropionQMUserVariable();
	    $eligibleIngredientsOfBupropion = QMUserVariable::getUserVariables(1, [
		    'ingredientOfUserTagVariableId' => $bupropionQMUserVariable->getVariableIdAttribute(),
		    'searchPhrase'                  => $magnesiumQMUserVariable->getVariableName()
	    ]);
	    $this->assertCount(1, $eligibleIngredientsOfBupropion);
	    $eligibleIngredientsOfBupropion = $this->searchVariables($magnesiumQMUserVariable->getVariableName(),
	                                                  ['ingredientOfUserTagVariableId' => $bupropionQMUserVariable->getVariableIdAttribute()]);
	    $this->assertCount(2, $eligibleIngredientsOfBupropion);
        $eligibleIngredientVariable = $eligibleIngredientsOfBupropion[0];
        $eligibleIngredientVariable =
            QMUserVariable::getByNameOrId(1, $eligibleIngredientVariable->variableId, ['includeTags' => true]);
        $this->assertCount(2, $eligibleIngredientsOfBupropion);
        $this->verifyNoTagsOnVariable($eligibleIngredientVariable);
        $eligibleIngredientsOfBupropion = $this->searchVariables($bupropionQMUserVariable->getVariableName(),
            ['ingredientUserTagVariableId' => $magnesiumQMUserVariable->getVariableIdAttribute()]);
        $this->assertCount(1, $eligibleIngredientsOfBupropion);
        $eligibleIngredientVariable = $eligibleIngredientsOfBupropion[0];
        $eligibleIngredientVariable =
            QMUserVariable::getByNameOrId(1, $eligibleIngredientVariable->variableId, ['includeTags' => true]);
        $this->verifyNoTagsOnVariable($eligibleIngredientVariable);
        $eligibleIngredientsOfBupropion = $this->searchForEligibleIngredients(
            $bupropionQMUserVariable->getVariableName(),
            $bupropionQMUserVariable->getVariableIdAttribute());
        $this->assertCount(0, $eligibleIngredientsOfBupropion);
        $eligibleIngredientsOfMg = $this->searchForEligibleIngredientContainers(
            $magnesiumQMUserVariable->getVariableName(),
            $magnesiumQMUserVariable->getVariableIdAttribute());
        $this->assertCount(0, $eligibleIngredientsOfBupropion);
    }
	/**
	 * @param string $currentVariableName
	 * @param int $currentVariableId
	 * @return QMVariable[]|object[]
	 */
	public function searchForJoinableVariables(string $query, int $currentVariableId): array{
		$gottenVariables = $this->searchVariables($query, ['joinVariableId' => $currentVariableId]);
		return $gottenVariables;
	}
	private function searchForEligibleIngredients(string $q, int $variableId){
		$r = $this->slimGet('/api/v1/ingredients', [
			'q' => $q,
			'variable_id' => $variableId
		], 200);
		return json_decode($r->getBody());
	}
	private function searchForEligibleIngredientContainers(string $q, int $variableId){
		$r = $this->slimGet('/api/v1/ingredientContainers', [
			'q' => $q,
			'variable_id' => $variableId
		], 200);
		return json_decode($r->getBody());
	}
}
