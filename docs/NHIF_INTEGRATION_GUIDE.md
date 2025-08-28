# NHIF Integration Guide for Practice1.0

## Overview
This guide explains how to use the integrated NHIF (National Health Insurance Fund) module in your hospital management system.

## Features Implemented

### 1. NHIF Member Verification
- Verify NHIF card status and member details
- Store member information in local database
- Track verification history

### 2. Claims Management
- Create and submit claims to NHIF
- Track claim status and responses
- Automated claim data building from patient visits

### 3. Tariffs Synchronization
- Download and sync NHIF tariffs
- Handle excluded services
- Support for multiple facility codes

### 4. Pre-approved Services Verification
- Verify pre-approved services
- Reference number validation

## Configuration

### 1. Environment Variables
Add the following to your `.env` file:

```env
# NHIF Configuration
NHIF_USERNAME=your_nhif_username
NHIF_PASSWORD=your_nhif_password
NHIF_MODE=test  # or 'production'
NHIF_FACILITY_CODE=your_facility_code
```

### 2. Test Mode vs Production Mode
- **Test Mode**: Uses NHIF test servers (http://196.13.105.15/nhifservice/breeze/)
- **Production Mode**: Uses NHIF production servers (https://verification.nhif.or.tz/nhifservice/breeze/)

## How to Use

### 1. Accessing NHIF Dashboard
Navigate to: `/nhif` in your application

### 2. Verifying NHIF Members
1. Go to NHIF Dashboard
2. Enter NHIF card number
3. Select the patient from dropdown
4. Choose visit type (Outpatient/Inpatient/Emergency)
5. Click "Verify Member"

### 3. Synchronizing Tariffs
1. Go to NHIF Dashboard
2. Enter your facility code
3. Click "Sync Tariffs"
4. Wait for synchronization to complete

### 4. Managing Claims
Claims are automatically created from patient visits and can be submitted through the NHIF interface.

## API Endpoints

### Member Verification
```
POST /nhif/verify-member
Parameters: card_number, patient_id, visit_type_id, remarks
```

### Card Details
```
POST /nhif/get-card-details
Parameters: card_number
```

### Sync Tariffs
```
POST /nhif/sync-tariffs
Parameters: facility_code
```

### Submit Claim
```
POST /nhif/submit-claim
Parameters: patient_visit_id
```

## Database Tables

### nhif_members
Stores NHIF member information and verification history.

### nhif_claims
Stores claim information and submission status.

### nhif_claim_items
Stores individual claim items (services, medications).

### nhif_claim_diseases
Stores diagnosis information for claims.

### nhif_tariffs
Stores NHIF tariff information for different services.

## Integration with Existing Models

### Patient Model
- Added `nhifMember()` relationship
- Added `nhifClaims()` relationship
- Added `hasActiveNhifMembership()` method
- Added `getNhifCardNumberAttribute()` accessor

## Error Handling
- All NHIF API calls include proper error handling
- Errors are logged to Laravel logs
- User-friendly error messages are displayed
- Network timeouts and retry mechanisms are implemented

## Security Considerations
- NHIF credentials are stored in environment variables
- All API calls use HTTPS in production mode
- User authentication is required for all NHIF operations
- Audit trail is maintained for all NHIF operations

## Troubleshooting

### Common Issues

1. **Connection Timeout**
   - Check internet connectivity
   - Verify NHIF servers are accessible
   - Increase timeout in config if needed

2. **Invalid Credentials**
   - Verify NHIF_USERNAME and NHIF_PASSWORD in .env
   - Contact NHIF for credential validation

3. **Invalid Facility Code**
   - Verify your facility is registered with NHIF
   - Check facility code format

4. **Card Not Found**
   - Verify card number format
   - Check if card is active in NHIF system

## Next Steps

### Recommended Enhancements
1. Add automated claim generation from patient visits
2. Implement claim status checking
3. Add referral management
4. Create detailed reports for NHIF claims
5. Add bulk member verification
6. Implement claim reconciliation

### Customization
The NHIF service can be customized to match your specific workflow by:
1. Modifying the `buildClaimData()` method in NhifController
2. Adding custom validation rules
3. Extending the models with additional fields
4. Creating custom reports and dashboards

## Support
For technical support or questions about the NHIF integration, please refer to:
- Laravel documentation: https://laravel.com/docs
- NHIF API documentation (provided by NHIF)
- This project's documentation

## Version Information
- Laravel NHIF Integration: Custom implementation for Laravel 12
- Compatible with NHIF API version: Current
- Last updated: July 2025
