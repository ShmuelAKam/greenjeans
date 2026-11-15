# GreenJeans

A PHP web application for interacting with the Geni.com API to manage, query, and synchronize genealogical data with a local relational database.

It is divided into two parts:
1. An OOD designed SDK, API wrapper, with classes for all of the API objects such as media files (Document, Photo, Video), Profiles, Projects, etc, etc, etc.
2. A persistence layer, for saving these objects. Supports relevant CRUD operations. Currently supports Sqlite and MySql.

The SDK can be used independently of the DB layer.

## Local Configuration

Before running the application, create your local configuration files from the provided examples in the project root:

cp db.local.php.example   db.local.php
cp geni.local.php.example geni.local.php

db.local.php
Holds your local or production database connection settings (host, database name, username, and password). Edit this file with your MySQL/MariaDB credentials.

geni.local.php
Holds your Geni API application credentials (client ID, client secret, and redirect URI (if relevant)). Edit this file with your registered Geni app details.

Note: Both *.local.php files are excluded from Git via .gitignore and will not be overwritten during deployments.

## Getting Started

the Api/Auth directory contains php files for the three types of authentication that Geni uses. Copy the relevant file to your app directory, and adjust the definition of PACKAGE_ROOT.

