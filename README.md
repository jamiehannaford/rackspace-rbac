# RBAC scripts for Rackspace cloud

Included are a bunch of scripts that will allow you to quickly and easily provision API users with temporary roles. 
This is useful when you're hosting a hackathon or user group and need to give folks temporary access to Rackspace 
APIs. Scripts have been written common tasks - and are available to use in `./scripts`.

# Installation

If you do not have Composer:

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin
```

Then you will need to install this repo:

```bash
git clone https://github.com/jamiehannaford/rackspace-rbac
composer install --no-dev
```

# Setup

You will need to have `RS_USERNAME` and `RS_API_KEY` set as environment variables.

# Listing all available roles

Edit and run `./scripts/list-roles`. It will list the name and ID of every single role available to sub-users.

# Batch creating users

Edit and run `./scripts/create-users`. It will create `total` amount of users with a `prefix`, and assign them all to 
 the role IDs specified. So if the prefix is "foo" and the total is 3, these users will be created: `foo_1`, `foo_2`, 
 `foo_3`.

# Batch deleting users

Edit and run `./scripts/delete-user`. It will delete all users that match the `prefix`.