<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\UpcItemDb;
use App\Models\Variable;
use App\Scrapers\BaseScraper;
use App\DataSources\QMConnector;
use App\Slim\Model\User\QMUser;
use App\VariableCategories\FoodsVariableCategory;
class UpcItemDbScraper extends BaseScraper {
    public function getUserLogin(): string{
        return QMUser::population()->getLoginName();
    }
    public function getBaseUrl(): string{
        return 'https://api.upcitemdb.com/prod/v1/lookup';
    }
    public function getPathsClasses(): array{
        // TODO: Implement getPathsClasses() method.
    }
    public function getScraperVariableData(): array{
        return [
            Variable::FIELD_VARIABLE_CATEGORY_ID => FoodsVariableCategory::ID
        ];
    }
    public function getResponseType(): string{
        return QMConnector::RESPONSE_TYPE_JSON;
    }
    public static function searchByUpc($upc){
        $user_key = 'only_for_dev_or_pro';
        $endpoint = 'https://api.upcitemdb.com/prod/v1/lookup';
        $ch = curl_init();
        /* if your client is old and doesn't have our CA certs
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);*/
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "user_key: $user_key",
            "key_type: 3scale"
        ]);

        // HTTP GET
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $endpoint.'?upc='.$upc);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200){
            le("$httpcode ".\App\Logging\QMLog::print_r($response, true));
        }
        /* if you need to run more queries, do them in the same connection.
         * use rawurlencode() instead of URLEncode(), if you set search string
         * as url query param
         * for search requests, change to sleep(6)
         */
        sleep(2);
        // proceed with other queries
        curl_close($ch);
        return $response;
    }

    public function getBaseApiUrl(): string
    {
       return $this->getBaseUrl();
    }

    public function saveConnectorRequestResponse(string $url, $body, array $headers = [], string $method = 'GET', int $code = 200): void
    {
        le("UpcItemDbScraper::saveConnectorRequestResponse() is not implemented");
        // TODO: Implement saveConnectorRequestResponse() method.
    }
}
