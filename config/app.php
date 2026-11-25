<?php

$appUrl = getenv('APP_URL');

$baseUrl = is_string($appUrl) && trim($appUrl) !== ''
    ? rtrim(trim($appUrl), '/')
    : 'http://localhost:8080';
