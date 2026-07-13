# Feature Flags Implementation Guide

## Overview

This application uses environment-based feature flags to control functionality without changing code. The system is designed to work seamlessly between development and production environments.

## How It Works

### Configuration

All feature flags are defined in `config/features.php` and read from `.env` variables:

```php
// Usage in your code
config('features.email_verification')  // Returns true/false
config('features.ai')                   // Returns true/false
```

### Environment Defaults

- **Development** (`APP_ENV=local`): Email verification **disabled** by default
- **Production** (`APP_ENV=production`): Email verification **enabled** by default

You can override any default by setting the flag explicitly in `.env`.

---

## Email Verification Feature Flag

### Development Mode (FEATURE_EMAIL_VERIFICATION=false)

When disabled, the system:
- ✅ Skips email verification after registration
- ✅ Automatically logs users in
- ✅ Redirects directly to dashboard
- ✅ Does NOT send verification emails
- ✅ Does NOT require SMTP configuration
- ✅ Allows login without email verification

### Production Mode (FEATURE_EMAIL_VERIFICATION=true)

When enabled, the system:
- ✅ Sends verification emails after registration
- ✅ Requires email verification before accessing protected routes
- ✅ Shows verification notice to unverified users
- ✅ Enforces verified middleware on protected routes

---

## Implementation Details

### Modified Components

1. **`config/features.php`** - Feature flag configuration
2. **`app/Http/Middleware/VerifyEmailFeatureFlag.php`** - Custom verified middleware
3. **`app/Listeners/SendEmailVerificationNotification.php`** - Custom event listener
4. **`app/Providers/AppServiceProvider.php`** - Event registration
5. **`app/Http/Controllers/Auth/RegisteredUserController.php`** - Auto-verify on registration
6. **`app/Http/Controllers/Auth/EmailVerificationNotificationController.php`** - Respect feature flag
7. **`app/Http/Controllers/Auth/EmailVerificationPromptController.php`** - Redirect if disabled
8. **`bootstrap/app.php`** - Register custom middleware
9. **`.env.example`** - Feature flag documentation

### Laravel Components NOT Modified

- ✅ User model still implements `MustVerifyEmail`
- ✅ Verification routes still exist
- ✅ Verification middleware still works
- ✅ All framework functionality preserved

---

## Registration Flow

### Development (FEATURE_EMAIL_VERIFICATION=false)

```
User Registers
    ↓
Auto-Verify Email (email_verified_at = now())
    ↓
Fire Registered Event
    ↓
Custom Listener Checks Flag (Skips Email)
    ↓
Automatically Login
    ↓
Redirect to Dashboard
    ↓
Access Granted (Verified Middleware Allows)
```

### Production (FEATURE_EMAIL_VERIFICATION=true)

```
User Registers
    ↓
Create User (email_verified_at = NULL)
    ↓
Fire Registered Event
    ↓
Custom Listener Checks Flag (Sends Email)
    ↓
Verification Email Sent
    ↓
Automatically Login (to temp session)
    ↓
Try to Access Protected Route
    ↓
Verified Middleware Redirects to Verification Notice
    ↓
User Clicks Link in Email
    ↓
Email Marked as Verified
    ↓
Access Granted
```

---

## Login Flow

### Development (FEATURE_EMAIL_VERIFICATION=false)

```
User Provides Credentials
    ↓
Authentication Check (Passes)
    ↓
VerifyEmailFeatureFlag Middleware
    ↓
Flag Disabled → Allow Access
    ↓
Dashboard Accessible
```

### Production (FEATURE_EMAIL_VERIFICATION=true)

```
User Provides Credentials
    ↓
Authentication Check (Passes)
    ↓
VerifyEmailFeatureFlag Middleware
    ↓
Check email_verified_at (NULL)
    ↓
Redirect to Verification Notice
    ↓
User Verifies Email
    ↓
Dashboard Accessible
```

---

## Configuration Examples

### Development Setup (.env)

```bash
APP_ENV=local
APP_DEBUG=true
FEATURE_EMAIL_VERIFICATION=false

# No SMTP needed in development
MAIL_MAILER=log
```

### Production Setup (.env)

```bash
APP_ENV=production
APP_DEBUG=false
FEATURE_EMAIL_VERIFICATION=true

# SMTP configured
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
MAIL_FROM_ADDRESS=noreply@resumehub.com
```

---

## Available Feature Flags

All flags are documented in `config/features.php`:

