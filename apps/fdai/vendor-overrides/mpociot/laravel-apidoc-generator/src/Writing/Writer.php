<?php

namespace Mpociot\ApiDoc\Writing;

use App\Files\FileFinder;
use App\Utils\QMRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mpociot\ApiDoc\Tools\DocumentationConfig;
use Mpociot\Documentarian\Documentarian;
use App\Files\FileHelper;
use App\Types\QMStr;
class Writer
{
    /**
     * @var Command
     */
    protected $output;

    /**
     * @var DocumentationConfig
     */
    private $config;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var bool
     */
    private $forceIt;

    /**
     * @var bool
     */
    private $shouldGeneratePostmanCollection = true;

    /**
     * @var Documentarian
     */
    private $documentarian;

    /**
     * @var bool
     */
    private $isStatic;

    /**
     * @var string
     */
    private $sourceOutputPath;

    /**
     * @var string
     */
    private $outputPath;

    public function __construct(Command $output, DocumentationConfig $config = null, bool $forceIt = false)
    {
        // If no config is injected, pull from global
        $this->config = $config ?: new DocumentationConfig(config('apidoc'));
        $this->baseUrl = $this->config->get('base_url') ?? config('app.url');
        $this->forceIt = $forceIt;
        $this->output = $output;
        $this->shouldGeneratePostmanCollection = $this->config->get('postman.enabled', false);
        $this->documentarian = new Documentarian();
        $this->isStatic = $this->config->get('type') === 'static';
        $this->sourceOutputPath = 'resources/docs';
        $this->outputPath = $this->isStatic ? ($this->config->get('output_folder') ?? 'public/docs') : 'resources/views/apidoc';
    }

    public function writeDocs(Collection $routes)
    {
        // The source files (index.md, js/, css/, and images/) always go in resources/docs/source.
        // The static assets (js/, css/, and images/) always go in public/docs/.
        // For 'static' docs, the output files (index.html, collection.json) go in public/docs/.
        // For 'laravel' docs, the output files (index.blade.php, collection.json)
        // go in resources/views/apidoc/ and storage/app/apidoc/ respectively.

        $this->writeMarkdownAndSourceFiles($routes);

        $this->writeHtmlDocs();

        $this->writePostmanCollection($routes);
    }

    /**
     * @param  Collection $parsedRoutes
     *
     * @return void
     */
    public function writeMarkdownAndSourceFiles(Collection $parsedRoutes)
    {
        $targetFile = $this->getSourceOutputPath() . '/source/index.md';
        $compareFile = $this->getSourceOutputPath() . '/source/.compare.md';

        $infoText = view('apidoc::partials.info')
            ->with('outputPath', 'docs')
            ->with('showPostmanCollectionButton', $this->shouldGeneratePostmanCollection);

        $settings = ['languages' => $this->config->get('example_languages')];
        // Generate Markdown for each route
        $parsedRouteOutput = $this->generateMarkdownOutputForEachRoute($parsedRoutes, $settings);

        $frontmatter = view('apidoc::partials.frontmatter')
            ->with('settings', $settings);

        /*
         * If the target file already exists,
         * we check if the documentation was modified
         * and skip the modified parts of the routes.
         */
        if (file_exists($targetFile) && file_exists($compareFile)) {
            $generatedDocumentation = file_get_contents($targetFile);
            $compareDocumentation = file_get_contents($compareFile);

            $parsedRouteOutput->transform(function (Collection $routeGroup) use ($generatedDocumentation, $compareDocumentation) {
                return $routeGroup->transform(function (array $route) use ($generatedDocumentation, $compareDocumentation) {
                    if (preg_match('/<!-- START_' . $route['id'] . ' -->(.*)<!-- END_' . $route['id'] . ' -->/is', $generatedDocumentation, $existingRouteDoc)) {
                        $routeDocumentationChanged = (preg_match('/<!-- START_' . $route['id'] . ' -->(.*)<!-- END_' . $route['id'] . ' -->/is', $compareDocumentation, $lastDocWeGeneratedForThisRoute) && $lastDocWeGeneratedForThisRoute[1] !== $existingRouteDoc[1]);
                        if ($routeDocumentationChanged === false || $this->forceIt) {
                            if ($routeDocumentationChanged) {
                                $this->output->warn('Discarded manual changes for route [' . implode(',', $route['methods']) . '] ' . $route['uri']);
                            }
                        } else {
                            $this->output->warn('Skipping modified route [' . implode(',', $route['methods']) . '] ' . $route['uri']);
                            $route['modified_output'] = $existingRouteDoc[0];
                        }
                    }

                    return $route;
                });
            });
        }

        $prependFileContents = $this->getMarkdownToPrepend();
        $appendFileContents = $this->getMarkdownToAppend();

        $markdown = view('apidoc::documentarian')
            ->with('writeCompareFile', false)
            ->with('frontmatter', $frontmatter)
            ->with('infoText', $infoText)
            ->with('prependMd', $prependFileContents)
            ->with('appendMd', $appendFileContents)
            ->with('outputPath', $this->config->get('output'))
            ->with('showPostmanCollectionButton', $this->shouldGeneratePostmanCollection)
            ->with('parsedRoutes', $parsedRouteOutput);

        $this->output->info('Writing index.md and source files to: ' . $this->getSourceOutputPath());

        if (! is_dir($this->getSourceOutputPath())) {
            $documentarian = new Documentarian();
            $documentarian->create($this->getSourceOutputPath());
        }

        // Write output file
        file_put_contents($targetFile, $markdown);

        // Write comparable markdown file
        $compareMarkdown = view('apidoc::documentarian')
            ->with('writeCompareFile', true)
            ->with('frontmatter', $frontmatter)
            ->with('infoText', $infoText)
            ->with('prependMd', $prependFileContents)
            ->with('appendMd', $appendFileContents)
            ->with('outputPath', $this->config->get('output'))
            ->with('showPostmanCollectionButton', $this->shouldGeneratePostmanCollection)
            ->with('parsedRoutes', $parsedRouteOutput);

        file_put_contents($compareFile, $compareMarkdown);

        $this->output->info('Wrote index.md and source files to: ' . $this->getSourceOutputPath());
    }

