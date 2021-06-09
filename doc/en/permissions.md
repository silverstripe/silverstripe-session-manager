# Managing permissions for sessions

By default, users can only see and invalidate their own sessions. Not even an administrator can see other users' sessions. This is meant to protect the privacy of all users.

Specific projects may wish to allow a some users to view and manage the sessions of other users.

## A note about privacy

Viewing a user's session details can allow you to determine their approximate location at specific times. Before allowing some of your users to manages other users' sessions, it's a good idea to have a conversation with them about the privacy implications.

You should also consider the relevant privacy legislation in the jurisdiction you operate in.

## Customising the permissions for `LoginSession`

`SilverStripe\SessionManager\Models\LoginSession` is the object that tracks the users' sessions. By altering the permission logic on this object, you can allow some users to manage other users' sessions. The two permissions you'll most likely want to change are `canView` and `canDelete`. You can customise `canEdit` and `canCreate` as well, but the use case for doing so is less clear.

### Creating an extension for `LoginSession`

The first step is to create a `DataExtension` that grant some users the ability to hooks into `LoginSession`'s `canView` and `canDelete` methods. This example aligns the permissions on the `LoginSession` to the permission on the Member who owns the `LoginSession`.

Alternatively, you could call `Permission::check()` to validate if the member has a pre-defined CMS permission. If you need even more granular permissions, you can implement a [PermissionProvider](https://docs.silverstripe.org/en/4/developer_guides/security/permissions/#permissionprovider) to define your own custom permissions.

```php
<?php
namespace My\App;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;

class LoginSessionExtension extends DataExtension
{
    /**
     * @param Member $member
     */
    public function canView($member)
    {
        if ($this->getOwner()->Member()->canView($member)) {
            // If you can view a Member, you can also view their sessions.
            // This does not allow you to terminate their session.
            return true;
        };
    }

    /**
     * @param Member $member
     */
    public function canDelete($member)
    {
        if ($this->getOwner()->Member()->canEdit($member)) {
            // If you can edit a Member, you can also log them out of a session.
            // This action is aligned to canDelete, because logging a user out is
            // equivalent to deleting the LoginSession.
            return true;
        };
    }
}
```

### Applying the extension to `LoginSession`

Add this bit of code to your project's YML configuration te enable your extension.

```yml
SilverStripe\SessionManager\Models\LoginSession:
  extensions:
    - My\App\LoginSessionExtension
```
