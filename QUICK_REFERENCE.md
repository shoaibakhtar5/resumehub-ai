# Quick Reference - Feature Flags

## 🚀 Quick Start

### Development Mode (Local)
```bash
# In .env
APP_ENV=local
FEATURE_EMAIL_VERIFICATION=false
```

**Result**: 
- ✅ No email verification required
- ✅ Users automatically logged in
- ✅ Dashboard immediately accessible
- ✅ No SMTP needed

### Production Mode
```bash
# In .env
APP_ENV=production
FEATURE_EMAIL_VERIFICATION=true
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
```

**Result**:
- ✅ Email verification required
- ✅ Verification emails sent
- ✅ Protected routes enforced
- ✅ Full Laravel security

---

## 📝 Using Feature Flags in Code

### Check a Feature Flag
```php
// In any controller, middleware, service, etc.
if (config('features.email_verification')) {
    // Feature is enabled
}

// Shorthand for specific features
if (config('features.ai')) {
    // AI features available
}
```

### Add New Feature Flag

**Step 1**: Add to `config/features.php`
```php
'my_new_feature' => env('FEATURE_MY_NEW_FEATURE', true),
```

**Step 2**: Add to `.env.example`
```bash
FEATURE_MY_NEW_FEATURE=true
```

**Step 3**: Use in code
```php
if (config('features.my_new_feature')) {
    // Your feature logic
}
```

---

## 📂 File Reference

| File | Purpose |
|------|---------|
| `config/features.php` | Feature flag definitions |
| `app/Http/Middleware/VerifyEmailFeatureFlag.php` | Verified middleware |
| `app/Listeners/SendEmailVerificationNotification.php` | Email sending listener |
| `app/Providers/AppServiceProvider.php` | Event listener registration |
| `.env.example` | Feature flag documentation |
| `FEATURE_FLAGS.md` | Comprehensive guide |
| `IMPLEMENTATION_SUMMARY.md` | Complete implementation details |

---

## 🔄 Registration Flow

### Development (`FEATURE_EMAIL_VERIFICATION=false`)
```
Register → Auto-Verify → Login → Dashboard ✅
```

### Production (`FEATURE_EMAIL_VERIFICATION=true`)
```
Register → Send Email → User Verifies → Login → Dashboard ✅
```

---

## 🧪 Quick Tests

### Test 1: Register in Development
```bash
# Set: FEATURE_EMAIL_VERIFICATION=false
# Expected: Dashboard accessible immediately
```

### Test 2: Register in Production
```bash
# Set: FEATURE_EMAIL_VERIFICATION=true, SMTP configured
# Expected: Verification email sent
```

### Test 3: Toggle Feature
```bash
# Start: FEATURE_EMAIL_VERIFICATION=false (register user)
# Change: FEATURE_EMAIL_VERIFICATION=true
# Expected: New users need verification, old users still work
```

---

## 🛠️ Common Tasks

### Disable Email Verification Temporarily
```bash
# In .env
FEATURE_EMAIL_VERIFICATION=false
```

### Enable Email Verification
```bash
# In .env
FEATURE_EMAIL_VERIFICATION=true
```

### Add New Feature Flag
```bash
# 1. config/features.php
'payment_gateway' => env('FEATURE_PAYMENT_GATEWAY', true),

# 2. .env.example
FEATURE_PAYMENT_GATEWAY=true

# 3. Use in code
if (config('features.payment_gateway')) { }
```

### Check Current Configuration
```bash
# In .env, look for:
FEATURE_EMAIL_VERIFICATION=false  # Current setting
APP_ENV=local                      # Environment
```

---

## ⚠️ Important Notes

1. **No Code Changes for Deployment**: Just update `.env`
2. **Always Use `config()` Helper**: Never hardcode env() calls
3. **Document Feature Flags**: Add comments explaining behavior
4. **Test Both States**: Test with flag enabled and disabled
5. **Reversible**: Can toggle anytime without code changes

---

## 📞 Troubleshooting

| Issue | Solution |
|-------|----------|
| Emails still being sent in dev | Verify `FEATURE_EMAIL_VERIFICATION=false` in .env |
| Can't access dashboard | Check if email_verified_at is NULL in production |
| Routes not working | Run `php artisan route:cache` and `php artisan route:clear` |
| Feature flag not working | Ensure config is cached: `php artisan config:cache` |

---

## 📚 Related Files

- **Full Guide**: `FEATURE_FLAGS.md`
- **Implementation Details**: `IMPLEMENTATION_SUMMARY.md`
- **Configuration**: `config/features.php`
- **Environment Template**: `.env.example`

---

**Last Updated**: July 9, 2026  
**Status**: ✅ Ready for Use
