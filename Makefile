.PHONY: *

SHELL = /bin/sh

CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

#
# If doc-base or phd exist as siblings to the current directory, add those as
# volumes to our Docker runs.
#

PATHS := -v .:/var/www/en
ifneq ($(wildcard ../doc-base/LICENSE),)
	PATHS += -v ${PWD}/../doc-base:/var/www/doc-base
endif
ifneq ($(wildcard ../phd/LICENSE),)
	PATHS += -v ${PWD}/../phd:/var/www/phd
endif

xhtml: .docker/built
	docker run --rm ${PATHS} -w /var/www -u ${CURRENT_UID}:${CURRENT_GID} php/doc-en
	if [ -n "${BUILD_ENV}" ] && [ "${BUILD_ENV}" = "devcontainer" ]; then \
		.devcontainer/configure-apache.sh "${PWD}/output/php-chunked-xhtml"; \
	fi

php: .docker/built
	docker run --rm ${PATHS} -w /var/www -u ${CURRENT_UID}:${CURRENT_GID} \
		-e FORMAT=php php/doc-en
	if [ -n "${BUILD_ENV}" ] && [ "${BUILD_ENV}" = "devcontainer" ]; then \
		.devcontainer/configure-apache.sh "${PWD}/output/php-web"; \
	fi

build: .docker/built

.docker/built:
	docker build .docker -t php/doc-en
	touch .docker/built
