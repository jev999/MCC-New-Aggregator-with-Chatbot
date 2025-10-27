# Admin Geolocation Tracking Feature

## Overview
This feature adds geolocation tracking to the admin access logs, allowing super admins to view the geographic location from where admins are logging in. The system captures location data based on IP addresses and displays them on interactive maps.

## What Was Added

### 1. Database Changes
- **Migration**: `2025_01_15_000001_add_geolocation_to_admin_access_logs_table.php`
  - Added `latitude` (decimal, 10, 8)
  - Added `longitude` (decimal, 11, 8)
  - Added `location_details` (text)

### 2. Model Updates
- **AdminAccessLog Model**: Added `latitude`, `longitude`, and `location_details` to fillable array

### 3. Geolocation Service
- **New File**: `app/Services/GeolocationService.php`
  - Uses ip-api.com (free tier) to get geolocation from IP addresses
  - Handles local IPs gracefully
  - Provides formatted location details (City, Region, Country)

### 4. Controller Updates
Updated all admin authentication controllers to capture geolocation data on login:
- `UnifiedAuthController.php` - Captures geolocation for all admin types
- `SuperAdminAuthController.php` - Super admin logins
- `AdminAuthController.php` - Department admin logins (legacy)
- `DepartmentAdminAuthController.php` - Department admin logins
- `OfficeAdminAuthController.php` - Office admin logins

Each controller now includes:
```php
$geoData = $this->getGeolocationData($request->ip());
AdminAccessLog::create([
    // ... other fields
    'latitude' => $geoData['latitude'] ?? null,
    'longitude' => $geoData['longitude'] ?? null,
    'location_details' => $geoData['location_details'] ?? null,
]);
```

### 5. View Updates
- **access_logs.blade.php**: Enhanced to display location information
  - Added "Location" column to the access logs table
  - Shows location details text
  - Includes "View Map" button for locations with coordinates
  - Integrated Leaflet.js for interactive maps
  - Modal popup to display location on map

## Features

### Location Capture
- Automatically captures location when admins log in (both successful and failed attempts)
- Uses IP address to determine approximate location
- Handles local development IPs gracefully
- Falls back gracefully if geolocation service is unavailable

### Map Display
- Interactive maps using Leaflet.js
- Shows exact coordinates
- Displays admin name and location details in popup
- Modal interface for easy viewing
- Click outside to close

### Location Details Format
The `location_details` field contains formatted location information:
- Example: "Manila, Metro Manila, Philippines"
- Fallback to "Local network" for local IPs
- "Location unavailable" if geolocation fails

## How It Works

1. **On Admin Login**: When an admin attempts to log in, the system:
   - Captures their IP address
   - Calls GeolocationService to get location from IP
   - Stores latitude, longitude, and formatted location details in the database

2. **In Access Logs View**: Super admins can:
   - See location text for each access attempt
   - Click "View Map" button to see location on an interactive map
   - View exact coordinates
   - See admin name and location details

3. **Map Integration**: The map modal:
   - Displays a marker at the admin's location
   - Shows popup with admin name and location details
   - Provides zoom controls
   - Click outside to close

## Usage

### For Super Admins
1. Navigate to Admin Access Logs (https://mcc-nac.com/superadmin/admin-access)
2. View the "Location" column in the access logs table
3. Click "View Map" button next to any location
4. View the admin's location on an interactive map
5. Close the modal by clicking the X button or clicking outside

### What You'll See
- **Location Details**: City, Region, Country (e.g., "Manila, Metro Manila, Philippines")
- **View Map Button**: Blue button that opens the interactive map
- **Map Modal**: Shows marker, popup with details, and coordinates
- **Unavailable**: Shows "Location unavailable" if geolocation data is not available

## API Used

### ip-api.com
- **Service**: Free tier IP geolocation API
- **Endpoint**: `http://ip-api.com/json/{ip}`
- **Limits**: 
  - 45 requests per minute for free tier
  - No API key required
- **Response**: City, Region, Country, Latitude, Longitude

### Leaflet.js
- **Library**: Open-source interactive maps
- **Version**: 1.9.4
- **Tiles**: OpenStreetMap
- **Features**: Markers, Popups, Zoom controls

## Privacy Considerations

1. **IP-based Location**: Only approximate location (city/region level)
2. **No Real-time Tracking**: Only captured at login time
3. **Admin Access Only**: Only super admins can view location data
4. **Local Development**: Local IPs are handled gracefully without exposing data

## Configuration

No additional configuration is required. The service works out of the box.

### Optional: Customize Geolocation Service
You can replace ip-api.com with another service by modifying `app/Services/GeolocationService.php`:

```php
// Replace with your preferred service
$response = Http::timeout(3)->get("https://your-service.com/api/{$ip}");
```

## Notes

- Geolocation is approximate and based on IP address
- Some users may appear to log in from different locations (VPN, mobile networks, etc.)
- Location data is stored for security and audit purposes
- Failed login attempts also capture location for security monitoring

## Testing

To test the feature:
1. Log in as any admin type
2. View the Admin Access Logs as a super admin
3. Check that your location appears in the "Location" column
4. Click "View Map" to verify the map displays correctly
5. Verify that the location details are shown accurately

## Migration

To apply the database changes, run:

```bash
php artisan migrate
```

Or apply the specific migration:

```bash
php artisan migrate --path=database/migrations/2025_01_15_000001_add_geolocation_to_admin_access_logs_table.php
```

## Files Modified

1. `database/migrations/2025_01_15_000001_add_geolocation_to_admin_access_logs_table.php` (NEW)
2. `app/Models/AdminAccessLog.php` (MODIFIED)
3. `app/Services/GeolocationService.php` (NEW)
4. `app/Http/Controllers/UnifiedAuthController.php` (MODIFIED)
5. `app/Http/Controllers/SuperAdminAuthController.php` (MODIFIED)
6. `app/Http/Controllers/AdminAuthController.php` (MODIFIED)
7. `app/Http/Controllers/DepartmentAdminAuthController.php` (MODIFIED)
8. `app/Http/Controllers/OfficeAdminAuthController.php` (MODIFIED)
9. `resources/views/superadmin/access_logs.blade.php` (MODIFIED)

