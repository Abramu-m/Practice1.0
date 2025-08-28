<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Router;
use ReflectionClass;
use ReflectionMethod;

class DetectUnusedRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:unused 
                          {--format=table : Output format (table, json, csv)}
                          {--export= : Export results to file}
                          {--exclude=* : Exclude specific route names or patterns}
                          {--include-api : Include API routes in analysis}
                          {--show-details : Show detailed information about each route}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect unused routes in the Laravel application';

    /**
     * Routes that are commonly used but might not be easily detectable
     */
    protected $commonRoutes = [
        'login',
        'logout',
        'register',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'dashboard'
    ];

    /**
     * File extensions to search for route usage
     */
    protected $searchExtensions = ['php', 'blade.php', 'js', 'vue', 'ts'];

    /**
     * Directories to search for route usage
     */
    protected $searchDirectories = [
        'app',
        'resources/views',
        'resources/js',
        'public/js',
        'tests'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Analyzing routes for unused entries...');
        $this->newLine();

        // Get all routes
        $routes = $this->getAllRoutes();
        $this->info("Found {$routes->count()} total routes");

        // Analyze each route
        $unusedRoutes = [];
        $usedRoutes = [];
        $progressBar = $this->output->createProgressBar($routes->count());
        $progressBar->start();

        foreach ($routes as $route) {
            $routeInfo = $this->analyzeRoute($route);
            
            if ($routeInfo['is_used']) {
                $usedRoutes[] = $routeInfo;
            } else {
                $unusedRoutes[] = $routeInfo;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults($unusedRoutes, $usedRoutes);

        // Export if requested
        if ($this->option('export')) {
            $this->exportResults($unusedRoutes, $usedRoutes);
        }

        return 0;
    }

    /**
     * Get all routes from the application
     */
    protected function getAllRoutes()
    {
        $routes = collect();
        
        // Get routes from the route collection
        foreach (Route::getRoutes() as $route) {
            // Skip if API routes are excluded
            if (!$this->option('include-api') && $this->isApiRoute($route)) {
                continue;
            }

            // Skip excluded routes
            if ($this->shouldExcludeRoute($route)) {
                continue;
            }

            $routes->push($route);
        }

        return $routes;
    }

    /**
     * Analyze a single route for usage
     */
    protected function analyzeRoute($route)
    {
        $routeName = $route->getName();
        $routeUri = $route->uri();
        $routeMethods = implode('|', $route->methods());
        $routeAction = $route->getActionName();
        
        // Check if route is used
        $isUsed = $this->isRouteUsed($route);
        
        // Get additional details if requested
        $details = [];
        if ($this->option('show-details')) {
            $details = $this->getRouteDetails($route);
        }

        return [
            'name' => $routeName,
            'uri' => $routeUri,
            'methods' => $routeMethods,
            'action' => $routeAction,
            'is_used' => $isUsed,
            'details' => $details,
            'usage_locations' => $isUsed ? $this->findUsageLocations($route) : []
        ];
    }

    /**
     * Check if a route is used in the codebase
     */
    protected function isRouteUsed($route)
    {
        $routeName = $route->getName();
        $routeUri = $route->uri();

        // Check if it's a common route
        if ($routeName && in_array($routeName, $this->commonRoutes)) {
            return true;
        }

        // Search in files
        foreach ($this->searchDirectories as $directory) {
            $path = base_path($directory);
            
            if (!File::exists($path)) {
                continue;
            }

            if ($this->searchInDirectory($path, $routeName, $routeUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search for route usage in a directory
     */
    protected function searchInDirectory($directory, $routeName, $routeUri)
    {
        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            $extension = $file->getExtension();
            
            if (!in_array($extension, $this->searchExtensions) && 
                !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            if ($this->searchInFile($file->getPathname(), $routeName, $routeUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search for route usage in a file
     */
    protected function searchInFile($filePath, $routeName, $routeUri)
    {
        try {
            $content = File::get($filePath);
            
            // Search patterns
            $patterns = $this->getSearchPatterns($routeName, $routeUri);
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get search patterns for route usage
     */
    protected function getSearchPatterns($routeName, $routeUri)
    {
        $patterns = [];
        
        if ($routeName) {
            // Laravel route() helper
            $patterns[] = '/route\s*\(\s*[\'"]' . preg_quote($routeName, '/') . '[\'"]/';
            
            // Named routes in forms and links
            $patterns[] = '/[\'"]' . preg_quote($routeName, '/') . '[\'"]/';
            
            // URL helper with route name
            $patterns[] = '/url\s*\(\s*[\'"]' . preg_quote($routeName, '/') . '[\'"]/';
        }

        // Direct URI usage
        $uriPattern = preg_quote($routeUri, '/');
        $uriPattern = str_replace('\{', '(?:', $uriPattern);
        $uriPattern = str_replace('\}', ')?', $uriPattern);
        $patterns[] = '/[\'"]' . str_replace('\?', '\w*', $uriPattern) . '[\'"]/';
        
        // href attributes
        $patterns[] = '/href\s*=\s*[\'"][^\'\"]*' . preg_quote($routeUri, '/') . '[^\'\"]*[\'"]/';
        
        // action attributes
        $patterns[] = '/action\s*=\s*[\'"][^\'\"]*' . preg_quote($routeUri, '/') . '[^\'\"]*[\'"]/';

        return $patterns;
    }

    /**
     * Find specific usage locations for a route
     */
    protected function findUsageLocations($route)
    {
        $locations = [];
        $routeName = $route->getName();
        $routeUri = $route->uri();

        foreach ($this->searchDirectories as $directory) {
            $path = base_path($directory);
            
            if (!File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);
            
            foreach ($files as $file) {
                $extension = $file->getExtension();
                
                if (!in_array($extension, $this->searchExtensions) && 
                    !str_ends_with($file->getFilename(), '.blade.php')) {
                    continue;
                }

                $usageLines = $this->findUsageInFile($file->getPathname(), $routeName, $routeUri);
                if (!empty($usageLines)) {
                    $locations[] = [
                        'file' => $file->getRelativePathname(),
                        'lines' => $usageLines
                    ];
                }
            }
        }

        return $locations;
    }

    /**
     * Find usage lines in a specific file
     */
    protected function findUsageInFile($filePath, $routeName, $routeUri)
    {
        try {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES);
            $usageLines = [];
            
            $patterns = $this->getSearchPatterns($routeName, $routeUri);
            
            foreach ($lines as $lineNumber => $line) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $usageLines[] = $lineNumber + 1;
                        break;
                    }
                }
            }
            
            return array_unique($usageLines);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get additional route details
     */
    protected function getRouteDetails($route)
    {
        $details = [];
        
        // Controller and method information
        $action = $route->getAction();
        if (isset($action['controller'])) {
            $details['controller'] = $action['controller'];
        }
        
        // Middleware
        $details['middleware'] = $route->middleware();
        
        // Route parameters
        $details['parameters'] = $route->parameterNames();
        
        // Route domain
        if ($route->domain()) {
            $details['domain'] = $route->domain();
        }

        return $details;
    }

    /**
     * Check if route should be excluded
     */
    protected function shouldExcludeRoute($route)
    {
        $excludePatterns = $this->option('exclude');
        $routeName = $route->getName();
        $routeUri = $route->uri();

        foreach ($excludePatterns as $pattern) {
            if ($routeName && fnmatch($pattern, $routeName)) {
                return true;
            }
            if (fnmatch($pattern, $routeUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if route is an API route
     */
    protected function isApiRoute($route)
    {
        return str_starts_with($route->uri(), 'api/');
    }

    /**
     * Display results
     */
    protected function displayResults($unusedRoutes, $usedRoutes)
    {
        $format = $this->option('format');

        $this->info("📊 Analysis Results:");
        $this->info("Used routes: " . count($usedRoutes));
        $this->error("Unused routes: " . count($unusedRoutes));
        $this->newLine();

        if (empty($unusedRoutes)) {
            $this->info("🎉 Great! No unused routes found.");
            return;
        }

        $this->error("⚠️  Unused Routes Found:");
        $this->newLine();

        switch ($format) {
            case 'json':
                $this->line(json_encode($unusedRoutes, JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $this->displayCsvResults($unusedRoutes);
                break;
            default:
                $this->displayTableResults($unusedRoutes);
                break;
        }

        if ($this->option('show-details') && !empty($usedRoutes)) {
            $this->newLine();
            $this->info("✅ Used Routes (sample):");
            $sampleUsed = array_slice($usedRoutes, 0, 5);
            $this->displayTableResults($sampleUsed, true);
        }
    }

    /**
     * Display results in table format
     */
    protected function displayTableResults($routes, $showUsage = false)
    {
        $headers = ['Name', 'URI', 'Methods', 'Action'];
        
        if ($showUsage) {
            $headers[] = 'Usage Locations';
        }

        $rows = [];
        foreach ($routes as $route) {
            $row = [
                $route['name'] ?: 'N/A',
                $route['uri'],
                $route['methods'],
                $this->truncateString($route['action'], 50)
            ];
            
            if ($showUsage && !empty($route['usage_locations'])) {
                $locations = array_slice($route['usage_locations'], 0, 2);
                $locationText = implode(', ', array_map(function($loc) {
                    return $loc['file'] . ':' . implode(',', $loc['lines']);
                }, $locations));
                $row[] = $this->truncateString($locationText, 60);
            } elseif ($showUsage) {
                $row[] = 'Multiple locations';
            }
            
            $rows[] = $row;
        }

        $this->table($headers, $rows);
    }

    /**
     * Display results in CSV format
     */
    protected function displayCsvResults($routes)
    {
        $this->line("Name,URI,Methods,Action");
        foreach ($routes as $route) {
            $this->line(sprintf(
                '"%s","%s","%s","%s"',
                $route['name'] ?: 'N/A',
                $route['uri'],
                $route['methods'],
                str_replace('"', '""', $route['action'])
            ));
        }
    }

    /**
     * Export results to file
     */
    protected function exportResults($unusedRoutes, $usedRoutes)
    {
        $exportPath = $this->option('export');
        $data = [
            'analysis_date' => now()->toISOString(),
            'total_routes' => count($unusedRoutes) + count($usedRoutes),
            'unused_count' => count($unusedRoutes),
            'used_count' => count($usedRoutes),
            'unused_routes' => $unusedRoutes,
            'used_routes' => $usedRoutes
        ];

        File::put($exportPath, json_encode($data, JSON_PRETTY_PRINT));
        $this->info("Results exported to: {$exportPath}");
    }

    /**
     * Truncate string for display
     */
    protected function truncateString($string, $length)
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        return substr($string, 0, $length - 3) . '...';
    }
}