| Flag | Description | Default |
|------|-------------|---------|
| `email_verification` | Email verification requirement | Local: false, Prod: true |
| `ai` | AI-powered features | true |
| `payments` | Payment processing | true |
| `blog` | Blog functionality | true |
| `analytics` | Analytics tracking | true |
| `social_login` | Social authentication | true |
| `otp_login` | OTP authentication | true |
| `notifications` | Notification system | true |
| `ats_checker` | ATS checker tool | true |
| `resume_templates` | Resume templates | true |

---

## Testing the Feature Flag

### Test Case 1: Development Mode (Feature Disabled)

```bash
# Set in .env
APP_ENV=local
FEATURE_EMAIL_VERIFICATION=false

# Expected behavior:
# 1. Register new user
# 2. User auto-logs in
# 3. Dashboard immediately accessible
# 4. No verification email sent
# 5. No SMTP errors
```

### Test Case 2: Production Mode (Feature Enabled)

```bash
# Set in .env
APP_ENV=production
FEATURE_EMAIL_VERIFICATION=true
MAIL_MAILER=smtp  # SMTP configured

# Expected behavior:
# 1. Register new user
# 2. User auto-logs in temporarily
# 3. Verification email sent
# 4. Protected routes redirect to verification notice
# 5. User clicks link in email
# 6. Email verified, dashboard accessible
```

### Test Case 3: Toggle Feature Flag

```bash
# Start in development
APP_ENV=local
FEATURE_EMAIL_VERIFICATION=false

# Register user → Dashboard works

# Switch to production
FEATURE_EMAIL_VERIFICATION=true

# Login with same user → Redirected to verification (since email_verified_at is NULL)
# OR manually verify: $user->markEmailAsVerified()
# Then login works normally
```

---

## Middleware Flow

The custom `VerifyEmailFeatureFlag` middleware replaces Laravel's built-in `verified` middleware:

```php
// In routes/web.php
Route::get('/dashboard', [...])
    ->middleware(['auth', 'verified'])  // Uses custom middleware
    ->name('dashboard');
```

### Middleware Logic

```php
if (!config('features.email_verification')) {
    // Development: Allow all authenticated users
    return $next($request);
}

// Production: Enforce verification
if ($user->email_verified_at === null) {
    return redirect()->route('verification.notice');
}

return $next($request);
```

---

## Event Listener Flow

The custom `SendEmailVerificationNotification` listener respects feature flags:

```php
// When user registers:
event(new Registered($user));

// Listener checks:
if (!config('features.email_verification')) {
    return;  // Skip sending email in development
}

// Production: Send verification email
$event->user->sendEmailVerificationNotification();
```

---

## Reversibility Guarantee

This implementation is **100% reversible through .env only**:

1. **No code deleted** - All Laravel components intact
2. **No code commented out** - No "uncomment when needed" code
3. **No hard deletions** - Migration and controller files unchanged
4. **Pure config-based** - Feature flags control behavior

To restore default Laravel behavior:
- Set `FEATURE_EMAIL_VERIFICATION=true`
- Configure SMTP
- Application works with default verification system

---

## Adding New Feature Flags

To add a new feature flag:

1. Add to `config/features.php`:
   ```php
   'new_feature' => env('FEATURE_NEW_FEATURE', true),
   ```

2. Add to `.env.example`:
   ```bash
   FEATURE_NEW_FEATURE=true
   ```

3. Use in code:
   ```php
   if (config('features.new_feature')) {
       // Feature logic
   }
   ```

---

## Best Practices

1. **Always use config() helper** - Never hardcode environment checks
2. **Document feature flags** - Add comments explaining behavior
3. **Test both states** - Test with flag enabled and disabled
4. **Use meaningful names** - `FEATURE_X` clearly indicates a feature flag
5. **Default wisely** - Different defaults for dev vs production
6. **Avoid duplication** - Create helper methods for complex logic
7. **Keep listeners simple** - One responsibility per listener

---

## Troubleshooting

### Emails Still Being Sent in Development

**Solution**: Verify `FEATURE_EMAIL_VERIFICATION=false` in `.env` and restart the application.

### Can't Access Dashboard After Login

**Possible causes**:
- Feature flag is enabled but SMTP not configured
- User's `email_verified_at` is NULL in production
- Middleware not registered properly

**Solution**: Check `.env`, verify SMTP config, manually verify user if needed.

### Routes Not Working

**Possible causes**:
- Changes in `routes/auth.php` not reloaded
- Middleware registration failed

**Solution**: Clear route cache: `php artisan route:cache` and `php artisan route:clear`

---

## Summary

This feature flag system provides:
- ✅ Clean separation of development and production behavior
- ✅ No code changes needed when deploying
- ✅ Fully reversible through .env
- ✅ Scalable for future features
- ✅ Professional production-ready implementation
- ✅ SOLID principles and Laravel best practices
