@extends('layouts.app_main_layout')

@section('page_title', 'Redundancy Analysis')

@section('main_content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-2">
                <i class="fas fa-chart-pie"></i> Project Redundancy Analysis
            </h4>
            <p class="text-muted mb-0">
                Comprehensive assessment of redundant routes, controllers, methods, and models in the application.
            </p>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 fw-light">Total Routes</div>
                            <div class="fs-3 fw-bold">{{ $analysis['statistics']['total_routes'] }}</div>
                        </div>
                        <div><i class="fas fa-route fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 fw-light">Route Files</div>
                            <div class="fs-3 fw-bold">{{ $analysis['statistics']['total_route_files'] }}</div>
                        </div>
                        <div><i class="fas fa-file-code fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 fw-light">Controllers</div>
                            <div class="fs-3 fw-bold">{{ $analysis['statistics']['total_controllers'] }}</div>
                        </div>
                        <div><i class="fas fa-cogs fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 fw-light">Models</div>
                            <div class="fs-3 fw-bold">{{ $analysis['statistics']['total_models'] }}</div>
                        </div>
                        <div><i class="fas fa-database fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 mb-3">
            <div class="card text-white bg-secondary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 fw-light">Avg Methods/Controller</div>
                            <div class="fs-3 fw-bold">{{ $analysis['statistics']['avg_controller_methods'] }}</div>
                        </div>
                        <div><i class="fas fa-chart-bar fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Redundancy Findings -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Redundancy Findings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Duplicate Routes -->
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">
                                    <i class="fas fa-clone"></i> Duplicate Routes
                                </h6>
                                <p class="mb-1">
                                    Found <strong>{{ count($analysis['redundancies']['duplicate_routes']) }}</strong> duplicate route(s)
                                </p>
                                @if(count($analysis['redundancies']['duplicate_routes']) > 0)
                                    <small class="text-muted">Click "View Details" below to see specifics</small>
                                @else
                                    <small class="text-success"><i class="fas fa-check-circle"></i> No duplicates found!</small>
                                @endif
                            </div>
                        </div>

                        <!-- Oversized Controllers -->
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="fas fa-weight-hanging"></i> Oversized Controllers (&gt;15 methods)
                                </h6>
                                <p class="mb-1">
                                    Found <strong>{{ count($analysis['redundancies']['oversized_controllers']) }}</strong> oversized controller(s)
                                </p>
                                @if(count($analysis['redundancies']['oversized_controllers']) > 0)
                                    <small class="text-muted">Consider breaking these into smaller controllers</small>
                                @else
                                    <small class="text-success"><i class="fas fa-check-circle"></i> All controllers are well-sized!</small>
                                @endif
                            </div>
                        </div>

                        <!-- Minimal Controllers -->
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-feather"></i> Minimal Controllers (≤3 methods)
                                </h6>
                                <p class="mb-1">
                                    Found <strong>{{ count($analysis['redundancies']['minimal_controllers']) }}</strong> minimal controller(s)
                                </p>
                                @if(count($analysis['redundancies']['minimal_controllers']) > 0)
                                    <small class="text-muted">Consider merging these into related controllers</small>
                                @endif
                            </div>
                        </div>

                        <!-- Related Models -->
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-secondary">
                                <h6 class="alert-heading">
                                    <i class="fas fa-project-diagram"></i> Related Model Groups
                                </h6>
                                <p class="mb-1">
                                    Found <strong>{{ count($analysis['redundancies']['related_models']) }}</strong> model group(s) with 3+ related models
                                </p>
                                <small class="text-muted">Review for potential consolidation opportunities</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabbed Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="analysisTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="routes-tab" data-bs-toggle="tab" data-bs-target="#routes" type="button" role="tab">
                                <i class="fas fa-route"></i> Routes Analysis
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="controllers-tab" data-bs-toggle="tab" data-bs-target="#controllers" type="button" role="tab">
                                <i class="fas fa-cogs"></i> Controllers Analysis
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="models-tab" data-bs-toggle="tab" data-bs-target="#models" type="button" role="tab">
                                <i class="fas fa-database"></i> Models Analysis
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="redundancies-tab" data-bs-toggle="tab" data-bs-target="#redundancies" type="button" role="tab">
                                <i class="fas fa-exclamation-triangle"></i> Redundancy Details
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="analysisTabContent">
                        <!-- Routes Tab -->
                        <div class="tab-pane fade show active" id="routes" role="tabpanel">
                            <h5 class="mb-3">Route Files Analysis</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Size</th>
                                            <th>Routes Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analysis['routes'] as $routeFile)
                                        <tr>
                                            <td><code>{{ $routeFile['file'] }}</code></td>
                                            <td>
                                                @if($routeFile['exists'])
                                                    <span class="badge bg-success">Exists</span>
                                                @else
                                                    <span class="badge bg-danger">Missing</span>
                                                @endif
                                            </td>
                                            <td>{{ $routeFile['size'] }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $routeFile['route_count'] }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#routes-{{ $loop->index }}"
                                                        aria-expanded="false">
                                                    <i class="fas fa-eye"></i> View Routes
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="routes-{{ $loop->index }}">
                                            <td colspan="5">
                                                <div class="p-3 bg-light">
                                                    @if(count($routeFile['routes']) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 100px;">Methods</th>
                                                                        <th>URI</th>
                                                                        <th>Name</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach(array_slice($routeFile['routes'], 0, 10) as $route)
                                                                    <tr>
                                                                        <td><code class="small">{{ $route['methods'] }}</code></td>
                                                                        <td><code class="small">{{ $route['uri'] }}</code></td>
                                                                        <td><code class="small">{{ $route['name'] ?? 'N/A' }}</code></td>
                                                                        <td><code class="small">{{ Str::limit($route['action'], 50) }}</code></td>
                                                                    </tr>
                                                                    @endforeach
                                                                    @if(count($routeFile['routes']) > 10)
                                                                    <tr>
                                                                        <td colspan="4" class="text-center text-muted">
                                                                            ... and {{ count($routeFile['routes']) - 10 }} more routes
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <p class="text-muted mb-0">No routes found in this file.</p>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Controllers Tab -->
                        <div class="tab-pane fade" id="controllers" role="tabpanel">
                            <h5 class="mb-3">Controllers Analysis (Sorted by Method Count)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Controller Name</th>
                                            <th>Path</th>
                                            <th>Methods</th>
                                            <th>Size</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analysis['controllers'] as $controller)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $controller['name'] }}</strong>
                                                <button class="btn btn-sm btn-link p-0 ms-2" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#controller-{{ $loop->index }}"
                                                        aria-expanded="false">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                            <td><code class="small">{{ $controller['path'] }}</code></td>
                                            <td>
                                                <span class="badge {{ $controller['method_count'] > 15 ? 'bg-danger' : ($controller['method_count'] <= 3 ? 'bg-warning' : 'bg-success') }}">
                                                    {{ $controller['method_count'] }}
                                                </span>
                                            </td>
                                            <td>{{ $controller['size'] }}</td>
                                            <td>
                                                @if($controller['method_count'] > 15)
                                                    <span class="badge bg-danger">Oversized</span>
                                                @elseif($controller['method_count'] <= 3)
                                                    <span class="badge bg-warning text-dark">Minimal</span>
                                                @else
                                                    <span class="badge bg-success">Good</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="controller-{{ $loop->index }}">
                                            <td colspan="6">
                                                <div class="p-3 bg-light">
                                                    <h6>Methods ({{ count($controller['methods']) }}):</h6>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($controller['methods'] as $method)
                                                            <code class="badge bg-secondary">{{ $method }}</code>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Models Tab -->
                        <div class="tab-pane fade" id="models" role="tabpanel">
                            <h5 class="mb-3">Models Analysis ({{ count($analysis['models']) }} total)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Model Name</th>
                                            <th>Full Class Name</th>
                                            <th>Size</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analysis['models'] as $model)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $model['name'] }}</strong></td>
                                            <td><code class="small">{{ $model['full_name'] }}</code></td>
                                            <td>{{ $model['size'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Redundancy Details Tab -->
                        <div class="tab-pane fade" id="redundancies" role="tabpanel">
                            <h5 class="mb-3">Detailed Redundancy Analysis</h5>
                            
                            <!-- Duplicate Routes -->
                            <div class="mb-4">
                                <h6 class="text-danger">
                                    <i class="fas fa-clone"></i> Duplicate Routes ({{ count($analysis['redundancies']['duplicate_routes']) }})
                                </h6>
                                @if(count($analysis['redundancies']['duplicate_routes']) > 0)
                                    <div class="alert alert-danger">
                                        <p class="mb-2">The following routes are defined multiple times:</p>
                                        @foreach($analysis['redundancies']['duplicate_routes'] as $duplicate)
                                        <div class="mb-3 p-2 bg-white border rounded">
                                            <strong>{{ $duplicate['key'] }}</strong> - Found {{ $duplicate['count'] }} times
                                            <ul class="mb-0 mt-2">
                                                @foreach($duplicate['routes'] as $route)
                                                <li>
                                                    Name: <code>{{ $route['name'] ?? 'N/A' }}</code> | 
                                                    Action: <code>{{ $route['action'] }}</code>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> No duplicate routes found!
                                    </div>
                                @endif
                            </div>

                            <!-- Oversized Controllers -->
                            <div class="mb-4">
                                <h6 class="text-warning">
                                    <i class="fas fa-weight-hanging"></i> Oversized Controllers ({{ count($analysis['redundancies']['oversized_controllers']) }})
                                </h6>
                                @if(count($analysis['redundancies']['oversized_controllers']) > 0)
                                    <div class="alert alert-warning">
                                        <p class="mb-2">These controllers have more than 15 methods and should be refactored:</p>
                                        <ul>
                                            @foreach($analysis['redundancies']['oversized_controllers'] as $controller)
                                            <li>
                                                <strong>{{ $controller['name'] }}</strong> - 
                                                <span class="badge bg-danger">{{ $controller['method_count'] }} methods</span>
                                                <br><small class="text-muted">{{ $controller['path'] }}</small>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> All controllers are well-sized!
                                    </div>
                                @endif
                            </div>

                            <!-- Minimal Controllers -->
                            <div class="mb-4">
                                <h6 class="text-info">
                                    <i class="fas fa-feather"></i> Minimal Controllers ({{ count($analysis['redundancies']['minimal_controllers']) }})
                                </h6>
                                @if(count($analysis['redundancies']['minimal_controllers']) > 0)
                                    <div class="alert alert-info">
                                        <p class="mb-2">These controllers have 3 or fewer methods and could potentially be merged:</p>
                                        <ul>
                                            @foreach($analysis['redundancies']['minimal_controllers'] as $controller)
                                            <li>
                                                <strong>{{ $controller['name'] }}</strong> - 
                                                <span class="badge bg-warning text-dark">{{ $controller['method_count'] }} methods</span>
                                                <br><small class="text-muted">Methods: {{ implode(', ', $controller['methods']) }}</small>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <!-- Related Models -->
                            <div class="mb-4">
                                <h6 class="text-secondary">
                                    <i class="fas fa-project-diagram"></i> Related Model Groups ({{ count($analysis['redundancies']['related_models']) }})
                                </h6>
                                @if(count($analysis['redundancies']['related_models']) > 0)
                                    <div class="alert alert-secondary">
                                        <p class="mb-2">These model groups share naming patterns and may have consolidation opportunities:</p>
                                        @foreach($analysis['redundancies']['related_models'] as $prefix => $models)
                                        <div class="mb-2">
                                            <strong>{{ $prefix }}*</strong> 
                                            <span class="badge bg-secondary">{{ count($models) }} models</span>
                                            <br><small class="text-muted">{{ implode(', ', $models) }}</small>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li class="mb-2">
                            <strong>Consolidate Route Definitions:</strong> Remove duplicate routes from multiple route files to avoid conflicts.
                        </li>
                        <li class="mb-2">
                            <strong>Refactor Oversized Controllers:</strong> Break down controllers with more than 15 methods into smaller, focused controllers following single responsibility principle.
                        </li>
                        <li class="mb-2">
                            <strong>Merge Minimal Controllers:</strong> Consider merging controllers with 3 or fewer methods into related larger controllers to reduce file count.
                        </li>
                        <li class="mb-2">
                            <strong>Review Model Relationships:</strong> Examine related model groups for potential inheritance or trait-based consolidation.
                        </li>
                        <li class="mb-2">
                            <strong>Implement Service Layer:</strong> Extract complex business logic from controllers into dedicated service classes.
                        </li>
                        <li class="mb-2">
                            <strong>Regular Audits:</strong> Schedule periodic redundancy audits to maintain code quality and prevent technical debt accumulation.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