    public function generateMarkdownOutputForEachRoute(Collection $parsedRoutes, array $settings): Collection
    {
        $parsedRouteOutput = $parsedRoutes->map(function (Collection $routeGroup) use ($settings) {
            return $routeGroup->map(function (array $route) use ($settings) {
                if (count($route['cleanBodyParameters']) && ! isset($route['headers']['Content-Type'])) {
                    // Set content type if the user forgot to set it
                    $route['headers']['Content-Type'] = 'application/json';
                }
                $qmRoute = QMRoute::findByUri($route["uri"], $route["methods"][0]);
                if(empty($route["metadata"]["title"])){
                    $route["metadata"]["title"] = $qmRoute->getTitleAttribute();
                }
                $route["metadata"]["authenticated"] = $qmRoute->requiresAuth();
                if(empty($route["metadata"]["description"])){
                    $route["metadata"]["description"] =
                        //"<div>". // This breaks the rendering from markdown to HTML
                        //"<div style=\"max-width: 50%; padding: 10px;\">".
                        $qmRoute->getDescriptionHtml($route["boundUri"])
                    //."</div>"
                    ;
                }
                // TODO:: if(empty($route["metadata"]["groupName"])){$route["metadata"]["groupName"] = $qmRoute->getGroupName();}
                $hasRequestOptions = ! empty($route['headers']) || ! empty($route['cleanQueryParameters']) || ! empty($route['cleanBodyParameters']);
                $route['output'] = (string) view('apidoc::partials.route')
                    ->with('hasRequestOptions', $hasRequestOptions)
                    ->with('route', $route)
                    ->with('qmRoute', $qmRoute)
                    ->with('settings', $settings)
                    ->with('baseUrl', $this->baseUrl)
                    ->render();

                return $route;
            });
        });

        return $parsedRouteOutput;
    }

    protected function writePostmanCollection(Collection $parsedRoutes): void
    {
        if ($this->shouldGeneratePostmanCollection) {
            $this->output->info('Generating Postman collection');

            $collection = $this->generatePostmanCollection($parsedRoutes);
            if ($this->isStatic) {
                $collectionPath = $this->getOutputPath()."/collection.json";
                file_put_contents($collectionPath, $collection);
            } else {
                $storageInstance = Storage::disk($this->config->get('storage'));
                $storageInstance->put('apidoc/collection.json', $collection, 'public');
                if ($this->config->get('storage') == 'local') {
                    $collectionPath = 'storage/app/apidoc/collection.json';
                } else {
                    $collectionPath = $storageInstance->url('collection.json');
                }
            }

            $this->output->info("Wrote Postman collection to: {$collectionPath}");
        }
    }

    /**
     * Generate Postman collection JSON file.
     *
     * @param Collection $routes
     *
     * @return string
     */
    public function generatePostmanCollection(Collection $routes)
    {
        /** @var PostmanCollectionWriter $writer */
        $writer = app()->makeWith(
            PostmanCollectionWriter::class,
            ['routeGroups' => $routes, 'baseUrl' => $this->baseUrl]
        );

        return $writer->getCollection();
    }

    protected function getMarkdownToPrepend(): string
    {
        $prependFile = $this->getSourceOutputPath() . '/source/prepend.md';
        $prependFileContents = file_exists($prependFile)
            ? file_get_contents($prependFile) . "\n" : '';

        return $prependFileContents;
    }

