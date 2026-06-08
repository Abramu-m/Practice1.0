<?php

/**
 * Navigation Helper Functions
 * 
 * These functions help determine active navigation states across the application.
 * They check if current route/path matches the provided patterns.
 */

if (!function_exists('is_nav_active')) {
    /**
     * Check if any of the given routes or paths are currently active
     * 
     * @param array $routes Array of route name patterns (supports wildcards with *)
     * @param array $paths Array of URL path patterns (supports wildcards with *)
     * @return bool
     */
    function is_nav_active(array $routes = [], array $paths = []): bool
    {
        // Check route names
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return true;
            }
        }
        
        // Check URL paths
        foreach ($paths as $path) {
            if (request()->is($path)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('nav_active_class')) {
    /**
     * Return 'active' class if the navigation item is active
     * 
     * @param array $routes Array of route name patterns
     * @param array $paths Array of URL path patterns
     * @return string
     */
    function nav_active_class(array $routes = [], array $paths = []): string
    {
        return is_nav_active($routes, $paths) ? 'active' : '';
    }
}

if (!function_exists('nav_menu_open_class')) {
    /**
     * Return 'menu-open' class if any child routes are active
     * 
     * @param array $routes Array of route name patterns
     * @param array $paths Array of URL path patterns
     * @return string
     */
    function nav_menu_open_class(array $routes = [], array $paths = []): string
    {
        return is_nav_active($routes, $paths) ? 'menu-open' : '';
    }
}

if (!function_exists('nav_display_style')) {
    /**
     * Return inline display style for active menus
     * 
     * @param array $routes Array of route name patterns
     * @param array $paths Array of URL path patterns
     * @return string
     */
    function nav_display_style(array $routes = [], array $paths = []): string
    {
        return is_nav_active($routes, $paths) ? 'display: block;' : 'display: none;';
    }
}

if (!function_exists('nav_active_query_class')) {
    /**
     * Return 'active' class if the given route is active and all query parameters match.
     *
     * Useful when multiple sub-items share the same route but differ only by query string
     * (e.g. ?status=pending vs ?status=dispensed).
     *
     * @param array $routes      Array of route name patterns
     * @param array $queryParams Associative array of expected query parameters
     * @return string
     */
    function nav_active_query_class(array $routes = [], array $queryParams = []): string
    {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                foreach ($queryParams as $key => $value) {
                    if ($value === null) {
                        // Expect this query key to be absent
                        if (request()->query($key) !== null) {
                            return '';
                        }
                    } else {
                        if (request()->query($key) !== $value) {
                            return '';
                        }
                    }
                }
                return 'active';
            }
        }

        return '';
    }
}

if (!function_exists('current_route_name')) {
    /**
     * Get the current route name (useful for debugging)
     * 
     * @return string|null
     */
    function current_route_name(): ?string
    {
        return request()->route() ? request()->route()->getName() : null;
    }
}

if (!function_exists('current_path')) {
    /**
     * Get the current URL path (useful for debugging)
     * 
     * @return string
     */
    function current_path(): string
    {
        return request()->path();
    }
}
