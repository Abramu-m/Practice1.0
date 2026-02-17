# Lab Investigation Modal Component

A reusable modal component for ordering lab investigations and medical procedures throughout the application.

## Overview

This component consists of three main files that work together to provide a complete lab investigation ordering interface:

1. **Blade Partial** (`resources/views/partials/lab_investigation_modal.blade.php`) - The HTML structure
2. **JavaScript** (`public/js/lab-investigation-modal.js`) - All functionality and AJAX calls
3. **CSS** (`public/css/lab-investigation-modal.css`) - Styling for the modal

## Features

- **Dual context support** - Works in both patient visit and consultation contexts
- **Patient-specific ordering** - Orders investigations for specific patients and visits
- **Service search** - Real-time search for medical services with autocomplete
- **Current investigations display** - Shows all investigations for the current visit/consultation
- **Priority levels** - Support for routine, urgent, and STAT priorities
- **Dynamic form loading** - Automatically loads required forms for specific investigations
- **Investigation management** - Delete and cancel investigations directly from the modal
- **Real-time updates** - Refreshes investigation list after each action
- **Form tracking integration** - Integrates with consultation form tracking system
- **Toastr notifications** - User-friendly success and error notifications

## Installation

### Step 1: Include the Blade Partial

Add the modal to your view file:

```blade
@extends('layouts.your_layout')

@section('main_content')
    <!-- Your page content -->
    
    <!-- Button to trigger the modal (Visit Context) -->
    <button onclick="openLabModal({{ $patientId }}, {{ $visitId }}, '{{ $patientName }}')" class="btn btn-primary">
        <i class="fas fa-flask"></i> Order Investigation
    </button>
    
    <!-- Button to trigger the modal (Consultation Context) -->
    <button onclick="openLabModal({{ $patientId }}, {{ $consultationId }}, '{{ $patientName }}', 'consultation')" class="btn btn-primary">
        <i class="fas fa-flask"></i> Order Investigation
    </button>
@endsection

{{-- Include the modal component --}}
@include('partials.lab_investigation_modal')
```

### Step 2: Include the CSS

Add the CSS file in your styles section:

```blade
@section('styles')
<link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">
@endsection
```

### Step 3: Include the JavaScript

Add the JavaScript file in your scripts section:

```blade
@section('scripts')
    {{-- Lab Investigation Modal JavaScript --}}
    <script src="{{ asset('js/lab-investigation-modal.js') }}"></script>
    
    {{-- Optional: If using consultation investigation forms --}}
    <script src="{{ asset('js/consultation/investigations.js') }}"></script>
@endsection
```

### Step 3: Include the CSS

Add the CSS file in your extra_footer_content or head section:

```blade
@section('extra_footer_content')
    {{-- Lab Investigation Modal Styles --}}
    <link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">
@endsection
```

## Usage

### Opening the Modal

Call the `openLabModal()` function with three or four parameters:

```javascript
// Visit context (default)
openLabModal(patientId, visitId, patientName);

// Consultation context
openLabModal(patientId, consultationId, patientName, 'consultation');
```

**Parameters:**
- `patientId` (number) - The patient's ID
- `visitId` (number) - The current visit or consultation ID
- `patientName` (string) - The patient's full name (displayed in modal title)
- `context` (string, optional) - Either 'visit' (default) or 'consultation'

### Context Behavior

The modal adapts its behavior based on the context:

**Visit Context ('visit' - default):**
- Save endpoint: `POST /investigations`
- List endpoint: `GET /patient-visits/{id}/investigations-partial`
- No form tracking integration

**Consultation Context ('consultation'):**
- Save endpoint: `POST /consultations/{id}/investigations`
- List endpoint: `GET /consultations/{id}/investigations-partial`
- Calls `markFormAsSaved()` after saving
- Calls `loadInvestigations()` to refresh consultation table

### Example Buttons

```html
<!-- Visit Context -->
<button onclick="openLabModal(123, 456, 'John Doe')" class="btn btn-primary">
    <i class="fas fa-flask"></i> Order Lab Investigation
</button>

<!-- Consultation Context -->
<button onclick="openLabModal(123, 789, 'John Doe', 'consultation')" class="btn btn-warning">
    <i class="fas fa-flask"></i> Order Investigation
</button>
```

### Example in Blade Loop

