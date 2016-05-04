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
use App\Models\Setting;
use App\Models\Department;
use App\Models\FacultyGradeImportLog;
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;
use App\Services\Helper;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for settings pages
 */
class SettingsController
{
    /**
     * Manage settings page
     * 
     * URL: /dashboard/settings/
     */
    public function index(Request $request, Application $app)
    {
        $settings = Setting::all()->keyBy('id');
        $currentYear = date('Y');
        
        $form = Form::create();
        
        /*
            Generate academic year changes.
            e.g. 2014 - 2015
                 2015 - 2016
         */
        $years = new Collection(range($currentYear - 2, $currentYear + 2));
        $years = $years->map(function ($year, $i) {
            return $year . ' - ' . ($year + 1);
        })->toArray();

        $periods = array(
            'PRELIM',
            'MIDTERM',
            'PREFINAL',
            'FINAL'
        );

        $form->add('academic_year', 'choice', array(
            'choices'   => array_combine($years, $years),
            'data'      => $settings->has('academic_year') ? 
                            $settings->get('academic_year')->value : null
        ));

        $form->add('period', 'choice', array(
            'choices'   => array_combine($periods, $periods),
            'data'      => $settings->has('period') ? 
                            $settings->get('period')->value : null
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            foreach ($form->getData() as $key => $value) {
                if ($settings->has($key)) {
                    $settings->get($key)->value = $value;
                    $settings->get($key)->save();

                    continue;
                }

                Setting::create(array(
                    'id'    => $key,
                    'value' => $value
                ));
            }

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

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            try {
                $fs->remove($dirs);
            } catch (\Exception $e) {
                dd($e);
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
        $user = Auth::user()->getModel();
        $form = Form::create();
        
        $form->add('_confirm_password', 'repeated', array(
            'type'              => 'password',
            'first_options'     => array('label' => 'Password'),
            'second_options'    => array('label' => 'Repeat Password'),
            'constraints'       => new CustomAssert\PasswordMatch(array(
                'hash' => $user->password
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            Grade::truncate();
            FacultyGradeImportLog::truncate();
            Department::whereNotNull('name')->update(array(
                'grade_submission_deadline' => null
            ));

            FlashBag::add('messages', 'info>>>Database cleanup completed');
            return $app->redirect($app->path('dashboard.settings.maintenance'));
        }

        return View::render('dashboard/settings/database-cleanup', array(
            'form'      => $form->createView(),
        ));
    }
}
