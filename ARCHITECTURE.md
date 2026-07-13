# Feature Flags - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                       │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────┐      ┌──────────────────┐            │
│  │  Registration    │      │     Login        │            │
│  │  Controller      │      │  Controller      │            │
│  └────────┬─────────┘      └────────┬─────────┘            │
│           │                         │                       │
│           ▼                         ▼                       │
│  ┌──────────────────────────────────────────┐              │
│  │  Feature Flag Check                      │              │
│  │  config('features.email_verification')   │              │
│  └──────────┬─────────────────────┬─────────┘              │
│             │                     │                        │
│    FALSE    │                     │    TRUE               │
│             ▼                     ▼                        │
│  ┌──────────────────┐  ┌──────────────────┐              │
│  │  Auto-Verify     │  │  Send Email      │              │
│  │  User Email      │  │  Notification    │              │
│  └──────────────────┘  └──────────────────┘              │
│             │                     │                        │
│             └──────────┬──────────┘                        │
│                        ▼                                   │
│             ┌──────────────────────┐                      │
│             │  Login User          │                      │
│             │  Auth::login($user)  │                      │
│             └──────────┬───────────┘                      │
│                        ▼                                   │
│             ┌──────────────────────┐                      │
│             │  Redirect Dashboard  │                      │
│             └──────────┬───────────┘                      │
│                        ▼                                   │
│             ┌──────────────────────┐                      │
│             │  VerifyEmailFeature  │                      │
│             │  Flag Middleware     │                      │
│             └──────────┬───────────┘                      │
│                        │                                   │
│             FALSE      │      TRUE                        │
│                        │                                   │
│        ┌───────────────┼────────────────┐                 │
│        ▼               ▼                ▼                 │
│  ┌──────────┐  ┌───────────────┐ ┌─────────────┐        │
│  │ ALLOW    │  │ Check Email   │ │ DENY/       │        │
│  │ ACCESS   │  │ Verified?     │ │ REDIRECT    │        │
│  └──────────┘  └───────┬───────┘ └─────────────┘        │
│                        │                                   │
│          ┌─────────────┼─────────────┐                   │
│          ▼             ▼             ▼                   │
│       ALLOW         REDIRECT     REDIRECT                │
│     DASHBOARD     VERIFICATION    NOTICE                 │
│                                                           │
└─────────────────────────────────────────────────────────────┘
```

---

## Development Flow (Feature Disabled)

```
┌─────────────────────────────────────────────────────────────┐
│                  DEVELOPMENT MODE                            │
│            FEATURE_EMAIL_VERIFICATION=false                 │
└─────────────────────────────────────────────────────────────┘

  1. User Registration
     │
     ├─ Validate Input
     │
     ├─ Create User (email_verified_at = NULL)
     │
  2. Auto-Verify User
     │
     ├─ Set email_verified_at = NOW()
     │
  3. Fire Registered Event
     │
     ├─ SendEmailVerificationNotification Listener
     │  └─ Check: config('features.email_verification')
     │     └─ FALSE → Return (Skip Email) ✅
     │
  4. Auth::login($user)
     │
  5. Redirect to Dashboard
     │
  6. Access Protected Route
     │
     ├─ VerifyEmailFeatureFlag Middleware
     │  └─ Check: config('features.email_verification')
     │     └─ FALSE → Allow Access ✅
     │
  7. Dashboard Accessible
     │
     └─ ✅ SUCCESS
        • No email sent
        • No SMTP errors
        • No verification needed
        • User immediately productive
