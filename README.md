# MWGuerra App Design

> **Note: This package is currently in alpha. Breaking changes may occur in future releases as we continue to develop and enhance its functionality.**

The `mwguerra/app-design` package is a powerful tool for Laravel applications, designed to automate the creation of database migrations, resource routes, controllers, requests classes, and basic tests. It also enhances CRUD functionality by integrating new resources into the application's layout menu dynamically.

## Features

- **Automatic Generation**: Create all migrations, controllers, and models from a simple YAML configuration.
- **CRUD Functionality**: Automatically adds CRUD operations to your Laravel application.
- **Menu Integration**: Dynamically add new resources to specified menus based on the configuration.
- **Extensible**: Easily extendible to fit more complex scenarios or additional types of files.

## Dependencies

This package requires the following to function properly:

- Laravel 11.x
- PHP 8.2 or higher

Make sure your environment meets these requirements before installing the package.

## Installation

### Installing the Package

To install the `mwguerra/app-design` package, use Composer. Run the following command in your terminal:

```bash
composer require mwguerra/app-design
```

### Installing Additional Dependencies with Artisan

The `mwguerra/app-design` package can automatically manage additional dependencies required by the generated resources (such as Vue.js components or additional Laravel packages) via an Artisan command. This is particularly useful for integrating UI libraries or other packages that enhance the functionality of the resources you are creating.

To install these dependencies, you can use the following Artisan command provided by the package:

```bash
php artisan app-design:install-dependencies
```

This command also checks the `app-design-resources.yaml` configuration file for any specified dependencies and installs them accordingly. This helps streamline the setup process, ensuring that all necessary libraries and tools are correctly installed and configured without manual intervention.

## Configuration Example

Below is a simple example of the `app-design-resources.yaml` file. This configuration will generate migrations, resource routes, controllers, resources, request classes, basic tests, and integrate new resources into the application menu.

```yaml
resources:
  - name: User
    showInMenu: true
    menuType: main # main or user
    description: Represents an authenticated user of the application
    database:
      tableName: users
      columns:
        - name: id
          type: bigIncrements
        - name: team_id
          type: unsignedBigInteger
          foreignKey: true
        - name: name
          type: string
          validation:
            - required
            - string
            - max:255
        - name: email
          type: string
          unique: true
          validation:
            - required
            - email
            - unique:users
        - name: created_at
          type: timestamp
          nullable: true
        - name: updated_at
          type: timestamp
          nullable: true
      relationships:
        - type: hasMany
          model: Post
          foreignKey: user_id
  - name: Post
    showInMenu: true
    menuType: main # main or user
    database:
      tableName: posts
      description: Represents a post created by an user of the application
      columns:
        - name: id
          type: bigIncrements
        - name: team_id
          type: unsignedBigInteger
          foreignKey: true
        - name: user_id
          type: unsignedBigInteger
          foreignKey: true
          validation:
            - required
            - integer
            - exists:users,id
        - name: title
          type: string
          validation:
            - required
            - string
            - max:255
        - name: body
          type: text
          validation:
            - required
            - string
        - name: created_at
          type: timestamp
          nullable: true
        - name: updated_at
          type: timestamp
          nullable: true
      relationships:
        - type: belongsTo
          model: User
          foreignKey: user_id
        - type: hasMany
          model: Comment
          foreignKey: post_id
        - type: belongsToMany
          model: Tag
          table: post_tag
          foreignPivotKey: post_id
          relatedPivotKey: tag_id
          withPivot:
            - name: assigned_at  # Extra data in pivot table
              type: timestamp
        - type: morphMany
          model: Image
          as: imageable
  - name: Comment
    showInMenu: true
    menuType: main # main or user
    description: Represents a comment created by an user
    database:
      tableName: comments
      columns:
        - name: id
          type: bigIncrements
        - name: team_id
          type: unsignedBigInteger
          foreignKey: true
        - name: post_id
          type: unsignedBigInteger
          foreignKey: true
          validation:
            - required
            - integer
            - exists:posts,id
        - name: body
          type: text
          validation:
            - required
            - string
            - max:1000
        - name: created_at
          type: timestamp
          nullable: true
        - name: updated_at
          type: timestamp
          nullable: true
      relationships:
        - type: belongsTo
          model: Post
          foreignKey: post_id
          relationships:
        - type: morphMany
          model: Image
          as: imageable
  - name: Tag
    showInMenu: true
    menuType: main # main or user
    description: Represents a tag for a post created by an user
    database:
      tableName: tags
      columns:
        - name: id
          type: bigIncrements
        - name: name
          type: string
          unique: true
          validation:
            - required
            - string
            - max:50
            - unique:tags
      relationships:
        - type: belongsToMany
          model: Post
          table: post_tag
          foreignPivotKey: tag_id
          relatedPivotKey: post_id
          withPivot:
            - name: assigned_at  # Extra data in pivot table
              type: timestamp
  - name: Profile
    showInMenu: true
    menuType: main # main or user
    description: Represents a user's profile
    database:
      tableName: profiles
      columns:
        - name: id
          type: bigIncrements
        - name: team_id
          type: unsignedBigInteger
          foreignKey: true
        - name: user_id
          type: unsignedBigInteger
          foreignKey: true
          validation:
            - required
            - integer
            - exists:users,id
        - name: bio
          type: text
          validation:
            - nullable
            - string
            - max:500
        - name: created_at
          type: timestamp
          nullable: true
        - name: updated_at
          type: timestamp
          nullable: true
      relationships:
        - type: belongsTo
          model: User
          foreignKey: user_id
  - name: Image
    description: Represents an image that can be attached to posts or comments
    database:
      tableName: images
      columns:
        - name: id
          type: bigIncrements
        - name: team_id
          type: unsignedBigInteger
          foreignKey: true
        - name: url
          type: string
          storage: local  # Or 's3', 'cloudinary', etc.
          path_prefix: uploads/images # Or adjust as needed
        - name: imageable_id
          type: unsignedBigInteger
        - name: imageable_type
          type: string
        - name: created_at
          type: timestamp
          nullable: true
        - name: updated_at
          type: timestamp
          nullable: true
```

## Usage

To use the package, simply run the artisan command with your YAML configuration file:

```bash
php artisan app-design:process-resources path/to/your/config.yaml
```

This command will read the specified YAML file and perform all the necessary operations to integrate the defined resources into your Laravel application.

## Contributing

We welcome contributions from the community! If you'd like to help improve the `mwguerra/app-design` package, here are a few ways you can get involved:

- **Reporting Bugs**: If you find a bug, please open an issue on our GitHub repository with a detailed description of the bug and the steps to reproduce it. This helps us make the package more stable for everyone.

- **Feature Suggestions**: Have ideas on how to make the package better? Submit an issue with your suggestion. We love to hear from our users and strive to make improvements that benefit the wider community.

- **Submitting Pull Requests**: If you have made an improvement or fixed an issue, you can submit a pull request. Please ensure your code adheres to the existing code style and includes any necessary tests. All contributions are reviewed for quality and compatibility.

### Note on Contributions:

- **No Guaranteed Inclusion**: While we appreciate all community contributions, submitting a pull request or feature suggestion does not guarantee its inclusion into the project. All suggestions will be considered based on their merit and alignment with the project's goals.

- **Licensing**: By contributing to the `mwguerra/app-design` package, you agree that your contributions will be licensed under the same MIT License as the package.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
