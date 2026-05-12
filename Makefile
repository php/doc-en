.PHONY: *

SHELL = /bin/sh

CURRENT_UID := $(shell id -u)
CURRENT_GID := $(shell id -g)

#
# If doc-base or phd exist as siblings to the current directory, add those as
# volumes to our Docker runs.
#

PATHS := -v ${PWD}:/var/www/en
ifneq ($(wildcard ../doc-base/LICENSE),)
	PATHS += -v ${PWD}/../doc-base:/var/www/doc-base
endif
ifneq ($(wildcard ../phd/LICENSE),)
	PATHS += -v ${PWD}/../phd:/var/www/phd
endif

xhtml: docker-build
	docker run --rm ${PATHS} -w /var/www -u ${CURRENT_UID}:${CURRENT_GID} php/doc-en

php: docker-build
	docker run --rm ${PATHS} -w /var/www -u ${CURRENT_UID}:${CURRENT_GID} \
		-e FORMAT=php php/doc-en

build: docker-build

docker-build:
	@CURRENT_HASH="$$(sha256sum .docker/Dockerfile | cut -d' ' -f1)"; \
	if [ ! -f .docker/built ] || [ "$$CURRENT_HASH" != "$$(cat .docker/built)" ]; then \
		docker build \
			--build-arg UID=${CURRENT_UID} --build-arg GID=${CURRENT_GID} \
			.docker -t php/doc-en; \
		echo "$$CURRENT_HASH" > .docker/built; \
	else \
		echo "Docker image is up to date"; \
	fi
