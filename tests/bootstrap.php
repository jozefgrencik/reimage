<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

// Fixate session_id early on, as php7.2+ does not allow the session_id to be set
// after stdout has been written to.
// session_id('cli'); //???

define('TEST_DIR', __DIR__);
define('TEST_IMG1', __DIR__ . '/Fixture/IMG_20190816_142144.jpg');