```

---

## Production Flow (Feature Enabled)

```
┌─────────────────────────────────────────────────────────────┐
│                  PRODUCTION MODE                             │
│            FEATURE_EMAIL_VERIFICATION=true                  │
└─────────────────────────────────────────────────────────────┘

  1. User Registration
     │
     ├─ Validate Input
     │
     ├─ Create User (email_verified_at = NULL)
     │
  2. Check Verify User (Not needed in production)
     │
     ├─ Skip auto-verify (Feature Enabled)
     │
  3. Fire Registered Event
     │
     ├─ SendEmailVerificationNotification Listener
     │  └─ Check: config('features.email_verification')
     │     └─ TRUE → Send Email ✅
     │
  4. Auth::login($user)
     │
  5. Redirect to Dashboard
     │
  6. Try to Access Protected Route
     │
     ├─ VerifyEmailFeatureFlag Middleware
     │  └─ Check: config('features.email_verification')
     │     └─ TRUE → Check email_verified_at
     │        └─ NULL → Redirect to Verification Notice ✅
     │
  7. User Sees Verification Notice
     │
     ├─ Option 1: Click Link in Email
     │  └─ VerifyEmailController Verifies Email
     │     └─ email_verified_at = NOW()
     │
     ├─ Option 2: Request New Email
     │  └─ EmailVerificationNotificationController
     │     └─ Send Email Again
     │
  8. Click Verified Link
     │
     ├─ Verify Email Marked
     │
     ├─ Redirect to Dashboard
     │
  9. Access Protected Route Again
     │
     ├─ VerifyEmailFeatureFlag Middleware
     │  └─ Check: email_verified_at IS NOT NULL
     │     └─ TRUE → Allow Access ✅
     │
  10. Dashboard Accessible
      │
      └─ ✅ SUCCESS
         • Email verification required
         • Security enforced
         • User properly verified
```

---

## Component Interaction Diagram

```
                          ┌─────────────────┐
                          │  .env            │
                          │  FEATURE_EMAIL   │
                          │  _VERIFICATION   │
                          └────────┬─────────┘
                                   │
                          ┌────────▼────────┐
                          │  config/        │
                          │  features.php   │
                          └────────┬────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    │              │              │
                    ▼              ▼              ▼
         ┌──────────────────┐ ┌─────────────┐ ┌─────────────┐
         │ RegisteredUser   │ │ Verification│ │ VerifyEmail │
         │ Controller       │ │ Prompt      │ │ Feature     │
         └────────┬─────────┘ │ Controller  │ │ Flag        │
                  │           └──────┬──────┘ │ Middleware  │
                  │                  │        └──────┬──────┘
                  ▼                  ▼               ▼
         ┌──────────────────┐ ┌─────────────┐ ┌─────────────┐
         │ Registered       │ │ Email       │ │ User Model  │
         │ Event            │ │ Verification│ │ email_      │
         │ Dispatched       │ │ Notification│ │ verified_at │
         └────────┬─────────┘ │ Controller  │ └─────────────┘
                  │           └──────┬──────┘
                  │                  │
                  ▼                  ▼
         ┌──────────────────────────────────┐
         │ SendEmailVerification            │
         │ Notification Listener            │
         │ (Feature Flag Aware)             │
         └────────────┬─────────────────────┘
                      │
          ┌───────────┴───────────┐
          │                       │
   FALSE  ▼              TRUE     ▼
    ┌──────────┐        ┌──────────────┐
    │ Skip     │        │ Send Email   │
    │ Email    │        │ Notification │
    └──────────┘        └──────────────┘
