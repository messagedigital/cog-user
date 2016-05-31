# Changelog

## 2.2.0

- Make users deletable
- Add `User\Delete` class for deleting users
- Add `user.delete` service which returns instance of `User\Delete`
- Add `deleted_by` and `deleted_at` columns to `user` table

## 2.1.0

- `Edit::setGroups()` can take an array of `GroupInterface` instances as well as string representations of groups (i.e. group names)
- `Edit::setGroups()` will throw a `LogicException` if a variable in te array is neither a string nor an instance of `GroupInterface`
- Added missing `$password` property to `User` object

## 2.0.3

- Check user in session is an instance of `User\UserInterface` before returning via the service container
- Fix issue where check if user is anonymous did not call correct class
- Check user is instance of `User\User` when validating user hash

## 2.0.2

- Removed useless 'Keep me logged in' checkbox from login form

## 2.0.1

- Fire event on user creation

## 2.0.0

- Initial open source release