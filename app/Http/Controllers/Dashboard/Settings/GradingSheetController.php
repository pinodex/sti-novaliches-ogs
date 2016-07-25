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

use Session;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Controllers\Controller;
use App\Extensions\Settings;
use App\Extensions\Form;

class GradingSheetController extends Controller
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
        $regexConstraint = new Assert\Regex([
            'message'   => 'Please enter a valid cell address',
            'pattern'   => '/[A-Z]+[0-9]+/',
            'match'     => true
        ]);

        $store = collect(Settings::getAll())->filter(function ($value, $key) {
            return strpos($key, 'grading_sheet_') === 0;
        })->toArray();

        $form = Form::create($store);

        $form->add('grading_sheet_student_number_cell', Type\TextType::class, [
            'label'         => 'Student number cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_prelim_grade_cell', Type\TextType::class, [
            'label'         => 'Prelims grade cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_midterm_grade_cell', Type\TextType::class, [
            'label'         => 'Midterms grade cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_prefinal_grade_cell', Type\TextType::class, [
            'label'         => 'Pre-finals grade cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_final_grade_cell', Type\TextType::class, [
            'label'         => 'Finals grade cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_prelim_absent_cell', Type\TextType::class, [
            'label'         => 'Prelims absences cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_midterm_absent_cell', Type\TextType::class, [
            'label'         => 'Midterms absences cell',
            'constraints'   => $regexConstraint
        ]);

        $form->add('grading_sheet_prefinal_absent_cell', Type\TextType::class, [
            'label'         => 'Pre-finals absences cell',
            'constraints'   => $regexConstraint
        ]);
        
        $form->add('grading_sheet_final_absent_cell', Type\TextType::class, [
            'label'         => 'Finals absences cell',
            'constraints'   => $regexConstraint
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            Settings::setArray($data);
            Session::flash('flash_message', 'success>>>Grading sheet settings has been saved');

            return redirect()->route('dashboard.settings.gradingsheet.index');
        }

        return view('dashboard/settings/gradingsheet/index', [
            'form'  => $form->createView()
        ]);
    }
}
