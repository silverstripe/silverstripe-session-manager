title: Session Manager
summary: Allow members to manage and revoke access to multiple login sessions across devices.

# What does the session manager module do?

The session manager module allows members to manage (see active and revoke) login sessions across devices used to access the CMS. Each login to a member's account is tracked and can be managed from their profile page.

# How to use it

## Getting started

Make sure you have the [Silverstripe CMS Session Manager](https://addons.silverstripe.org/add-ons/silverstripe/session-manager) module installed on your website.

## Logging in

When logging in with "Keep me signed in for 30 days" selected, a session will remain active on that device for the full 30 days unless it is terminated prior to that allocated timeframe. Without this selected a session will only last for xyz hours.

[notice]
You should not use the "Keep me signed in" functionality when working on a device shared with other users (e.g.: internet café computer or a public library workstation).
[/notice]

![Silverstripe CMS log in form](_images/logging-in.png)

## Viewing login sessions

In order to view login sessions once logged in, navigate to your profile by clicking on your name in the left hand CMS menu. Every valid and currently active login session will be listed under `Login sessions`.



![Viewing login sessions](_images/viewing-login-sessions.png)

There is various data associated with every login session that can be used to identify the device that is logged in:

* Browser
* Operating System
* IP Address
* Last active time
* Sign in time

[notice]
Note: Members can only view access for their own profile. Nobody else will have access to view your sessions.
[/notice]

## Revoking access

To remove access for a session associated with a device, click the *_Log out_* link next to the session you want to remove. This session will be immediately removed and anyone viewing the CMS using this session will need to log back in.

![Confirmation screen for revoking a login session](_images/revoking-login-sessions.png)

[notice]
A member can only revoke access for their own profile. No one else will have access to remove your sessions.
[/notice]

## Login session expiry

Login sessions have an expiry of 30 days by default. After this, the session will no longer be valid and the user must log in again. Once a session has expired it will be removed from the list of active login sessions.
