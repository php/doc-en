
# PHP Documentation

Please refer to the
[contribution guidelines](https://github.com/php/doc-base/blob/master/docs/contributing.md)
and the [README file](https://github.com/php/doc-base/blob/master/README.md)
within the [doc-base repository](https://github.com/php/doc-base)
for details on how to contribute to PHP's documentation.

The PHP manual is available at [php.net/docs](https://php.net/docs).

## Creating this setup

For information related to creating this setup,
see the [contribution guidelines](https://github.com/php/doc-base/blob/master/docs/contributing.md)
or [this page](https://doc.php.net/tutorial/local-setup.php) on our documentation website.

## Building With make and Docker

- Install Docker (https://docs.docker.com/get-docker/)
- Rebuild the documentation using `make`
- Open output/php-chunked-xhtml/ in your browser.

If the `doc-base` or `phd` repositories are available in directories to the
adjacent to this directory, those will be used for building.

To force the Docker image used for building to itself be rebuilt, you can run
`make -B build`, otherwise the `Makefile` will only build it if does not
already exist.

You can also build the `web` version of the documentation with `make php`
and the output will be placed in output/php-web

## Translations

For the translations of this documentation, see:

- [Brazilian Portuguese](https://github.com/php/doc-pt_br) (doc-pt_br)
- [Chinese (Simplified)](https://github.com/php/doc-zh) (doc-zh)
- [English](https://github.com/php/doc-en) (doc-en)
- [French](https://github.com/php/doc-fr) (doc-fr)
- [German](https://github.com/php/doc-de) (doc-de)
- [Italian](https://github.com/php/doc-it) (doc-it)
- [Japanese](https://github.com/php/doc-ja) (doc-ja)
- [Polish](https://github.com/php/doc-pl) (doc-pl)
- [Romanian](https://github.com/php/doc-ro) (doc-ro)
- [Russian](https://github.com/php/doc-ru) (doc-ru)
- [Spanish](https://github.com/php/doc-es) (doc-es)
- [Turkish](https://github.com/php/doc-tr) (doc-tr)
- [Ukrainian](https://github.com/php/doc-uk) (doc-uk)

## Documentation pipeline

For more information on the various repositories that make up PHP's documentation pipeline,
see this [overview](https://github.com/php/doc-base/blob/master/docs/overview.md).
