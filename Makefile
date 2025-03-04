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

php: .docker/built
	docker run --rm ${PATHS} -w /var/www -u ${CURRENT_UID}:${CURRENT_GID} \
		-e FORMAT=php php/doc-en

build: .docker/built

.docker/built:
	docker build .docker -t php/doc-en
	touch .docker/built
