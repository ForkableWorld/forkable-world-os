<?php
namespace DataTableServices;
use App\DataTableServices\UserDataTableService;
use Tests\UnitTestCase;

class DataTablesUnitTest extends UnitTestCase
{
    public function testUserDataTable(){
        $this->skipTest("Data lab sucks");
        $datatable = new UserDataTableService();
        $columns = $datatable->getColumns();
        $searchable = collect($columns)->where('searchable')->all();
        $searchable = array_keys($searchable);
        $this->assertArrayEquals([
            0 => 'display_name',
            1 => 'user_url',
            2 => 'user_login',
            3 => 'user_email',
            4 => 'roles',], $searchable);
    }
}
