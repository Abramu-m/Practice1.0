<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RedundancyAnalysisController extends Controller
{
    /**
     * Display the redundancy analysis report.
     */
    public function index()
    {
        $analysis = [
            'routes' => $this->analyzeRoutes(),
            'controllers' => $this->analyzeControllers(),
            'models' => $this->analyzeModels(),
            'redundancies' => $this->identifyRedundancies(),
            'statistics' => $this->generateStatistics(),
        ];

        return view('admin.redundancy-analysis.index', compact('analysis'));
    }

    /**
     * Analyze all routes in the application.
     */
    protected function analyzeRoutes(): array
    {
        $routeFiles = [
            'web.php' => base_path('routes/web.php'),
            'api.php' => base_path('routes/api.php'),
            'auth.php' => base_path('routes/auth.php'),
            'medication.php' => base_path('routes/medication.php'),
            'requisitions.php' => base_path('routes/requisitions.php'),
            'learning.php' => base_path('routes/learning.php'),
            'test-cds.php' => base_path('routes/test-cds.php'),
        ];

        $routeAnalysis = [];
        $allRoutes = Route::getRoutes();
        $routesByFile = [];

        // Group routes by their likely source file based on URI patterns
        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            $methods = implode('|', $route->methods());
            $action = $route->getActionName();

            // Try to determine which file this route comes from
            $sourceFile = $this->guessRouteSourceFile($uri, $name);
            
            if (!isset($routesByFile[$sourceFile])) {
                $routesByFile[$sourceFile] = [];
            }

            $routesByFile[$sourceFile][] = [
                'uri' => $uri,
                'name' => $name,
                'methods' => $methods,
                'action' => $action,
            ];
        }

        foreach ($routeFiles as $fileName => $filePath) {
            $routeCount = isset($routesByFile[$fileName]) ? count($routesByFile[$fileName]) : 0;
            $fileSize = File::exists($filePath) ? File::size($filePath) : 0;

            $routeAnalysis[] = [
                'file' => $fileName,
                'path' => $filePath,
                'exists' => File::exists($filePath),
                'size' => $this->formatBytes($fileSize),
                'route_count' => $routeCount,
                'routes' => $routesByFile[$fileName] ?? [],
            ];
        }

        return $routeAnalysis;
    }

    /**
     * Guess which route file a route belongs to based on URI patterns.
     */
    protected function guessRouteSourceFile(string $uri, ?string $name): string
    {
        if (str_starts_with($uri, 'api/')) {
            return 'api.php';
        }
        if (str_contains($uri, 'login') || str_contains($uri, 'register') || str_contains($uri, 'password')) {
            return 'auth.php';
        }
        if (str_contains($uri, 'medication') || str_contains($uri, 'grn') || str_contains($uri, 'stock')) {
            return 'medication.php';
        }
        if (str_contains($uri, 'requisition') || str_contains($uri, 'store-unit') || str_contains($uri, 'store-location')) {
            return 'requisitions.php';
        }
        if (str_contains($uri, 'learn')) {
            return 'learning.php';
        }
        if (str_contains($uri, 'cds-test')) {
            return 'test-cds.php';
        }
        return 'web.php';
    }

    /**
     * Analyze all controllers.
     */
    protected function analyzeControllers(): array
    {
        $controllersPath = app_path('Http/Controllers');
        $controllers = [];

        $files = File::allFiles($controllersPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace($controllersPath . '/', '', $file->getPathname());
                $className = 'App\\Http\\Controllers\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                if (class_exists($className)) {
                    $reflection = new \ReflectionClass($className);
                    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                    
                    // Filter out inherited methods and magic methods
                    $userMethods = array_filter($methods, function($method) use ($reflection) {
                        return $method->class === $reflection->getName() 
                            && !str_starts_with($method->name, '__');
                    });

                    $controllers[] = [
                        'name' => $reflection->getShortName(),
                        'full_name' => $className,
                        'path' => $relativePath,
                        'method_count' => count($userMethods),
                        'methods' => array_map(fn($m) => $m->name, $userMethods),
                        'size' => $this->formatBytes(File::size($file->getPathname())),
                    ];
                }
            }
        }

        // Sort by method count descending
        usort($controllers, fn($a, $b) => $b['method_count'] <=> $a['method_count']);

        return $controllers;
    }

    /**
     * Analyze all models.
     */
    protected function analyzeModels(): array
    {
        $modelsPath = app_path('Models');
        $models = [];

        if (!File::exists($modelsPath)) {
            return $models;
        }

        $files = File::allFiles($modelsPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace($modelsPath . '/', '', $file->getPathname());
                $className = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                if (class_exists($className)) {
                    $reflection = new \ReflectionClass($className);
                    
                    $models[] = [
                        'name' => $reflection->getShortName(),
                        'full_name' => $className,
                        'path' => $relativePath,
                        'size' => $this->formatBytes(File::size($file->getPathname())),
                    ];
                }
            }
        }

        // Sort alphabetically
        usort($models, fn($a, $b) => $a['name'] <=> $b['name']);

        return $models;
    }

    /**
     * Identify redundancies in the codebase.
     */
    protected function identifyRedundancies(): array
    {
        return [
            'duplicate_routes' => $this->findDuplicateRoutes(),
            'oversized_controllers' => $this->findOversizedControllers(),
            'minimal_controllers' => $this->findMinimalControllers(),
            'related_models' => $this->findRelatedModels(),
        ];
    }

    /**
     * Find duplicate routes.
     */
    protected function findDuplicateRoutes(): array
    {
        $allRoutes = Route::getRoutes();
        $routesByUri = [];
        $duplicates = [];

        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            $methods = implode('|', $route->methods());
            $key = $methods . '::' . $uri;

            if (!isset($routesByUri[$key])) {
                $routesByUri[$key] = [];
            }

            $routesByUri[$key][] = [
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'methods' => $methods,
                'uri' => $uri,
            ];
        }

        foreach ($routesByUri as $key => $routes) {
            if (count($routes) > 1) {
                $duplicates[] = [
                    'key' => $key,
                    'count' => count($routes),
                    'routes' => $routes,
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Find oversized controllers (> 15 methods).
     */
    protected function findOversizedControllers(): array
    {
        $controllers = $this->analyzeControllers();
        
        return array_filter($controllers, function($controller) {
            return $controller['method_count'] > 15;
        });
    }

    /**
     * Find minimal controllers (<= 3 methods).
     */
    protected function findMinimalControllers(): array
    {
        $controllers = $this->analyzeControllers();
        
        return array_filter($controllers, function($controller) {
            return $controller['method_count'] > 0 && $controller['method_count'] <= 3;
        });
    }

    /**
     * Find related models by naming patterns.
     */
    protected function findRelatedModels(): array
    {
        $models = $this->analyzeModels();
        $grouped = [];

        foreach ($models as $model) {
            // Group by prefix (e.g., Medication*, Store*, Cds*, etc.)
            preg_match('/^([A-Z][a-z]+)/', $model['name'], $matches);
            $prefix = $matches[1] ?? 'Other';

            if (!isset($grouped[$prefix])) {
                $grouped[$prefix] = [];
            }

            $grouped[$prefix][] = $model['name'];
        }

        // Only return groups with 3+ models
        return array_filter($grouped, fn($group) => count($group) >= 3);
    }

    /**
     * Generate statistics summary.
     */
    protected function generateStatistics(): array
    {
        $routes = $this->analyzeRoutes();
        $controllers = $this->analyzeControllers();
        $models = $this->analyzeModels();
        
        $totalRoutes = 0;
        foreach ($routes as $routeFile) {
            $totalRoutes += $routeFile['route_count'];
        }

        return [
            'total_routes' => $totalRoutes,
            'total_route_files' => count($routes),
            'total_controllers' => count($controllers),
            'total_models' => count($models),
            'avg_controller_methods' => count($controllers) > 0 
                ? round(array_sum(array_column($controllers, 'method_count')) / count($controllers), 1)
                : 0,
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