```blade
@foreach($visits as $visit)
    <!-- Visit Context -->
    <button onclick="openLabModal({{ $visit->patient_id }}, {{ $visit->id }}, {{ json_encode($visit->patient->full_name) }})" 
            class="btn btn-sm btn-primary">
        <i class="fas fa-flask"></i> Lab
    </button>
@endforeach

@foreach($consultations as $consultation)
    <!-- Consultation Context -->
    <button onclick="openLabModal({{ $visit->patient_id }}, {{ $consultation->id }}, {{ json_encode($visit->patientInfo->full_name) }}, 'consultation')" 
            class="btn btn-sm btn-warning">
        <i class="fas fa-flask"></i> Order Investigation
    </button>
@endforeach
```

**Important:** Always use `{{ json_encode($patientName) }}` instead of `'{{ $patientName }}'` to properly escape special characters (apostrophes, commas, quotes) in patient names that could break JavaScript syntax.

### Consultation Integration Requirements

When using the modal in consultation context, ensure:

1. **loadInvestigations() function is defined:**
```javascript
window.loadInvestigations = function() {
    $.ajax({
        url: `/consultations/${window.consultationId}/investigations-partial`,
        method: 'GET',
        success: function(html) {
            $('#investigations-table-container').html(html);
        }
    });
};
```

2. **markFormAsSaved() function is available** (from consultation form tracking system)

3. **Consultation ID is set globally:**
```javascript
window.consultationId = {{ $consultation->id }};
```

## Dependencies

### Required

1. **jQuery** - For DOM manipulation and AJAX
2. **Bootstrap 5** - For modal functionality and styling
3. **Toastr** - For notifications
4. **Font Awesome** - For icons

### Optional

- `js/consultation/investigations.js` - If you need to load investigation forms dynamically

## API Endpoints

The component expects these API endpoints to be available. Endpoints vary by context:

### Visit Context Endpoints

#### 1. Get Patient Category
```
GET /patient-visits/{visitId}/category
```
**Response:**
```json
{
    "category_id": 1
}
```

#### 2. Get Investigations for Visit
```
GET /patient-visits/{visitId}/investigations-partial
```
**Response:**
```json
{
    "success": true,
    "html": "<div>...</div>",
    "count": 3
}
```

#### 3. Create Investigation (Visit)
```
POST /investigations
```
**Payload:**
```
patient_id=123&visit_id=456&medical_service_id=789&priority=routine&notes=...
```
**Response:**
```json
{
    "success": true,
    "message": "Investigation ordered successfully"
}
```

### Consultation Context Endpoints

#### 1. Get Investigations for Consultation
```
GET /consultations/{consultationId}/investigations-partial
```
**Response:** HTML table content

#### 2. Create Investigation (Consultation)
```
POST /consultations/{consultationId}/investigations
```
**Payload:**
```
patient_id=123&medical_service_id=789&priority=routine&notes=...&quantity=1
```
**Response:**
```json
{
    "success": true,
    "message": "Investigation ordered successfully"
}
```

#### 3. Delete Consultation Investigation
```
DELETE /consultations/investigations/{investigationId}
```
**Response:**
```json
{
    "success": true,
    "message": "Investigation removed successfully"
}
```

### Shared Endpoints (Both Contexts)

#### 1. Search Medical Services
```
GET /api/medical-services/search?query={query}&limit={limit}&patient_category_id={categoryId}
```
**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Complete Blood Count",
            "code": "CBC",
            "price": 15000,
            "category": "Hematology",
            "has_pricing": true,
            "requires_form": false,
            "form_type": null
        }
    ]
}
```

#### 2. Delete Investigation (Visit Context)
```
DELETE /investigations/{investigationId}
```
**Response:**
```json
{
    "success": true,
    "message": "Investigation deleted successfully"
}
```

#### 3. Cancel Investigation (Visit Context)
```
PATCH /investigations/{investigationId}/cancel
```
**Response:**
```json
{
    "success": true,
    "message": "Investigation cancelled successfully"
}
```

#### 4. Load Investigation Form (Optional)
```
GET /api/investigation-form/{formType}
```
**Response:** HTML content of the form

## Customization

### Changing Modal Size

The modal uses Bootstrap's `modal-xl` class. You can change it to:
- `modal-sm` - Small
- `modal-lg` - Large
- `modal-xl` - Extra Large (default)
- `modal-fullscreen` - Full Screen

Edit line 7 in `resources/views/partials/lab_investigation_modal.blade.php`:
```blade
<div class="modal-dialog modal-xl">  <!-- Change modal-xl to your preference -->
```

### Customizing Styles

Edit `public/css/lab-investigation-modal.css` to customize:
- Colors
- Fonts
- Spacing
- Dropdown appearance
- Button styles

### Modifying Priority Options

Edit the priority select dropdown in `resources/views/partials/lab_investigation_modal.blade.php`:
```blade
<select class="form-control" name="priority">
    <option value="routine">Routine</option>
    <option value="urgent">Urgent</option>
    <option value="stat">STAT</option>
    <!-- Add more priority levels here -->
