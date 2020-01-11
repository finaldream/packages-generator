# packages-generator
Simple packages.json generator for Composer

Converts a directory of archives automagically into a custom Composer repository.

Supports hosting a number of pre-packaged archives and expose them via package.json as repository.

Use-Cases:

* i.e. provide 3rd-party Plugins or Themes to a composer-based Wordpress set-up.
* host any pre-packged archives and generate simple package-definitions.

## Usage

```
PHP: php packages-generator.php [options]
Bin: packages-generator [options]

    --dir=<path>            path of repository
    --ext=<extensions>      Default "zip", overrides extensions (comma-separated)
    --baseurl=<url>         Public URL where the repository will be hosted
    --package-type=<type>   Optional, adds "type" field to package (i.e. "wordpress-plugin")
    --dry-run               Don\'t write packages.json
    --verbose               output generated structure
```

Create a directory containing Zip-files, which will be served as packages.
In order to extract relevant information like name and version, the files needs to be named in a certain way:

```
name@version.zip

i.e. special-wordpress-plugin@1.2.3.zip
i.e. namespace/special-wordpress-plugin@1.2.3.zip
```

package can be name-spaced by putting them into folders.

## Implementation

Possible ways to implement this script

### Manual
* manage the archives locally, run the script manually and upload everything via FTP

### Cronjob
* Upload files to a Webhost
* generate packages.json via cronjob

### CI Pipeline

* create CI pipeline on your provider of choice
* manage the archives via git
* run the pipeline on git-push
* generate packages.json
* publish to S3