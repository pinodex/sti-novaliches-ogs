<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Extensions\Importer\ImporterInterface;
use App\Extensions\Parser\AbstractParser;
use App\Jobs\Job;

class ImportJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var AbstractParser Parser instance
     */
    protected $parserClass;

    /**
     * @var string Importer class FQDN
     */
    protected $importerClass;

    /**
     * @var array Importer params
     */
    protected $importerParams;

    /**
     * @var string Path to spreadsheet file
     */
    protected $filePath;

    /**
     * @var array Sheets to import
     */
    protected $sheets;

    /**
     * Create a new job instance.
     * 
     * @param AbstractParser $parser Parser instance
     * @param string $importerClass Importer class FQDN
     * @param array $sheets Sheets to import
     * @param array $importerParams Importer params
     */
    public function __construct($parserClass, $importerClass, $filePath, array $sheets, array $importerParams = array())
    {
        $this->parserClass = $parserClass;
        $this->importerClass = $importerClass;
        $this->importerParams = $importerParams;
        $this->filePath = $filePath;
        $this->sheets = $sheets;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $parser = call_user_func_array([$this->parserClass, 'parse'], [$this->filePath]);
        $contents = $parser->getSheetsContent($this->sheets);

        call_user_func_array([$this->importerClass, 'import'], array_merge([$contents], $this->importerParams));
    }
}
