#!/bin/bash

sudo openssl pkcs12 -export -in /etc/letsencrypt/live/cas.dev.call-learning.io/cert.pem -inkey /etc/letsencrypt/live/cas.dev.call-learning.io/privkey.pem  -CAfile /etc/letsencrypt/live/cas.dev.call-learning.io/chain.pem -legacy -out cas.p12 -name "cas" -passout pass:changeit
