<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Models\User;
use Moesif\Middleware\MoesifLaravel;
use App\Slim\Middleware\QMAuth;
class QMMoesif
{
    public const URL = "https://www.moesif.com/wrap/app/586:35-617:37/search/events/dates/-7d";
    public function maskRequestHeaders($headers) {
        //$headers['header5'] = '';
        return $headers;
    }

    public function maskRequestBody($body) {
        return $body;
    }

    public function maskResponseHeaders($headers) {
        //$headers['header2'] = 'XXXXXX';
        return $headers;
    }

    public function maskResponseBody($body) {
        return $body;
    }

    public function identifyUserId($request, $response): ?int{
        $id = QMAuth::id();
        return $id;
    }

    public function identifyCompanyId($request, $response){
        return null;
        //return "67890";
    }

    public function identifySessionId($request, $response) {
        if ($request->hasSession()) {
            return $request->session()->getId();
        } else {
            return null;
        }
    }

    public function getMetadata($request, $response): array{
        return GlobalLogMeta::get();
    }

    public function skip($request, $response): bool{
        $myUrl = $request->fullUrl();
        if (strpos($myUrl, '/health') !== false) {
            return true;
        }
        return false;
    }
    /**
     * @param User $u
     */
    public static function setUser(User $u){
        $user = [
            "user_id" => $u->id,
            "created" => $u->getUserRegistered(),
//            "company_id" => "67890", // If set, associate user with a company object
//            "campaign" => array(
//                "utm_source" => "google",
//                "utm_medium" => "cpc",
//                "utm_campaign" => "adwords",
//                "utm_term" => "api+tooling",
//                "utm_content" => "landing"
//            ),
            "metadata" => [
                "email" => $u->email,
                "first_name" => $u->first_name,
                "last_name" => $u->last_name,
//                "title" => "Software Engineer",
//                "sales_info" => array(
//                    "stage" => "Customer",
//                    "lifetime_value" => 24000,
//                    "account_owner" => "mary@contoso.com"
//                )
            ]
        ];

        $middleware = new MoesifLaravel();
        try {
            $middleware->updateUser($user);
        } catch (\Exception $e) {
            le($e);
        }
    }
}
