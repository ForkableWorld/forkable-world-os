<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\States\LoginStateButton;
use App\Traits\HasBaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use LogicException;
use App\Slim\Model\Notifications\QMDeviceToken;
use Throwable;
class InvalidDeviceTokenException extends LogicException implements ProvidesSolution {
    use HasBaseSolution;
    /**
     * PushException constructor.
     * @param string $message
     * @param QMDeviceToken $deviceToken
     * @param Throwable|null $previous
     */
    public function __construct(string $message, QMDeviceToken $deviceToken, Throwable $previous = null){
        $message .= "for device token: $deviceToken";
        $deviceToken->setErrorMessage(QMDeviceToken::ERROR_RESPONSE_INVALID_REGISTRATION." ".$message);
        parent::__construct($message, 500, $previous);
    }
    public function getSolutionTitle(): string{
        return "Save a Fresh One";
    }
    public function getSolutionDescription(): string{
        return "Save a fresh device token to Firebase by logging in as mike again and going to inbox.";
    }
    public function getDocumentationLinks(): array{
        return [
            "Log In Again" => LoginStateButton::generateDevUrl(['logout' => 1])
        ];
    }
}
