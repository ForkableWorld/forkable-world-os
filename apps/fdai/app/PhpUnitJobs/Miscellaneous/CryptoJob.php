<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Miscellaneous;
use App\Files\FileHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Slim\Model\Crypto\BestPerformerStrategy;
use App\Slim\Model\Crypto\BreakoutStrategy;
use App\Slim\Model\Crypto\CryptoModel;
use App\Slim\Model\Crypto\CryptoStrategy;
use App\Slim\Model\Crypto\LeastBadStrategy;
use App\Slim\Model\Crypto\LongestEnduranceStrategy;
use App\Slim\Model\Crypto\OhlcvDataPoint;
use App\Slim\Model\Crypto\TradeParameters;
use App\Slim\Model\Crypto\CryptoTaxes;
use App\Logging\QMLog;
use App\PhpUnitJobs\JobTestCase;
/** Class CryptoJobTest
 * @package App\PhpUnitJobs
 */
class CryptoJob extends JobTestCase {
    const JOB_TYPE_all_tests = 'all_tests';
    const JOB_TYPE_best_performer_execution = 'best_performer_execution';
    const JOB_TYPE_BREAKOUT_TEST = 'breakout_test';
    const JOB_TYPE_ENDURANCE_EXECUTION = 'endurance_execution';
    const JOB_TYPE_ENDURANCE_TEST = 'endurance_test';
    const JOB_TYPE_LEAST_BAD_EXECUTION = 'least_bad_execution';
    const JOB_TYPE_LEAST_BAD_TEST = 'least_bad_test';
    const JOB_TYPE_OHLCV = 'ohlcv';
    const JOB_TYPE_output_test_results = 'output_results';
    const JOB_TYPE_PERFORMANCE_EXECUTION = 'performance_execution';
    const JOB_TYPE_PERFORMANCE_TEST = 'performance_test';
    const JOB_TYPE_TAXES = 'taxes';
    /**
     * @group run
     */
    public function testCrypto(){
        CryptoModel::setCryptoConnectionStrings();
        if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_TAXES){
            QMLog::info("JOB_TYPE_OHLCV");
            $result = CryptoTaxes::getTaxesSpreadsheet();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_OHLCV){
            QMLog::info("JOB_TYPE_OHLCV");
            $result = OhlcvDataPoint::fetchNewOhlcvDataForAll();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_LEAST_BAD_TEST){
            $strategy = new LeastBadStrategy();
            $result = $strategy->backTestForAllParameterCombinations();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_LEAST_BAD_EXECUTION){
            $strategy = new LeastBadStrategy();
            $result = $strategy->executeForAllExchanges();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_ENDURANCE_TEST){
            $strategy = new LongestEnduranceStrategy();
            $result = $strategy->backTestForAllParameterCombinations();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_ENDURANCE_EXECUTION){
            $strategy = new LongestEnduranceStrategy();
            $result = $strategy->executeForAllExchanges();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_PERFORMANCE_TEST){
            $strategy = new BestPerformerStrategy();
            $result = $strategy->backTestForAllParameterCombinations();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_PERFORMANCE_EXECUTION){
            $strategy = new BestPerformerStrategy();
            $result = $strategy->executeForAllExchanges();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_BREAKOUT_TEST){
            $strategy = new BreakoutStrategy();
            $result = $strategy->backTestForAllParameterCombinations();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_all_tests){
            $strategy = new BestPerformerStrategy();
            $strategy->backTestForAllParameterCombinations();
            $strategy = new LeastBadStrategy();
            $strategy->backTestForAllParameterCombinations();
            $strategy = new LongestEnduranceStrategy();
            $strategy->backTestForAllParameterCombinations();
            $strategy = new BreakoutStrategy();
            $result = $strategy->backTestForAllParameterCombinations();
        }else if(\App\Utils\Env::get('CRYPTO_JOB_TYPE') === self::JOB_TYPE_output_test_results){
            $result = TradeParameters::analyzeAllTestResultsFromMongoDb();
        }else{
            throw new \LogicException("Please specify CRYPTO_JOB_TYPE env");
        }
        $this->assertTrue($result);
    }
    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function test5MinBTC(){
        $crypto = new LongestEnduranceStrategy();
        $crypto->setCurrencyPair(CryptoStrategy::BTC_USDT);
        $crypto->setDaysOfTraining(1);
        $relativeDailyReturn = $crypto->regressionAnalysisForSingleCurrencyPairLagAndTimeFrame();
        $this->assertNotNull($relativeDailyReturn);
    }
    public function testGeneratePerformanceByYearMonthSpreadsheet(){
        $data = FileHelper::getDecodedJsonFile('response.json');
        /** @noinspection PhpUndefinedFieldInspection */
        $data = $data->data;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 2;
        $bySymbol = [];
        foreach($data as $symbolData){
            $symbol = $symbolData->base;
            $bySymbol[$symbol] = $symbolData;
            $prices = $symbolData->prices;
            $percents = $prices->latest_price->percent_change;
            $col = 'B';
            $sheet->setCellValue("A".$row, $symbol);
            foreach($percents as $period => $percent){
                $sheet->setCellValue($col."1", $period);
                $sheet->setCellValue($col.$row, $percent);
                $sheet->getStyle($col.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                $col++;
            }
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save(FileHelper::absPath('crypto.xlsx'));
    }
}
