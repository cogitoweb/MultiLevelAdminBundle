# README

## Introduction

CogitowebMultiLevelAdminBundle is a SonataProject Admin extension with
multi-level capability.
[SonataAdminBundle][1] allows only 1 nesting level for child Admins:
`/admin/app/parent/{id}/child/{childId}/action`.
CogitowebMultiLevelAdminBundle goes beyond this limit and handles virtually
infinite (actually 10) nesting levels:
`/admin/app/first/{id}/second/{firstChildId}/.../nth/{nthChildId}/action`.

An important difference between Sonata and Cogitoweb approach is that the former
creates the child Admin as an instance of the main one, while the latter
considers each Admin indipendent.
Such behaviour reflects on routes first, and on Admins hierachy awareness
consequently.

An option provided by CogitowebMultiLevelAdminBundle is the RESTful Admin API,
which makes basic CRUD actions available to pages under the Admin firewall.

### Important notes

* **Beware** that CogitowebMultiLevelAdminBundle overrides some
SonataAdminBundle services
* CogitowebMultiLevelAdminBundle uses Admin name to generate routes, instead of
entity class name

## Installation

Add the repository in `repositories` section of your project's `composer.json`,
so that composer becomes aware of CogitowebMultiLevelAdminBundle existance

```json
    ...
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/cogitoweb/MultiLevelAdminBundle"
        }
    ],
    ...
```

And install the package

```
$ composer require cogitoweb/multi-level-admin-bundle
```

## Enable bundle

Like all other bundles, to enable CogitowebMultiLevelAdminBundle add it in `app/AppKernel.php`, along with its dependencies

```php
            ...
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
			new FOS\RestBundle\FOSRestBundle(), // Optional: needed by RESTful Admin API
	        new Cogitoweb\MultiLevelAdminBundle\CogitowebMultiLevelAdminBundle(),
            ...
```

## Configuration

Although CogitowebMultiLevelAdminBundle does not need a custom configuration,
a `config.yml` file is provided to simplify SonataAdminBundle configuration with default values.
Given file can be imported in `app/config/config.yml` main configuration

```yaml
imports:
    ...
    - { resource: "@CogitowebMultiLevelAdminBundle/Resources/config/config.yml" }
    - { resource: "@CogitowebMultiLevelAdminBundle/Resources/config/restful_admin_api_config.yml" } # Optional: needed by RESTful Admin API
```

If you are interested in RESTful Admin API option, enable the path in `app/config/security.yml`

```yaml
    access_control:
        ...
        - { path: ^/api/admin/, role: [ROLE_ADMIN, ROLE_SONATA_ADMIN] }
```

## Routing

Like configuration, also a routing `routing.yml` file with SonataAdmin routing is provided
and can be imported in `app/config/routing.yml` main routing

```yaml
...
cogitoweb_multi_level_admin:
    resource: "@CogitowebMultiLevelAdminBundle/Resources/config/routing.yml"

# Optional: needed by RESTful Admin API
cogitoweb_restful_admin_api:
    resource: "@CogitowebMultiLevelAdminBundle/Resources/config/restful_admin_api_routing.yml"
    type:     rest
```

## Clear cache

System is almost ready. Just perform a clear cache

```
$ php app/console cache:clear
```

## Usage

To configure an Admin as child, call the `addParent` method with `[ "@parent_admin_service_id" ]` as argument
in your Admin service definition and set the `$parentAssociationMapping` parameter properly in your Admin class.

```yaml
# src/AppBundle/Resources/config/services.yml

    app.admin.child:
        class: AppBundle\Admin\ChildAdmin
        arguments: [~, AppBundle\Entity\Child, SonataAdminBundle:CRUD]
        tags:
            - {name: sonata.admin, manager_type: orm, group: admin, label: Child}
        calls:
            - [ addParent, [ "@app.admin.parent" ] ]
```

```php
// src/AppBundle/Admin/ChildAdmin.php

<?php
namespace AppBundle\Admin;
use       Cogitoweb\MultiLevelAdminBundle\Admin\AbstractAdmin;

class ChildAdmin extends AbstractAdmin
{
	protected $parentAssociationMapping = 'parent';
	...
}
```

### RESTful Admin API

Take a look at `api_admin_*` routes: *_sonata_admin* is the code of the Admin
you want to query.

[1]: https://sonata-project.org/bundles/admin/master/doc/index.html