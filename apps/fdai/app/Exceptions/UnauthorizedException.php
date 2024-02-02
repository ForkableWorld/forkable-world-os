<?php
namespace App\Exceptions;
use App\Slim\View\Request\QMRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
class UnauthorizedException extends AuthorizationException implements HttpExceptionInterface {
    /**
     * UnauthorizedException constructor.
     * @param string $message
     */
    public function __construct(string $message = "Unauthorized"){
        parent::__construct($message, QMException::CODE_UNAUTHORIZED);
    }

    public function getStatusCode(): int{
        return $this->getCode();
    }
    public function getHeaders(): array{
        return QMRequest::headers();
    }
}
