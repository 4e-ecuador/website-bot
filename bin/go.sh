#!/usr/bin/env bash
#
# 4E Website startup script
#

docker-compose up -d

symfony server:start -d --no-tls
