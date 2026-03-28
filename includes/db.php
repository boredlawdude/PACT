<?php
declare(strict_types=1);

// Always require the main app bootstrap to ensure db() is defined
require_once dirname(__DIR__) . '/app/bootstrap.php';

// db() function is defined in app/bootstrap.php. This file only loads dependencies.