<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\User;
use App\Solutions\DeleteRemindersSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class UserNotFoundException extends \Exception implements ProvidesSolution
{
    private int $userId;
    /**
     * UserNotFoundException constructor.
     */
    public function __construct(int $id){
        $this->userId = $id;
        $u = User::withTrashed()->find($id);
        if($u){
            parent::__construct("This user with id $id was deleted: $u", );
        } else {
            parent::__construct("No user with id $id was found in database, deleted or otherwise!", );
        }
    }
    public function getSolution(): Solution{
        return new DeleteRemindersSolution($this->getUserId());
    }
    /**
     * @return int
     */
    public function getUserId(): ?int{
        return $this->userId;
    }
}
