# Silverstripe CMS Session Manager

Allow users to manage and revoke access to multiple login sessions across devices.

**This module is in development, and is not ready for use in production**

![CMS view](images/cms.png)

## How it works

The module introduces a new database record type: `LoginSession`.
On first login, it creates a new record of this type, recording the IP and User-Agent,
and associates it with the user (via `LogInAuthenticationHandler`).
The record identifier is stored in the PHP session, so it can be retrieved on subsequent requests.

On each request, a middleware (`LoginSessionMiddleware`) checks if the current
PHP session is pointing to a valid `LoginSession` record.
If a valid record is found, it will update the `LastAccessed` date.
Otherwise, it will force a logout, destroying the PHP session.

A periodic process (`GarbageCollectionService`) cleans up expired `LoginSession` records.
Due to the way PHP sessions operate, it can not expire those sessions as well.
The PHP sessions will be invalidated on next request through `LoginSessionMiddleware`,
unless they expire independently beforehand (through PHP's own session expiry logic).

Silverstripe allows persisting login state via a "remember me" feature.
These `RememberLoginHash` records have their own expiry date.
This module associates them to `LoginSession` records,
and ensures their expiry is consistent with the new session behaviour
(see "Configuration" below for details).

The `LoginSession` tracks the IP address and user agent making the requests
in order to make different sessions easier to identify in the user interface.
It does not use changes to this metadata to invalidate sessions.

## Compatibility

The module should work independently of the storage mechanism used for PHP sessions (file-based sticky sessions, file-based sessions on a shared filesystem, [silverstripe/dynamodb](https://github.com/silverstripe/silverstripe-dynamodb), [silverstripe/hybridsessions](https://github.com/silverstripe/silverstripe-hybridsessions)).

It is also compatible with the [Silverstripe MFA module suite](https://github.com/silverstripe/silverstripe-mfa).

## Caveats

 * Every request with a logged-in user causes a database write (updating `LoginSession`), potentially affecting performance
 * Restoring a database from an older snapshot will invalidate current sessions.
 * PHP sessions can become out of sync with `LoginSession` objects. Both can exist beyond their expiry date.
   This is not an issue in practice since the association between the two is checked on each session-based request
   (through `LoginSessionMiddleware`).

## Configuration

### Logout across devices

This module respects the `SilverStripe\Security\RememberLoginHash.logout_across_devices` config setting, which defaults to `true`. This means that the default behaviour is to revoke _all_ a user’s sessions when they log out.

To change this so that logging out will only revoke the session for that one device, use the following config setting:

```yml
SilverStripe\Security\RememberLoginHash:
  logout_across_devices: false
```

**Important:** do not set this value to false if users do not have access to the CMS (or a custom UI where they can revoke sessions). Doing so would make it impossible to a user to revoke a session if they suspect their device has been compromised.

### Session timeout

Non-persisted login sessions (those where the user hasn’t ticked “remember me”) should expire after a period of inactivity, so that they’re removed from the list of active sessions even if the user closes their browser without completing the “log out” action. The length of time before expiry matches the `SilverStripe\Control\Session.timeout` value if one is set, otherwise falling back to a default of one hour. This default can be changed via the following config setting:

```yml
SilverStripe\SessionManager\Model\LoginSession:
  default_session_lifetime: 3600 # Default value: 1 hour in seconds
```

Note that if the user’s session expires before this timeout (e.g. a short `session.gc_maxlifetime` PHP ini setting), they **will** still be logged out. There will just be an extra session shown in the list of active sessions, even though no one can access it.

### Garbage collection

By default, this module will trigger garbage collection of expired sessions once in every 50 requests (approximately, using `mt_rand()`). It’s advised to disable this method of garbage collection and instead set up a cron task which points to `dev/tasks/LoginSessionGarbageCollectionTask`.

The default garbage collection probability can be adjusted by changing the `GarbageCollectionMiddleware.probability` config setting:

```yml
SilverStripe\SessionManager\Control\GarbageCollectionMiddleware:
  probability: 100 # Triggers garbage collection for 1 in every ~100 requests
```

The default garbage collection can be disabled by disabling `GarbageCollectionMiddleware`:

```yml
---
After: '#session-manager-middleware'
---
SilverStripe\Core\Injector\Injector:
  SilverStripe\Control\Director:
    properties:
      Middlewares:
        GarbageCollectionMiddleware: null
```

## To-do

- Privacy warning (storing IP/User-Agent - GDPR)
- More manual testing
- Unit test coverage
