<?php
namespace Tests\UnitTests\Utils;
use App\Models\Variable;
use App\Storage\QueryBuilderHelper;
use App\Utils\Constraint;
use Tests\UnitTestCase;

class ConstraintTest extends UnitTestCase
{
    public function testHumanizeWhereClause(){
        $c = new Constraint( 'internal_error_message',  'user_variable_relationships', 'not null', '=');
        $this->assertEquals("that have a Internal Error Message", $c->humanize());
    }
    public function testNameSearchConstraint(){
        $query = Variable::with([
            'category',
            'defaultUnit',
            'mostCommonUnit'
        ]);
        QueryBuilderHelper::addParams($query->getQuery(), ['name'=>'%moo%']);
        $variables = $query->get();
        $this->assertGreaterThan(0, $variables->count());
    }
}