</select>
```

### Adding Custom Validation

Add validation logic in `public/js/lab-investigation-modal.js` in the `saveLabInvestigation()` function:
```javascript
function saveLabInvestigation() {
    // Your custom validation
    if (!$('#modal_selected_service_id').val()) {
        toastr.error('Please select a medical service');
        return;
    }
    
    // Add more validation here
    
    // ... rest of the function
}
```

## Functions Reference

### Main Functions

#### `openLabModal(patientId, visitId, patientName)`
Opens the modal and loads patient/visit data.

#### `loadExistingInvestigations(visitId)`
Loads and displays all investigations for the specified visit.

#### `saveLabInvestigation()`
Submits the form and creates a new investigation order.

#### `deleteInvestigation(investigationId)`
Deletes an investigation (with confirmation).

#### `cancelInvestigation(investigationId)`
Cancels an investigation (sets status to cancelled).

### Search Functions

#### `searchModalMedicalServices(query)`
Searches for medical services matching the query.

#### `showModalServiceSuggestions(services)`
Displays the search results dropdown.

#### `hideModalServiceSuggestions()`
Hides the search results dropdown.

### Form Functions

#### `showModalServiceInfo(serviceId, serviceName, ...)`
Displays service information including price and form requirements.

#### `loadFormDisplay(formType)`
Dynamically loads investigation forms via AJAX.

#### `toggleFormDisplay(formType)`
Toggles the visibility of loaded forms.

#### `hideFormTypeInfo()`
Hides form type information and loaded forms.

## Troubleshooting

### Modal doesn't open
- Check that jQuery and Bootstrap are loaded
- Check browser console for errors
- Verify patient_id and visit_id are valid numbers

### Services not loading
- Check `/api/medical-services/search` endpoint is working
- Verify CSRF token is set correctly
- Check browser console for AJAX errors

### Form not submitting
- Verify `/investigations` POST endpoint exists
- Check CSRF token configuration
- Look for validation errors in browser console

### Notifications not showing
- Ensure Toastr library is loaded
- Check Toastr configuration in the JavaScript file

## Examples

### Example 1: In a Patient Details Page

```blade
@extends('layouts.app')

@section('main_content')
    <div class="card">
        <div class="card-header">
            <h3>{{ $patient->full_name }}</h3>
        </div>
        <div class="card-body">
            <!-- Patient information -->
            
            @if($patient->activeVisit)
                <button onclick="openLabModal({{ $patient->id }}, {{ $patient->activeVisit->id }}, '{{ $patient->full_name }}')" 
                        class="btn btn-primary">
                    <i class="fas fa-flask"></i> Order Investigation
                </button>
            @endif
        </div>
    </div>
@endsection

@include('partials.lab_investigation_modal')

@section('scripts')
    <script src="{{ asset('js/lab-investigation-modal.js') }}"></script>
@endsection

@section('extra_footer_content')
    <link rel="stylesheet" href="{{ asset('css/lab-investigation-modal.css') }}">
@endsection
```

### Example 2: In a Visits List

```blade
@foreach($visits as $visit)
    <tr>
        <td>{{ $visit->id }}</td>
        <td>{{ $visit->patient->full_name }}</td>
        <td>{{ $visit->visit_date }}</td>
        <td>
            <button onclick="openLabModal({{ $visit->patient_id }}, {{ $visit->id }}, '{{ $visit->patient->full_name }}')" 
                    class="btn btn-sm btn-warning">
                <i class="fas fa-flask"></i> Lab
            </button>
        </td>
    </tr>
@endforeach
```

## License

This component is part of the Practice1.0 project.

## Support

For issues or questions, please contact the development team.

---

**Last Updated:** February 2026
**Version:** 1.0
