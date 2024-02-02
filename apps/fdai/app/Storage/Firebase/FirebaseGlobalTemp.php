<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\Firebase;
use App\Types\TimeHelper;
use App\Logging\QMLog;
class FirebaseGlobalTemp extends FirebaseGlobalPermanent
{
    public static function formatKey(string $path, bool $temporary = true): string{
        return parent::formatKey($path, true);
    }
    /**
     * @return array|bool
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function clearTempIfOlderThan24Hours(){
        QMLog::infoWithoutContext('=== '.__FUNCTION__.' ===');
        $lastCleared = FirebaseGlobalPermanent::get("lastCleared");
        if(!$lastCleared){
            QMLog::info("Firebase lastCleared not set so setting to ".time());
            /** @noinspection PhpUnhandledExceptionInspection */
            return FirebaseGlobalPermanent::set("lastCleared", time());
        }
        if($lastCleared > time() - 86400){
            QMLog::info("Firebase lastCleared ".TimeHelper::timeSinceHumanString($lastCleared));
            return false;
        }
        QMLog::info("Firebase lastCleared ".TimeHelper::timeSinceHumanString($lastCleared)." so clearing temp");
        self::deleteTemp();
        /** @noinspection PhpUnhandledExceptionInspection */
        return FirebaseGlobalPermanent::set("lastCleared", time());
    }
    /**
     * @return array|bool
     */
    public static function deleteTemp(){
	    FirebaseGlobalPermanent::delete('App\Repos\QMAPIRepo');
        FirebaseGlobalPermanent::delete('CacheTest');
        FirebaseGlobalPermanent::delete('crypto');
        FirebaseGlobalPermanent::delete('crypto_trades');
        FirebaseGlobalPermanent::delete('deleted_user');
        FirebaseGlobalPermanent::delete('LastEmail');
        FirebaseGlobalPermanent::delete('ohlcvData');
        FirebaseGlobalPermanent::delete('Quantimodo');
	    FirebaseGlobalPermanent::delete('QuantimodoTest');
	    FirebaseGlobalPermanent::delete('html_tests');
        FirebaseGlobalPermanent::delete('Tests');
        FirebaseGlobalPermanent::delete('regression');
        FirebaseGlobalPermanent::delete('SynonymsTest');
        FirebaseGlobalPermanent::delete('testDurations');
	    FirebaseGlobalPermanent::delete('temp');
	    FirebaseGlobalPermanent::delete('testQueryCounts');
        return FirebaseGlobalTemp::delete('');
    }
}
