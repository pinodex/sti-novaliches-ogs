<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Providers;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Twig_Environment;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use App\Services\Helper;
use App\Services\Auth;

/**
 * Twig extensions
 * 
 * Used to inject extensions to Twig
 */
class TwigExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->extend('twig', function (Twig_Environment $twig, Application $app) {
            $twig->addFunction(new Twig_SimpleFunction('helper', function ($method, $args = array()) {
                return call_user_func_array(array(
                    Helper::class, $method
                ), $args);
            }));

            $twig->addFunction(new \Twig_SimpleFunction('flashbag', function ($name) use ($app) {
                return $app['flashbag']->get($name);
            }));

            $twig->addFilter(new Twig_SimpleFilter('format_student_id', array(
                Helper::class, 'formatStudentId'
            )));

            $twig->addFilter(new Twig_SimpleFilter('format_grade', array(
                Helper::class, 'formatGrade'
            )));

            $twig->addFilter(new Twig_SimpleFilter('grade_class', array(
                Helper::class, 'getGradeClass'
            )));

            return $twig;
        });
    }
    
    public function boot(Application $app)
    {
        $app->extend('twig', function (Twig_Environment $twig, Application $app) {
            $twig->addGlobal('current_user', Auth::user());

            return $twig;
        });
    }
}
