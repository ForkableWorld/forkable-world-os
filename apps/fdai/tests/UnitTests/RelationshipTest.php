<?php
namespace Tests\UnitTests;
use App\Models\User;
use Tests\UnitTestCase;
class RelationshipTest extends UnitTestCase
{
    public function testCalculateRelationshipCounts(){
        $u = User::mike();
        $res = $u->calculateInterestingNumberOfRelationCounts();
        $this->assertNotEmpty($res);
    }
}