```

---

## Middleware Decision Tree

```
┌─────────────────────────────────────────────────┐
│  VerifyEmailFeatureFlag Middleware              │
│  (Replaces Laravel's 'verified' Middleware)     │
└─────────────────────────────────────────────────┘
           │
           ▼
    ┌──────────────┐
    │   Request    │
    │  Comes In    │
    └──────┬───────┘
           │
           ▼
    ┌──────────────────────────────┐
    │ Check Feature Flag:          │
    │ config('features.            │
    │ email_verification')         │
    └──────┬──────────┬────────────┘
           │          │
       FALSE│         │TRUE
           │          │
           ▼          ▼
    ┌──────────┐  ┌────────────────┐
    │ ALLOW    │  │ Check User's   │
    │ ACCESS   │  │ email_verified │
    │ (Dev)    │  │ _at            │
    └──────────┘  └────┬───────────┘
                       │
             ┌─────────┴─────────┐
             │                   │
           NULL              NOT NULL
             │                   │
             ▼                   ▼
       ┌──────────────┐  ┌──────────┐
       │ REDIRECT to  │  │ ALLOW    │
       │ verification │  │ ACCESS   │
       │ .notice      │  │ (Prod)   │
       └──────────────┘  └──────────┘
```

---

## Event Listener Flow

```
┌──────────────────────────────────────────────┐
│  Registered Event Dispatched                 │
│  (User Registration Completed)               │
└──────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────┐
│  SendEmailVerificationNotification Listener  │
│  Receives: $event->user                      │
└──────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────┐
│  Check: config('features.email_verification')
└──────────────────────────────────────────────┘
           │
      ┌────┴────┐
      │          │
   FALSE      TRUE
      │          │
      ▼          ▼
   ┌──────┐  ┌─────────────────────┐
   │Return│  │$event->user         │
   │(Skip)│  │->sendEmailVerify    │
   └──────┘  │NotificationNotify() │
             └─────────────────────┘
                   │
                   ▼
             ┌──────────────┐
             │ Email Queued │
             │ or Sent      │
             └──────────────┘
```

---

## Configuration Hierarchy

```
┌─────────────────────────────────────────┐
│  Application Configuration Hierarchy    │
└─────────────────────────────────────────┘

  1. .env (Runtime Configuration)
     │
     ├─ FEATURE_EMAIL_VERIFICATION=false|true
     ├─ APP_ENV=local|production
     ├─ MAIL_MAILER=log|smtp
     └─ ... other env vars

         │
         ▼
  2. config/features.php (Feature Flags)
     │
     ├─ Uses env() helper to read from .env
     ├─ Provides intelligent defaults
     └─ Makes values available via config()

         │
         ▼
  3. Middleware & Controllers (Usage)
     │
     ├─ Check: config('features.xxx')
     ├─ Make decisions based on flag
     └─ Execute appropriate logic

         │
         ▼
  4. Application Behavior
     │
     ├─ Development: Features disabled
     ├─ Production: Features enabled
     └─ Can toggle anytime via .env
```

---

## State Transitions

```
                       USER REGISTRATION

  ┌─────────────────────────────────────────────────┐
  │         email_verified_at = NULL                │
  │           (User Created)                        │
  └─────────────────────────────────────────────────┘
                       │
      ┌────────────────┼────────────────┐
      │                │                │
  DEV │            PROD │                │
  FLAG│            FLAG │                │
  OFF │             ON  │                │
      │                │                │
      ▼                ▼                ▼
 ┌────────────┐  ┌──────────────┐
 │ Auto-      │  │ Wait for     │
 │ Verify     │  │ Email Click  │
 │ (Mark as   │  │ (Pending)    │
 │ Verified)  │  └──────┬───────┘
 └────────────┘         │
      │                 │
      │          User clicks link
      │                 │
      │                 ▼
      │          ┌──────────────┐
      │          │ Mark as      │
      │          │ Verified     │
      │          └──────┬───────┘
      │                 │
      └────────┬────────┘
               │
               ▼
    ┌──────────────────────────┐
    │ email_verified_at = NOW()│
    │ (User Verified)          │
    │ Can Access Dashboard     │
    └──────────────────────────┘
```

---

## Environment Decision Matrix

```
┌──────────────────────────────────────────────────────────────┐
│              Environment Decision Matrix                     │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  APP_ENV  │  FEATURE_FLAG  │  Behavior                      │
│  ─────────┼────────────────┼─────────────────────────────── │
│           │                │                                │
│  local    │  false         │  ✅ Dev Mode                   │
│           │                │  • No email verification       │
│           │                │  • Auto-login                  │
│           │                │  • Dashboard immediate         │
│           │                │  • No SMTP required            │
│           │                │                                │
│  local    │  true          │  ✅ Test Production           │
│           │                │  • Email verification on       │
│           │                │  • SMTP must be configured     │
│           │                │  • Verification required       │
│           │                │                                │
│  prod     │  (ignored)     │  ✅ Production                │
│           │  true (default)│  • Email verification enabled  │
│           │                │  • SMTP must be configured     │
│           │                │  • Full security enabled       │
│           │                │                                │
└──────────────────────────────────────────────────────────────┘
```

---

## File Dependencies

```
┌─────────────────────────────────────────┐
│       .env.example                      │
│  (Documentation & Template)             │
└──────────────────┬──────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────┐
│    config/features.php                  │
│  (Feature Flag Configuration)           │
└──────────────────┬──────────────────────┘
                   │
        ┌──────────┼──────────┐
        │          │          │
        ▼          ▼          ▼
    Bootstrap  Controllers  Middleware
    /app.php   /Auth/*      /Verify*
        │          │          │
        ├──────────┼──────────┤
        │                     │
        ▼                     ▼
   AppServiceProvider    VerifyEmailFeature
   (Event Listener)      Flag (Middleware)
        │                     │
        ▼                     ▼
   SendEmailVerification  Checks config()
   Notification Listener   on each request
        │
        ▼
   Checks config()
   on user registration
```

---

**Legend**:
- ✅ = Implementation Complete
- → = Execution Flow
- ▼ = Direction/Next Step
- | = Connection/Relationship

