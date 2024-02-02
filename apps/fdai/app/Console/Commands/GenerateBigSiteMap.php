<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

//USAGE: `php artisan generateSitemap`
//DEBUG: `./cli_debug.sh laravel/artisan generateSitemap`
namespace App\Console\Commands;
use App\Services\CorrelationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Log;
class GenerateBigSiteMap extends Command {
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'generateSitemap {loop=ETERNAL}';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Sends predictor emails to users';
    /**
     * Execute the console command.
     * @param CorrelationService $correlationService
     * @return mixed
     * @internal param EmailService $email
     * @internal param StripeService $stripe
     */
    public function handle(CorrelationService $correlationService){
        // create new sitemap object
        $sitemap = App::make("sitemap");
        // get all products from db (or wherever you store them)
        //$user_variable_relationships = DB::table('global_variable_relationships')->orderBy('id')->get();
        $filters['offset'] = 0;
        $filters['limit'] = 200;
        $correlations = [];
        $keepGoing = true;
        while($keepGoing){
            $newCorrelations = $correlationService->getAggregatedCorrelations($filters);
            /** @var \QuantiModo\Client\Model\Correlation[] $correlations */
            $correlations = array_merge($correlations, $newCorrelations);
            if(count($newCorrelations) < $filters['limit']){
                $keepGoing = false;
            }
            $filters['offset'] = $filters['offset'] + $filters['limit'];
            Log::info(__METHOD__." Got ".count($correlations)." user_variable_relationships");
            $keepGoing = false;
        }
        // counters
        $counter = 0;
        $sitemapCounter = 0;
        // add every product to multiple sitemaps with one sitemapindex
        foreach($correlations as $p){
            if($counter == 50000){
                Log::info(__METHOD__." Added 50k items to sitemap");
                // generate new sitemap file
                $sitemap->store('xml', 'sitemap-'.$sitemapCounter);
                // add the file to the sitemaps array
                $sitemap->addSitemap(secure_url('sitemap-'.$sitemapCounter.'.xml'));
                // reset items array (clear memory)
                $sitemap->model->resetItems();
                // reset the counter
                $counter = 0;
                // count generated sitemap
                $sitemapCounter++;
            }
            $lastModified = strtotime($p->getTimestamp());
            if(!$lastModified){
                $lastModified = date("c");
            }
            $priority = abs($p->getCorrelationCoefficient()) * $p->getStatisticalSignificance();
            $frequency = 'daily';
            $images = [
                [
                    'url'          => $p->getGaugeImage(),
                    'title'        => $p->getPredictorExplanation(),
                    'caption'      => $p->getStudyAbstract(),
                    'geo_location' => null
                ]
            ];
            $title = $p->getPredictorExplanation();
            // add product to items array
            $sitemap->add($p->getStudyLinkStatic(), $lastModified, $priority, $frequency, $images, $title);
            // count number of elements
            $counter++;
        }
        // you need to check for unused items
        if(!empty($sitemap->model->getItems())){
            // generate sitemap with last items
            $sitemap->store('xml', 'sitemap-'.$sitemapCounter);
            // add sitemap to sitemaps array
            $sitemap->addSitemap(secure_url('sitemap-'.$sitemapCounter.'.xml'));
            // reset items array
            $sitemap->model->resetItems();
        }
        // generate new sitemapindex that will contain all generated sitemaps above
        $sitemap->store('sitemapindex', 'sitemap');
    }
}
