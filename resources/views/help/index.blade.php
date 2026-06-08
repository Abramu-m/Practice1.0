@extends('layouts.app_main_layout')

@section('page_title', 'Help')

@section('main_content')
<div class="container">
  <h4>Help & Documentation</h4>
  <p class="text-muted">This page is a starting point for user help and documentation. Add guides, FAQs, and links to system docs here.</p>

  <div class="card">
    <div class="card-body">
      <h5>Getting started</h5>
      <ul>
        <li><a href="/docs/SYSTEM_OVERVIEW.md" target="_blank">System Overview</a></li>
        <li><a href="/docs/CLINICAL_WORKFLOW_GUIDE.md" target="_blank">Clinical Workflow Guide</a></li>
      </ul>
    </div>
  </div>
</div>
@endsection
