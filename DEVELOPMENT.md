# GovCMS Development Guide

This guide is intended for developers who want to set up a local development environment for the GovCMS project on
GitHub. The following instructions will guide you through the process of setting up a local environment, including
installing and configuring necessary software and dependencies.

## Prerequisites

Before you can start setting up your local development environment for GovCMS, you must have the following software
installed on your machine:

- Git
- Composer
- Docker (Optional)
- Docker Compose (Optional)

If you do not have any of these software installed, please follow the instructions provided by the software provider to
install them.

## Setup

- Via Composer
- Via Docker Compose

Follow the below steps to set up your local development environment for GovCMS:

### Via Composer

1. Clone the GovCMS project from GitHub by running the following command:

    ```console
    git clone -b 3.x-develop git@github.com:GovCMS/GovCMS.git
    ```

2. Navigate to the cloned project directory:

    ```console
    cd govcms
    ```

3. Install project dependencies using Composer:

    ```console
    composer update
    ```

### Via Docker Compose

1. Clone the GovCMS project from GitHub by running the following command:

    ```console
    git clone -b 3.x-develop git@github.com:GovCMS/GovCMS.git
    ```

2. Navigate to the cloned project directory:

    ```console
    cd govcms
    ```

3. Copy the auth.json.example file to auth.json (Optional only for Composer and GitHub API rate limit ):

    ```console
    cd .docker
    cp auth.json.example auth.json
    vi auth.json
    ```

4. Start the Docker containers by running the following command:

    ```console
    cd govcms
    docker compose up -d
    ```

5. The website should now be running at http://localhost:8888. You can access the website by opening this URL in your
   browser.

## Running Tests

TBD

## Contributing

We welcome contributions from the community. To contribute, please follow the below steps:

1. Fork the GovCMS project repository from GitHub.
2. Clone your forked repository to your local machine.
3. Create a new branch for your feature or bug fix.
4. Make your changes and commit them to your local branch.
5. Push your changes to your forked repository on GitHub.
6. Submit a pull request to the main GovCMS repository.

## Conclusion

That's it! You now have a fully-functional local development environment for the GovCMS project. Happy coding!
