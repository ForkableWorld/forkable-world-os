<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\Models\User;
use App\Models\WpPost;
use App\UI\ImageUrls;
use Illuminate\View\View;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\User\PhysicianUser;
use App\Slim\Model\User\QMUser;
class DescriptiveOverviewReportForPhysician extends AnalyticalReport {
    const OVERVIEW_OF_PATIENT_DATA = "Overview of patient data";
    /** @var PhysicianUser */
    private $physician;
    /**
     * @var QMUser
     */
    private $patient;
    /**
     * DescriptiveStatisticsReport constructor.
     * @param User $patient
     * @param PhysicianUser $physician
     */
    public function __construct(User $patient, PhysicianUser $physician){
        $this->physician = $physician;
        $this->patient = $patient;
        $this->setId($patient->getId());
    }
    /**
     * @return string
     */
    public function getCoverImage(): string{
        return ImageUrls::FACTORS_SLIDE;
    }
    /**
     * @return void
     */
    public function generatePDF(): void {
        $reminders = $this->getPhysician()->getDefaultTrackingReminders();
        if($reminders){
            $this->createReportFromDefaultReminders($reminders);
        }
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->getQMUser()->getDisplayNameAttribute()." Overview";
    }
    /**
     * @return PhysicianUser
     */
    public function getPhysician(): PhysicianUser{
        return $this->physician;
    }
    /**
     * @return QMUser
     */
    public function getPatient(): QMUser{
        return $this->patient;
    }
    /**
     * @return array
     */
    protected function getSpreadsheetRows(): array{
        // TODO: Implement getSpreadsheetRows() method.
    }
    /**
     * @param QMTrackingReminder[] $reminders
     */
    private function createReportFromDefaultReminders(array $reminders): void {
        foreach($reminders as $reminder){
            $v = $reminder->findQMUserVariable($this->getUserId());
            $reminderUnitId = $reminder->getUnitIdAttribute();
            if($reminderUnitId){
                $v->setUserUnit($reminderUnitId);
            }
            $this->addVariableOverview($v);
        }
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string{
        return "This report is an overview of your ".$this->getQMUser()->getDisplayNameAttribute()."'s data.";
    }
    /**
     * @return QMUser
     */
    public function getSourceObject(): QMUser{
        return $this->getQMUser();
    }
    /**
     * @return string
     */
    public function generateBodyHtml(): string {
        throw new \LogicException("Please implement ".__FUNCTION__." for ".$this->getShortClassName());
    }
    /**
     * @inheritDoc
     */
    public function generateEmailBody(): string {
        throw new \LogicException("Please implement ".__FUNCTION__." for ".$this->getShortClassName());
    }
    /**
     * @inheritDoc
     */
    public function getCategoryDescription(): string{
        return self::OVERVIEW_OF_PATIENT_DATA;
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return WpPost::CATEGORY_PATIENT_OVERVIEW_REPORTS;
    }
    static public function getDemoReport(): AnalyticalReport {
        return new self(User::mike(), QMUser::demo()->getPhysicianUser());
    }
    public function getShowContentView(array $params = []): View{
        return $this->getPatient()->getShowContentView($params);
    }
    protected function getShowPageView(array $params = []): View{
        return $this->getPatient()->getShowPageView($params);
    }
}
