<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
use App\DataSources\SpreadsheetImporters\GeneralSpreadsheetImporter;
use App\DataSources\SpreadsheetImportRequest;
use Throwable;
class UnrecognizedSpreadsheetFormatException extends Exception{
    /**
     * UnrecognizedSpreadsheetFormatException constructor.
     * @param $importRequest
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(SpreadsheetImportRequest $importRequest, $code = 400, Throwable $previous = null){
        $message = "Could not identify the format of the submitted spreadsheet.  " .
            "Please convert to the following format or email help@curedao.org to create a custom converter for you.  ".
            "The current standard format is as follows: ".GeneralSpreadsheetImporter::LONG_DESCRIPTION;
        $importRequest->setStatusError($message);
        parent::__construct($message, $code, $previous);
    }
}
