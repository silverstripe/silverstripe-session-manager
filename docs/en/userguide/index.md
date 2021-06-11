title: Session Manager
summary: Allow members to manage and revoke access to multiple login sessions across devices.

# What does the session manager module do?

The session manager module allows members to manage and revoke access to multiple login sessions across devices. Each login to a member is tracked and can be viewed from the profile page. Logins can also be revoked from here.

# How to use it

## Getting started

Make sure you have the [SilverStripe Session Manager](https://addons.silverstripe.org/add-ons/silverstripe/session-manager) module installed

## Logging in

When you log in with the module installed, a login session record will be created at the time of the login and associated with your current session.

![Logging in](_images/logging-in.png)

## Viewing login sessions

In order to view login sessions you have to go to your profile after logging in. Every currently valid login session will be listed under `Sessions`.

![Viewing login sessions](_images/viewing-login-sessions.png)

There is various data associated with every login session that can be used to identify the device that is logged in:

* Browser
* Operating System
* IP Address
* Last Active time
* Sign in time

[notice]
Note: Members can only view access for their own profile. Nobody else will have access to view your sessions.
[/notice]

## Revoking access

To remove access for a session associated with a device, click the `Log Out` link next to the session you want to remove. You will then be prompted to confirm the deletion to avoid accidentally removing access:

![Revoking login sessions](_images/revoking-login-sessions.png)

Once confirmed the login session will be removed and the session / device associated with it will no longer be logged in and will thus have to log back in.

[notice]
Note: Members can only revoke access for their own profile. Nobody else will have access to remove your sessions.
[/notice]

## Expiry

Login sessions have an expiry of 90 days by default. After this, the login will no longer be valid and the user must log in again. Only valid sessions are visible when viewing your sessions.
