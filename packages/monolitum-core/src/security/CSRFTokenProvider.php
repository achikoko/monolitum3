<?php

namespace monolitum\core\security;

interface CSRFTokenProvider
{

    function isCSRFSystemAvailable(): bool;

    function getCurrentCSRFToken(): string;

}
