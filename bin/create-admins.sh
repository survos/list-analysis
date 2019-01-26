#!/usr/bin/env bash
bin/console fos:user:create tac tac@survos.com nosmoke  2>/dev/null
bin/console fos:user:create admin admin@survos.com admin 2>/dev/null

bin/console fos:user:promote tac ROLE_ADMIN
bin/console fos:user:promote admin ROLE_ADMIN

