<?php

namespace Database\Seeders;

use App\Files\Json\JsonFile;
use App\Models\Connector;
use Illuminate\Support\Facades\DB;

class ConnectorsTableSeeder extends AbstractSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
		Connector::deleteAll();
        $data = $this->getData();
        foreach ($data as $connector){
            DB::table('connectors')->insert([$connector]);
        }
//        foreach ($this->getData() as $row) {
//            try {
//                Connector::updateOrCreate(['id' => $row['id']], $row);
//            } catch (\Throwable $e) {
//                $str = \App\Logging\QMLog::print_r($row, true);
//                ConsoleLog::error("Error inserting  in table 'connectors' in ConnectorsTableSeeder: " .
//                    $e->getMessage()."\nrow: ".$str);
//            }
//        }


    }

    /**
     * @return array[]
     */
    protected function getData(): array
    {
        $data = JsonFile::getArray('data/connectors.json');
        foreach ($data as $i => $row) {
            $data[$i]['is_public'] = $row['enabled'];
            $data[$i]['slug'] = $row['name'];
        }
        return $data;
    }
}
