# User

The `Message\User` cogule provides a simple user and permission system.

## Services

**todo: list services defined here**

## Events

The following events are fired by this cogule. The event object is always an instance of `Message\User\Event\Event`.

* **user.login.attempt**: fired when a login attempt is made (only if it passes basic validation), regardless of whether or not the user exists. This event is an instance of `Message\User\Event\LoginAttemptEvent`.
* **user.login**: fired when a user successfully logs in.
* **user.logout**: fired when a user successfully logs out.
* **user.password.request**: fired when a password reset request is made, only if the user exists.
* **user.password.reset**: fired when a user successfully resets their password.
	* Note that if the password was reset via the "forgotten password" system, the user is logged in after this event is fired and the **user.login** event is then fired.
* **user.create** fired when a user is created using the `Message\User\Create` decorator.
* **user.edit** fired when a user is edited using the `Message\User\Edit` decorator.
	* Note this event is not fired when:
		* The "password request time" is updated or cleared.
		* The "last login time" is updated.
		* The user is added to or removed from a group
* **user.email_confirmed** fired when a user confirms their email address (if required)
* **user.group.add** fired when a user is added to a group using `Message\User\Edit::addToGroup()`
* **user.group.remove** fired when a user is removed from a group using `Message\User\Edit::removeFromGroup()`

## Users

A user has the following properties:

* An ID
* An email address
* An "email confirmed" flag
* A title (e.g. Mr or Mrs)
* A forename
* A surname
* A "last login" timestamp
* A "password requested at" timestamp

And the usual metadata for creation and updating. There is no metadata for deletion because when a user is deleted they are hard deleted: they no longer exist in the database.

If your application or module needs to add more properties to a user, it is recommended to group these properties in a way that makes sense, and create a new model representing that group of properties. The `Message\User\User` object should remain a simple representation of basic user information.

For example, in an e-commerce module, address data might be stored for users. In this case, it would make sense to create a model representing a user's address, like so:

	class UserAddress
	{
		public $user;
		
		public $typeID;
		
		public $addressLines;
		public $town;
		public $postcode;
		public $countryID;
		public $stateID;
		public $telephone;
	}

## Groups

A user group is, of course, a simple way of grouping users. The `Message\User` cogule doesn't define any groups itself, but it provides groups functionality and a framework for any other cogule to define groups.

To define a group, first you need to create a class that implements `Message\User\Group\GroupInterface`:

	<?php
	
	namespace Message\MyModule;
	
	use Message\User\Group;
	
	class SuperAdmin implements Group\GroupInterface
	{
		/**
		 * {@inheritdoc}
		 */
		public function getName()
		{
			return 'super-admin';
		}
	
		/**
		 * {@inheritdoc}
		 */
		public function getDisplayName()
		{
			return 'Super Administrators';
		}
	
		/**
		 * {@inheritdoc}
		 */
		public function getDescription()
		{
			return 'Users with access to everything!';
		}
	
		/**
		 * {@inheritdoc}
		 */
		public function registerPermissions(Group\Permissions $permissions)
		{
			// ...
		}
	}

Then you just need to add it to the `user.groups` service definition, which is a collection of user groups available to the system. Any user group that is not added to this collection is not "known" to the system.

Ideally, groups should be added to the collection on the `modules.load.success` event. Add an event listener like this to your cogule's events bootstrap:

	$dispatcher->addListener('modules.load.success', function() use ($services) {
		$services['user.groups']
			->add(new Message\MyModule\SuperAdmin);
	});


## Permissions

A simple permissions system is included in this cogule that is built around user groups and route collections or routes.

Every group must implement the `registerPermissions()` method that is defined on the `Message\User\Group\GroupInterface` interface. The argument passed in to this method is an instance of `Message\User\Group\Permissions` for the appropriate group.

Within `registerPermissions()`, a set of permissions can be registered for the group. A group can be granted permission to any of the following:

* All routes within a specific route collection.
* A specific route.
* A specific route with specific parameter values.

Here's an example of all three:

	public function registerPermissions(Permissions $permissions)
	{
		// Grants the group access to all routes in the 'my.collection' route collection
		$permissions
			->addRouteCollection('my.collection');
		
		// Grants the group access to the `homepage` and `secret.page` route
		$permissions
			->addRoute('homepage')
			->addRoute('secret.page');
		
		// Grants the group access to the `file.view` route only when the `type` parameter equals "image"
		$permissions
			->addRoute('file.view', array('type' => 'image'));
	}

Once any group defines a permission for a route or a route collection, that route or route collection becomes "protected", making it unavailable unless the current user is in a group that has access to the route collection or specific route.

If the current user does not the appropriate permissions to access a protected route, an `Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException` is thrown.

## Controllers & Routes

**todo: write about the controllers available and the routes, and how to use them in an app**

## License

Mothership E-Commerce
Copyright (C) 2015 Jamie Freeman

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
