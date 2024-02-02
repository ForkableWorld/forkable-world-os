<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
class FinancialReport extends AnalyticalReport
{
    /**
     * @inheritDoc
     */
    public function generateBodyHtml(): string{
        $qa = [];
        $qa["Your Name"] = "Mike Sinn";
        $qa["Phone and email.n"] = "m@thinkbynumbers.org 618.391.0002";
        $qa["Company"] = "QuantiModo";
        $qa["General Update"] = $this->getGeneralUpdate();
        $qa["Your Name"] = "Mike Sinn";
    }
    /**
     * @inheritDoc
     */
    protected function getSpreadsheetRows(): array{
        // TODO: Implement getSpreadsheetRows() method.
    }
    /**
     * @inheritDoc
     */
    public function getSourceObject(){
        // TODO: Implement getSourceObject() method.
    }
    /**
     * @inheritDoc
     */
    public function generateEmailBody(): string{
        // TODO: Implement generateEmailHtml() method.
    }
    /**
     * @inheritDoc
     */
    public function getCoverImage(): string{
        // TODO: Implement getCoverImage() method.
    }
    /**
     * @inheritDoc
     */
    public static function getDemoReport(): AnalyticalReport{
        // TODO: Implement getDemoReport() method.
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        // TODO: Implement getCategoryName() method.
    }
    private function getGeneralUpdate(): string {
        return "
        Over the last 3 months, Quantimodo has made great progress in the form of data aggregation, analytics, research publishing, user experience, data validation, analytical reporting, code quality control, technical scaling, and automated code generation. I have implemented integrations with popular content management systems such as OctoberCMSand WordPress  to allow publishing and curation of generated analytical reports (https://studies.quantimo.do). Due to the complexity of the analytics and the computing resource demands, just-in-time analysis has been implemented using a custom analysis prioritization and queueing mechanism. Automated import via scraping of academic grade reporting systems now allows for generation of daily grade reports with immediate feedback and rewards for academic performance (https://static.quantimo.do/demo/daily-average-grade-for-super-dude-grade-report.html).  More guidance for new users has been implemented at https://web.quantimo.do.  Automatic importing of ultraviolet light exposure, pollen exposure, and particulate air pollution is now possible at https://web.quantimo.do/import.  The physician dashboard at https://physician.quantimo.do/ allows physicians to view their patients data in addition to regularly emailed reports. Better data validation and cleaning algorithms have improved the quality of the root cause analysis reports such as https://images.quantimo.do/root-cause-analysis/root-cause-analysis-overall-mood-example.pdf.   Perform predictive analyses using 90 different hyper-parameter combinations to determine durations of action and onset delay for various factors influencing symptom severity.
        ";
    }
}
