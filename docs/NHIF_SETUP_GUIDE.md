# NHIF Integration Setup Guide

## Overview
This guide will help you complete the setup and configuration of the NHIF (National Health Insurance Fund) integration for your Practice1.0 hospital management system.

## Prerequisites
- Laravel 12.18.0+ (already installed)
- MySQL Database (already configured)
- PHP 8.1+ (already available)
- Internet connection for NHIF API calls

## 🚀 Quick Setup Steps

### 1. Environment Configuration

Add the following NHIF configuration to your `.env` file:

```env
# NHIF Configuration
NHIF_USERNAME=your_nhif_username
NHIF_PASSWORD=your_nhif_password
NHIF_FACILITY_CODE=your_facility_code
NHIF_PRACTITIONER_NO=your_practitioner_number
NHIF_MODE=test
```

**Replace the placeholder values with your actual NHIF credentials:**
- `your_nhif_username`: Your NHIF API username
- `your_nhif_password`: Your NHIF API password  
- `your_facility_code`: Your healthcare facility code from NHIF
- `your_practitioner_number`: Your medical practitioner number

### 2. Database Migration

Run the NHIF database migrations:

```bash
php artisan migrate
```

This will create the following tables:
- `nhif_members` - NHIF member information
- `nhif_claims` - Claims submitted to NHIF
- `nhif_claim_items` - Individual items in claims
- `nhif_claim_diseases` - Diseases associated with claims
- `nhif_tariffs` - NHIF service tariffs

### 3. Test the Integration

Run the NHIF integration test command:

```bash
# Basic connectivity test
php artisan nhif:test

# Test member verification with specific card
php artisan nhif:test --card=12345678901

# Test tariff synchronization
php artisan nhif:test --sync-tariffs

# Verify member only (skip other tests)
php artisan nhif:test --card=12345678901 --verify-only
```

### 4. Access the NHIF Module

1. **Admin Dashboard**: Navigate to `/admin` in your browser
2. **NHIF Menu**: Look for "NHIF Management" in the sidebar
3. **Available Sections**:
   - **Member Verification**: Verify NHIF member details
   - **Claims Management**: Create and submit claims
   - **Tariff Sync**: Synchronize service tariffs
   - **Reports & Analytics**: View NHIF reports and statistics

## 📋 Module Features

### Member Verification
- Real-time NHIF member verification
- Card number validation
- Member details display
- Automatic member record creation

### Claims Management  
- Create new claims for patient visits
- Add claim items (consultations, procedures, medications)
- Submit claims to NHIF
- Track claim status
- Bulk claim operations

### Tariff Synchronization
- Sync service tariffs from NHIF
- View current tariff rates
- Filter and search tariffs
- Export tariff data

### Reports & Analytics
- Claims statistics and trends
- Revenue analytics
- Member verification reports
- Custom date range reports
- Export capabilities (PDF, Excel, CSV)

## 🔧 Configuration Options

### Test vs Production Mode

In your `config/nhif.php`:

```php
'mode' => env('NHIF_MODE', 'test'), // 'test' or 'production'
```

**Test Mode**: Uses NHIF test environment
**Production Mode**: Uses live NHIF API

### API Timeout Settings

```php
'timeout' => 30, // seconds
'retry_attempts' => 3,
```

### Logging

NHIF operations are logged to:
- `storage/logs/laravel.log`
- Look for entries with "NHIF" prefix

## 🗂️ File Structure

```
app/
├── Console/Commands/
│   └── NhifIntegrationTest.php     # Test command
├── Http/Controllers/
│   └── NhifController.php          # Main controller
├── Models/
│   ├── NhifMember.php             # Member model
│   ├── NhifClaim.php              # Claim model
│   ├── NhifClaimItem.php          # Claim items
│   ├── NhifClaimDisease.php       # Claim diseases
│   └── NhifTariff.php             # Tariffs
└── Services/
    └── NhifService.php             # Core API service

config/
└── nhif.php                        # NHIF configuration

database/migrations/
├── *_create_nhif_members_table.php
├── *_create_nhif_claims_table.php
├── *_create_nhif_claim_items_table.php
├── *_create_nhif_claim_diseases_table.php
└── *_create_nhif_tariffs_table.php

resources/views/nhif/
├── verify.blade.php                # Member verification
├── claims.blade.php               # Claims management
├── tariffs.blade.php              # Tariff sync
└── reports.blade.php              # Reports & analytics

routes/
└── web.php                         # NHIF routes added
```

## 🔗 API Endpoints

The following routes are available:

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/nhif` | NHIF main dashboard |
| GET | `/nhif/verify` | Member verification page |
| POST | `/nhif/verify-member` | Verify NHIF member |
| GET | `/nhif/claims` | Claims management page |
| POST | `/nhif/create-claim` | Create new claim |
| POST | `/nhif/submit-claim/{id}` | Submit claim to NHIF |
| GET | `/nhif/tariffs` | Tariffs page |
| POST | `/nhif/sync-tariffs` | Synchronize tariffs |
| GET | `/nhif/reports` | Reports page |
| POST | `/nhif/generate-report` | Generate custom report |
| POST | `/nhif/quick-report` | Generate quick report |
| GET | `/nhif/export-tariffs` | Export tariffs CSV |
| GET | `/nhif/export-claims` | Export claims CSV |

## 🛠️ Troubleshooting

### Common Issues

**1. "NHIF configuration not found"**
- Ensure `config/nhif.php` exists
- Check `.env` file has NHIF variables
- Run `php artisan config:cache`

**2. "Database connection failed"**
- Verify database is running
- Check database credentials in `.env`
- Run migrations: `php artisan migrate`

**3. "Member verification failed"**
- Verify NHIF credentials are correct
- Check if in test/production mode
- Ensure internet connectivity

**4. "View not found"**
- Ensure all view files exist in `resources/views/nhif/`
- Check file permissions
- Clear view cache: `php artisan view:clear`

### Debug Commands

```bash
# Clear all caches
php artisan optimize:clear

# Check NHIF configuration
php artisan config:show nhif

# Test database connection
php artisan tinker
>>> App\Models\NhifMember::count()

# View logs
tail -f storage/logs/laravel.log | grep NHIF
```

## 📞 Support

For technical support or questions:

1. Check the application logs: `storage/logs/laravel.log`
2. Run the test command: `php artisan nhif:test`
3. Verify configuration: `php artisan config:show nhif`

## 🔄 Next Steps

1. **Configure NHIF Credentials**: Update `.env` with real credentials
2. **Test Integration**: Use the test command to verify connectivity
3. **Train Users**: Familiarize staff with the NHIF module interface
4. **Customize Workflows**: Adapt claim building logic to your needs
5. **Monitor Performance**: Check logs and system performance

## 📈 Future Enhancements

Potential improvements that can be added:
- Automated claim status checking
- Bulk member verification
- Real-time notification system
- Advanced reporting dashboards
- Mobile-responsive interface
- Integration with billing system

---

**Note**: This integration is designed to work with the official NHIF API. Ensure you have valid NHIF credentials and API access before going live.
