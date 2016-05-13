<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard;

use Silex\Application;
use App\Models\Grade;
use App\Models\Department;
use App\Models\FacultyGradeImportLog;
use App\Services\View;
use App\Services\Form;
use App\Services\Helper;
use App\Services\Settings;
use App\Services\FlashBag;
use App\Controllers\Controller;
use App\Constraints as CustomAssert;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for settings pages
 */
class SettingsController extends Controller
{
    /**
     * Manage settings page
     * 
     * URL: /dashboard/settings/
     */
    public function index(Request $request, Application $app)
    {
        $currentYear = date('Y'); 

        $settings = Settings::getAll();
        $form = Form::create($settings);
        
        /*
            Generate academic year changes.
            e.g. 2014 - 2015
                 2015 - 2016
         */
        $years = new Collection(range($currentYear - 2, $currentYear + 2));
        $years = $years->map(function ($year, $i) {
            return $year . ' - ' . ($year + 1);
        })->toArray();

        $semesters = array(
            'FIRST'     => '1st semester',
            'SECOND'    => '2nd semester'
        );

        $periods = array(
            'PRELIM',
            'MIDTERM',
            'PREFINAL',
            'FINAL'
        );

        $dateOptions = array(
            'required'      => false,
            'html5'         => true,
            'input'         => 'string',
            'date_widget'   => 'single_text',
            'time_widget'   => 'single_text'
        );

        $form->add('academic_year', 'choice', array(
            'choices' => array_combine($years, $years)
        ));

        $form->add('semester', 'choice', array(
            'choices' => $semesters
        ));

        $form->add('period', 'choice', array(
            'choices' => array_combine($periods, $periods)
        ));

        $form->add('prelim_grade_deadline', 'datetime', array_merge($dateOptions, array(
            'label' => 'Preliminary grade submission deadline'
        )));

        $form->add('midterm_grade_deadline', 'datetime', array_merge($dateOptions, array(
            'label' => 'Midterm grade submission deadline'
        )));

        $form->add('prefinal_grade_deadline', 'datetime', array_merge($dateOptions, array(
            'label' => 'Pre-final grade submission deadline'
        )));

        $form->add('final_grade_deadline', 'datetime', array_merge($dateOptions, array(
            'label' => 'Final grade submission deadline'
        )));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            Settings::setArray($form->getData());
            FlashBag::add('messages', 'success>>>Settings has been updated');

            return $app->redirect($app->path('dashboard.settings'));
        }

        return View::render('dashboard/settings/index', array(
            'form'  => $form->createView()
        ));
    }

    /**
     * Server maintenance page
     * 
     * URL: /dashboard/settings/maintenance
     */
    public function maintenance()
    {
        $cache = new Finder();
        $cache->files()->in(ROOT . 'cache');

        $storage = new Finder();
        $storage->files()->in(ROOT . 'storage');

        $cacheSize = 0;
        $storageSize = 0;

        foreach ($cache as $file) {
            $cacheSize += $file->getSize();
        }

        foreach ($storage as $file) {
            $storageSize += $file->getSize();
        }

        return View::render('dashboard/settings/maintenance', array(
            'cache_size'    => Helper::formatBytes($cacheSize),
            'storage_size'  => Helper::formatBytes($storageSize)
        ));
    }

    /**
     * Clear cache/storage page
     * 
     * URL: /dashboard/settings/maintenance/clear
     */
    public function clear(Request $request, Application $app)
    {
        $target = $request->query->get('target');
        
        if (!in_array($target, array('cache', 'storage'))) {
            return $app->redirect($app->path('dashboard.settings.maintenance'));
        }

        $fs = new Filesystem();

        $dirs = new Finder();
        $dirs->directories()->in(ROOT . $target);

        $files = new Finder();
        $files->files()->in(ROOT . $target);

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            try {
                $fs->remove($files);
                $fs->remove($dirs);
            } catch (\Exception $e) {
                FlashBag::add('messages', 'danger>>>Error: ' . $e->getMessage());
                return $app->redirect($app->path('dashboard.settings.maintenance'));
            }

            FlashBag::add('messages', 'info>>>Directory has been cleared');
            return $app->redirect($app->path('dashboard.settings.maintenance'));
        }

        return View::render('dashboard/settings/clear', array(
            'form'      => $form->createView(),
            'target'    => $target
        ));
    }

    /**
     * Database cleanup page
     * 
     * URL: /dashboard/settings/maintenance/database-cleanup
     */
    public function databaseCleanup(Request $request, Application $app)
    {
        $form = Form::create();
        
        $form->add('_confirm_password', 'repeated', array(
            'type'              => 'password',
            'first_options'     => array('label' => 'Password'),
            'second_options'    => array('label' => 'Repeat Password'),
            'constraints'       => new CustomAssert\PasswordMatch(array(
                'hash' => $this->user->getModel()->password
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            Grade::truncate();

            FlashBag::add('messages', 'info>>>Database cleanup completed');
            return $app->redirect($app->path('dashboard.settings.maintenance'));
        }

        return View::render('dashboard/settings/database-cleanup', array(
            'form'      => $form->createView(),
        ));
    }
}
