<?php /** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\Variables;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Variable\VariableSynonymsProperty;
use App\Variables\CommonVariables\GoalsCommonVariables\CodeCommitsCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class VariableSynonymsPropertyTest extends SlimStagingTestCase
{
    public function testGenerateSynonymsForVariableWithUnitInName(){
        $v = QMCommonVariable::findByNameOrId("Bananas (grams)");
        $synonyms = $v->calculateSynonyms();
        $this->assertArrayEquals(['Bananas','Banana','Bananas (grams)'], $synonyms);
    }
    public function testSpendingSynonyms(){
        $l = Variable::find(5997072);
        $before = $l->getSynonymsAttribute();
        $after = VariableSynonymsProperty::calculate($l);
        $this->assertArrayEquals([
	        0 => 'Spending On Maxell LR03 AAA Cell 36 Pack Box Battery',
	        1 => 'Spending On Maxell LR03 AAA Cell 36 Pack Box Battery (723815)',], $after);
    }
    public function testDecodeSynonymsWithDoubleSlash(){
        $v = Variable::find(6055417);
        $ex = QMLog::var_export($v->getRawAttribute(Variable::FIELD_SYNONYMS), true);
        $original = '"[\\"Spending on Celebrating Your Success\\",\\"Spending on Celebrating the Single Life: Keys to Successful Living on Your Own\\",\\"Spending on Celeb The Single Life Keys To Successful Living On Your Own\\",\\"Spending on Celebrating The Single Life Keys To Successful Living On Your Own\\",\\"Spending on Celebrating Your Succes\\"]"';
        $decoded = VariableSynonymsProperty::decodeOrFallbackToName($original, $v);
        $this->assertArrayEquals([
            0 => 'Spending on Celebrating Your Success',
            1 => 'Spending on Celebrating the Single Life: Keys to Successful Living on Your Own',
            2 => 'Spending on Celeb The Single Life Keys To Successful Living On Your Own',
            3 => 'Spending on Celebrating The Single Life Keys To Successful Living On Your Own',
            4 => 'Spending on Celebrating Your Succes',
	                                 ], $decoded, 
                                 QMLog::var_export($decoded, true));
    }
    public function testDecodeSynonymsWithLotsOfSlashes(){
        $v = Variable::findByNameOrId(
            "EL SIGLO XIV, A TRAVES DE GUILLERMO RUBIO [ENCUADERNADO][EN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO] ] ,],");
        $original = "\"\\\"\\\\\\\"[\\\\\\\\\\\\\\\"By GUILLERMO RUBIO\\\\\\\\\\\\\\\",\\\\\\\\\\\\\\\"EL SIGLO XIV, A TRAVES DE GUILLERMO RUBIO [ENCUADERNADO][EN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO] [Hardcover] [Jan 01, 1952] RUBERT Y CANDAU, J. M.\\\\\\\\\\\\\\\",\\\\\\\\\\\\\\\"EL SIGLO XIV\\\\\\\\\\\\\\\",\\\\\\\\\\\\\\\"EL SIGLO XIV, A TRAVES DE GUILLERMO RUBIO [ENCUADERNADO][EN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO] ] ,],\\\\\\\\\\\\\\\",\\\\\\\\\\\\\\\"EL SIGLO XIV, A TRAVES DE GUILLERMO RUBIO [ENCUADERNADO][EN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO] ] ,]\\\\\\\\\\\\\\\"]\\\\\\\"\\\"\"";
        $actual = VariableSynonymsProperty::decodeOrFallbackToName($original, $v);
        $this->assertArrayEquals(array (
	        0 => 'By GUILLERMO RUBIO',
	        1 => 'EL SIGLO XIV',
	        2 => 'A TRAVES DE GUILLERMO RUBIO ENCUADERNADOEN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO Hardcover Jan 01',
	        3 => '1952 RUBERT Y CANDAU',
	        4 => 'J. M',
	        5 => 'A TRAVES DE GUILLERMO RUBIO ENCUADERNADOEN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO',
        ), $actual);
        $actual = VariableSynonymsProperty::calculate($v);
        // TODO: Clean up this kind of crap
        $this->assertArrayEquals(array (
	                                 0 => 'By GUILLERMO RUBIO',
	                                 1 => 'EL SIGLO XIV',
	                                 2 => 'A TRAVES DE GUILLERMO RUBIO ENCUADERNADOEN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO Hardcover Jan 01',
	                                 3 => '1952 RUBERT Y CANDAU',
	                                 4 => 'J. M',
	                                 5 => 'A TRAVES DE GUILLERMO RUBIO ENCUADERNADOEN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO',
	                                 6 => 'EL SIGLO XIV, A TRAVES DE GUILLERMO RUBIO [ENCUADERNADO][EN PAGINA 3 HAY UNA ANOTACION A BOLIGRAFO] ] ,],',
	                                 7 => 'EL SIGLO XIV, a TRAVES DE GUILLERMO RUBIO [ENCUADERNADO][EN PAGINA 3 HAY UNA ANOTACION a BOLIGRAFO] ] ,]',
                                 ), $actual);
    }
	public function testSetSynonymsAttribute(){
		$v = CodeCommitsCommonVariable::instance()->getVariable();
		$v->id = null;
		$v->synonyms = [];
		$v->name = 'Github Code Commits to mikepsinn/qm-api';
		$expected = array (
			0 => 'Github Code Commits to mikepsinn/qm-api',
			1 => 'Code Commits',
		);
		$calculated = $v->calculateSynonyms();
		$this->assertArrayEquals($expected, $calculated);
		$v->setAttribute(VariableSynonymsProperty::NAME, $calculated);
		$this->assertArrayEquals($calculated, $v->getSynonymsAttribute());
		$p = $v->getPropertyModel(VariableSynonymsProperty::NAME);
		$dbVal = $p->getDBValue();
		$this->assertEquals('["Github Code Commits to mikepsinn\/qm-api","Code Commits"]', $dbVal);
		$p->validate();
	}
}
