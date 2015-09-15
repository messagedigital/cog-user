# Changelog

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