    protected function getMarkdownToAppend(): string
    {
        $appendFile = $this->getSourceOutputPath() . '/source/append.md';
        $appendFileContents = file_exists($appendFile)
            ? "\n" . file_get_contents($appendFile) : '';

        return $appendFileContents;
    }

    protected function copyAssetsFromSourceFolderToPublicFolder(): void
    {
        $publicPath = base_path($this->config->get('output_folder') ?? base_path('public/docs'));
        if (! is_dir($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        if (! is_dir("{$publicPath}/css")) {
            mkdir("{$publicPath}/css", 0777, true);
        }

        if (! is_dir("{$publicPath}/js")) {
            mkdir("{$publicPath}/js", 0777, true);
        }


        // I use my own JS copy($this->getSourceOutputPath()."/js/all.js", "{$publicPath}/js/all.js");
        // Already there rcopy($this->getSourceOutputPath()."/images", "{$publicPath}/images");
        // Already there rcopy($this->getSourceOutputPath()."/css", "{$publicPath}/css");

        if ($logo = $this->config->get('logo')) {
            // I use my own JS copy($logo, "{$publicPath}/images/logo.png");
        }
    }

    protected function getSourceOutputPath(): string {
        return base_path($this->sourceOutputPath);
    }

    public function getOutputIndexHtmlPath():string{
        return $this->getOutputPath()."/index.html";
    }

    protected function moveOutputFromSourceFolderToTargetFolder(): void
    {
        $outputPath = $this->getOutputPath();
        if ($this->isStatic) {
            // Move output (index.html, css/style.css and js/all.js) to public/docs
            rename($this->getSourceOutputPath()."/index.html", $this->getOutputIndexHtmlPath());
        } else {
            // Move output to resources/views
            if (! is_dir($outputPath)) {
                mkdir($outputPath);
            }
            rename($this->getSourceOutputPath()."/index.html", "$outputPath/index.blade.php");
            $contents = file_get_contents("$outputPath/index.blade.php");
            //
            $contents = str_replace('href="css/style.css"', 'href="{{ asset(\'/docs/css/style.css\') }}"', $contents);
            $contents = str_replace('src="js/all.js"', 'src="{{ asset(\'/docs/js/all.js\') }}"', $contents);
            $contents = str_replace('src="images/', 'src="/docs/images/', $contents);
            $contents = preg_replace('#href="https?://.+?/docs/collection.json"#', 'href="{{ route("apidoc.json") }}"', $contents);
            file_put_contents("$outputPath/index.blade.php", $contents);
        }
    }

    public function writeHtmlDocs(): void
    {
        $this->output->info('Generating API HTML code');

        $this->documentarian->generate($this->getSourceOutputPath());

        // Move assets to public folder
        $this->copyAssetsFromSourceFolderToPublicFolder();

        $this->moveOutputFromSourceFolderToTargetFolder();

        $index = $this->getOutputIndexHtmlPath();
        $contents = file_get_contents($index);
        $contents = str_replace('<script src="js/all.js"></script>', '<script
            src="https://code.jquery.com/jquery-2.2.4.js"
            integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI="
            crossorigin="anonymous"></script>
    <script
            src="https://code.jquery.com/ui/1.11.3/jquery-ui.js"
            integrity="sha256-0vBSIAi/8FxkNOSKyPEfdGQzFDak1dlqFKBYqBp1yC4="
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-highlight@3.5.0/jquery.highlight.min.js"></script>
    <script src="js/lib/jquery.tocify.js"></script>
    <script src="js/lib/energize.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.1.0/highlight.min.js" integrity="sha256-fkOAs5tViC8MpG+5VCOqdlSpLL8htz4mdL2VZlWGoMA=" crossorigin="anonymous"></script>
    <script src="js/lib/imagesloaded.min.js"></script>
    <!--    <script src="js/lib/lunr.js"></script>-->
    <script src="js/script.js"></script>', $contents);
        $contents = QMStr::removeDatesAndTimes($contents);
        $contents = str_replace('http://localhost', '', $contents);
        file_put_contents($index, $contents);

        $postmanPath = base_path('public/docs/collection.json');
        $contents = file_get_contents($postmanPath);
        $contents = QMStr::removeDatesAndTimes($contents);
        file_put_contents($postmanPath, $contents);

        foreach(FileFinder::listFiles('storage/responses') as $file){
            $contents = file_get_contents($file);
            $contents = QMStr::removeDatesAndTimes($contents);
            file_put_contents($postmanPath, $contents);
        }

        $this->output->info("Wrote HTML documentation to:".$this->getOutputPath());
    }

    protected function getOutputPath(): string {
        return base_path($this->outputPath);
    }
}
