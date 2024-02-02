<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Traits\HasCauseAndEffect;
class StudyVotes {
    public $userVote;
    public float $averageVote;
    /**
     * @param HasCauseAndEffect|QMStudy|null $statistics
     */
    public function __construct($statistics){
        if(isset($statistics->userVote)){
            $this->userVote = $statistics->userVote;
        }
        if(isset($statistics->averageVote)){
            $this->averageVote = $statistics->averageVote;
        }
    }
}
