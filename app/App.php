<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Silex\Application;

/**
 * Main application
 */
class App extends Application {
    use Application\FormTrait;
    use Application\UrlGeneratorTrait;

    const VERSION = '1.0.0';
}
