<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard\Settings;

use Artisan;
use Storage;
use Session;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use App\Http\Controllers\Controller;
use App\Extensions\Settings;
use App\Extensions\Form;

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Manage settings page
     * 
     * URL: /dashboard/settings/
     */
    public function index(Request $request)
    {
        $currentYear = date('Y'); 

        $settings = Settings::getAll();
        $form = Form::create($settings);
        
        /*
            Generate academic year changes.
            e.g. 2014 - 2015
                 2015 - 2016
         */
        $years = collect(range($currentYear - 2, $currentYear + 2))->map(function ($year, $i) {
            return $year . ' - ' . ($year + 1);
        })->toArray();

        $semesters = [
            'FIRST'     => '1st semester',
            'SECOND'    => '2nd semester'
        ];

        $periods = [
            'PRELIM',
            'MIDTERM',
            'PREFINAL',
            'FINAL'
        ];

        $dateOptions = [
            'required'      => false,
            'html5'         => true,
            'input'         => 'string',
            'date_widget'   => 'single_text',
            'time_widget'   => 'single_text'
        ];

        $form->add('academic_year', Type\ChoiceType::class, [
            'choices' => array_combine($years, $years)
        ]);

        $form->add('semester', Type\ChoiceType::class, [
            'choices' => $semesters
        ]);

        $form->add('period', Type\ChoiceType::class, [
            'choices' => array_combine($periods, $periods)
        ]);

        $form->add('prelim_grade_deadline', Type\DateTimeType::class, array_merge($dateOptions, [
            'label' => 'Preliminary grade submission deadline'
        ]));

        $form->add('midterm_grade_deadline', Type\DateTimeType::class, array_merge($dateOptions, [
            'label' => 'Midterm grade submission deadline'
        ]));

        $form->add('prefinal_grade_deadline', Type\DateTimeType::class, array_merge($dateOptions, [
            'label' => 'Pre-final grade submission deadline'
        ]));

        $form->add('final_grade_deadline', Type\DateTimeType::class, array_merge($dateOptions, [
            'label' => 'Final grade submission deadline'
        ]));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            Settings::setArray($form->getData());
            Session::flash('flash_message', 'success>>>Settings has been updated');

            return redirect()->route('dashboard.settings.index');
        }

        return view('dashboard/settings/index', [
            'form'  => $form->createView()
        ]);
    }

    /**
     * Server maintenance page
     * 
     * URL: /dashboard/settings/maintenance
     */
    public function maintenance()
    {
        $importCacheFiles = Storage::allFiles('imports');
        $importCacheSize = 0;

        foreach ($importCacheFiles as $file) {
            $importCacheSize += Storage::size($file);
        }

        return view('dashboard/settings/maintenance', [
            'import_cache_size' => formatBytes($importCacheSize)
        ]);
    }

    /**
     * Server maintenance purge page
     * 
     * URL: /dashboard/settings/maintenance/purge
     */
    public function maintenancePurge(Request $request)
    {
        $store = $request->query->get('store');

        if (!in_array($store, ['importCache', 'appCache'])) {
            abort(404);
        }

        $name = 'cache';
        $action = function () {};

        switch ($store) {
            case 'importCache':
                $name = 'import cache';
                $action = function () { Storage::deleteDirectory('imports'); };

                break;
            
            case 'appCache':
                $name = 'app cache';
                $action = function () {
                    Artisan::call('cache:clear');
                    Artisan::call('debugbar:clear');
                    Artisan::call('twig:clean');
                    Artisan::call('view:clear');
                };

                break;
        }

        if ($request->getMethod() == 'POST') {
            $action();

            Session::flash('flash_message', 'success>>>Action completed');
            return redirect()->route('dashboard.settings.maintenance');
        }

        return view('dashboard/settings/purge', [
            'store_name' => $name
        ]);
    }
}
