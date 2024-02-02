<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Logging\QMLog;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Variable\VariableNumberCommonTaggedByProperty;
use App\Storage\DB\Writable;
use App\VariableCategories\MusicVariableCategory;
use App\Variables\QMCommonTag;
use App\Variables\QMUserVariable;
class TagCleanUpJobTest extends JobTestCase {
	public function testDeleteMusicTags(){
		$v = QMUserVariable::getByNameOrId(230, "Dental Floss");
		$v->analyzeFully(__FUNCTION__);
		$qb = QMCommonTag::readonly()->join(Variable::TABLE, Variable::TABLE.'.'. Variable::FIELD_ID, '=',
		                                    QMCommonTag::TABLE . '.' . QMCommonTag::FIELD_TAG_VARIABLE_ID)->where(Variable::FIELD_VARIABLE_CATEGORY_ID, MusicVariableCategory::ID);
		$rows = $qb->getArray();
		$qb->hardDelete("music");
		QMLog::infoWithoutObfuscation(count($rows)." tags for music");
		$qb = QMCommonTag::readonly()->join(Variable::TABLE, Variable::TABLE.'.'. Variable::FIELD_ID, '=',
		                                    QMCommonTag::TABLE . '.' . QMCommonTag::FIELD_TAGGED_VARIABLE_ID)->where(Variable::FIELD_VARIABLE_CATEGORY_ID, MusicVariableCategory::ID);
		$rows = $qb->getArray();
		$qb->hardDelete("music");
		QMLog::infoWithoutObfuscation(count($rows)." tagged for music");
	}
	/**
	 * @param string $tableToDeleteFrom
	 * @param string $localKey
	 * @param string $relatedTable
	 * @param string $foreignKey
	 */
	public static function deleteWithoutRelatedRecord(string $tableToDeleteFrom, string $localKey, string $relatedTable, string $foreignKey){
		$qb = Writable::db()->table($tableToDeleteFrom)->select([
			                                                        $tableToDeleteFrom . '.' . $localKey . ' as localKey',
			                                                        $relatedTable . '.' . $foreignKey . ' as foreignPrimaryKey',
		                                                        ])->leftJoin($relatedTable, $tableToDeleteFrom.'.'.$localKey, '=', $relatedTable.'.'.$foreignKey)->whereNull($relatedTable.'.'.$foreignKey);
		$rows = $qb->getArray();
		if(count($rows)){
			QMLog::error("Deleting " . count($rows) . " rows from $tableToDeleteFrom...");
		}else{
			QMLog::info("No orphaned records in $tableToDeleteFrom...");
		}
		foreach($rows as $row){
			QMLog::info("Deleting from $tableToDeleteFrom $localKey: $row->localKey, foreignKey: $row->foreignPrimaryKey");
			$foreignRow = Writable::db()->table($tableToDeleteFrom)->where($foreignKey, $row->localKey)->first();
			if($foreignRow){
				le("");
			}
			$result = Writable::getBuilderByTable($tableToDeleteFrom)->where($localKey, $row->localKey)->delete();
		}
	}
	private function updateCommonTagCounts(){
		$this->createNumberOfTagsView();
		CommonVariableCleanupJob::update_number_of_common_tags();
		$this->create_number_common_tagged_by_View();
		VariableNumberCommonTaggedByProperty::update_number_common_tagged_by();
	}
	private function create_number_common_tagged_by_View(){
		Writable::pdoStatement("
            CREATE ALGORITHM=UNDEFINED DEFINER=`quantimodo`@`%` SQL SECURITY DEFINER VIEW
            `" . Variable::FIELD_NUMBER_COMMON_TAGGED_BY . "` AS
            select count(`common_tags`.`tagged_variable_id`) AS `total`,`variables`.`id` AS `id`,`variables`.`name` AS `name`
            from (`common_tags` left join `variables` on((`variables`.`id` = `common_tags`.`tag_variable_id`)))
            group by `common_tags`.`tag_variable_id` order by `total` desc;
        ");
	}
	private function createNumberOfTagsView(){
		Writable::pdoStatement("
            CREATE ALGORITHM=UNDEFINED DEFINER=`quantimodo`@`%` SQL SECURITY DEFINER VIEW
            `" . Variable::FIELD_NUMBER_OF_COMMON_TAGS . "` AS
            select count(`common_tags`.`tag_variable_id`) AS `total`,`variables`.`id` AS `id`,`variables`.`name` AS `name`
            from (`common_tags` left join `variables` on((`variables`.`id` = `common_tags`.`tagged_variable_id`)))
            group by `common_tags`.`tagged_variable_id` order by `total` desc;
        ");
	}
}
