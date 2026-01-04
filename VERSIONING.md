# Brain Client Versioning

This document explains how Brain client versioning works and how to ensure your
application stays up to date.

## Version Format

Brain client uses **semantic versioning** (MAJOR.MINOR.PATCH):

- **MAJOR**: Breaking changes (rare)
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

Example: `1.0.0` → `1.0.1` (patch), `1.1.0` (minor), `2.0.0` (major)

## Current Version

The current client version is defined in `BrainEventClient::CLIENT_VERSION`.
Check the client file or use:

```php
use App\Services\BrainEventClient;

$client = new BrainEventClient(...);
$version = $client->getVersion();
```

## Version Tracking

### Automatic Tracking

The Brain client automatically includes its version in the
`X-Brain-Client-Version` header with every event sent. Brain tracks:

- Which version each project is using
- When each version was last seen
- Version history over time

### Viewing Your Version

In Brain admin:

1. Navigate to **Projects**
2. Find your project
3. Check the **Client Version** column
   - Green badge = up to date
   - Yellow badge = update available

## Checking for Updates

### Programmatic Check

```php
use App\Services\BrainEventClient;

$client = app(BrainEventClient::class);
$versionInfo = $client->checkVersion();

if ($versionInfo && $versionInfo['update_required']) {
    // Log warning or notify developers
    Log::warning('Brain client update available', $versionInfo);
}
```

### API Endpoint

You can also check directly:

```bash
curl https://again.com.au/api/v1/client/version \
  -H "X-Brain-Client-Version: 1.0.0"
```

Response:

```json
{
    "latest_version": "1.0.1",
    "current_version": "1.0.0",
    "update_required": true
}
```

## Updating the Client

### Method 1: Git Repository (Recommended)

If you installed via Git repository or submodule:

```bash
# Update the client files
cd brain-nucleus
git pull origin main

# Or re-run install script
./install.sh
```

### Method 2: Manual Copy

1. Download latest `BrainEventClient.php` from:
   https://github.com/iamjasonhill/thebrain/blob/main/brain-client/BrainEventClient.php

2. Replace your existing file:
   ```bash
   cp BrainEventClient.php app/Services/BrainEventClient.php
   ```

3. Update namespace if needed:
   ```php
   namespace App\Services;
   ```

### Method 3: Composer

If using Composer path repository:

```bash
composer update brain-nucleus/client
```

## Version Update Checklist

After updating the client:

1. ✅ **Verify version** - Check `BrainEventClient::CLIENT_VERSION` matches
   latest
2. ✅ **Test event sending** - Send a test event and verify it appears in Brain
3. ✅ **Check version tracking** - Verify your project shows the new version in
   Brain admin
4. ✅ **Review changelog** - Check for any breaking changes or new features

## Migration Guide

### Breaking Changes

If a major version update includes breaking changes:

1. Review the changelog or release notes
2. Update your code to match new API
3. Test thoroughly in staging
4. Deploy to production

### Non-Breaking Updates

For minor/patch updates:

1. Update client files
2. Test basic functionality
3. Deploy (usually safe)

## Best Practices

### 1. Check on Startup

Add version check to your application startup:

```php
// In AppServiceProvider or similar
public function boot(): void
{
    if (app()->environment('production')) {
        $client = app(BrainEventClient::class);
        $versionInfo = $client->checkVersion();
        
        if ($versionInfo && $versionInfo['update_required']) {
            Log::warning('Brain client update available', $versionInfo);
            // Optionally notify developers via email/Slack
        }
    }
}
```

### 2. Scheduled Checks

Create a scheduled task to check periodically:

```php
// In routes/console.php or a command
Schedule::call(function () {
    $client = app(BrainEventClient::class);
    $versionInfo = $client->checkVersion();
    
    if ($versionInfo && $versionInfo['update_required']) {
        // Send notification to team
    }
})->daily();
```

### 3. Monitor in Brain Admin

Regularly check the Projects page in Brain admin to see:

- Which projects are using outdated clients
- When clients were last seen
- Version distribution across projects

## Troubleshooting

### Version Not Updating

If Brain admin shows an old version:

1. Verify the client file has the correct version constant
2. Send a test event and check the `X-Brain-Client-Version` header
3. Check Brain logs for version tracking

### Update Check Fails

If `checkVersion()` returns null:

1. Verify Brain API is accessible
2. Check network connectivity
3. Review Brain logs for errors

### Version Mismatch

If your code shows one version but Brain shows another:

1. Clear any caches (opcache, Laravel cache)
2. Verify you're using the correct client file
3. Check for multiple client files in your codebase

## Related Documentation

- **[README.md](README.md)** - Installation and quick start guide
- **[INTEGRATION-GUIDE.md](INTEGRATION-GUIDE.md)** - Integration examples
