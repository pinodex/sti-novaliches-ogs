<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard;

use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
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
     * Dashboard index
     * 
     * URL: /dashboard/
     */
    public function index()
    {
        $context = array();

        if ($this->isRole('faculty')) {
            $faculty = $this->user->getModel();

            $context['faculty'] = $faculty->toArray();
            $context['unread_memo_count'] = $faculty->getUnreadMemoCount();
            $context['department'] = $faculty->department;
            
            $context['statuses'] = array(
                $faculty->getStatusAttribute('prelim'),
                $faculty->getStatusAttribute('midterm'),
                $faculty->getStatusAttribute('prefinal'),
                $faculty->getStatusAttribute('final')
            );

            $context['stats'] = array(
                'failed' => array(
                    'prelim'    => $faculty->getNumberOfFailsAttribute('prelim'),
                    'midterm'   => $faculty->getNumberOfFailsAttribute('midterm'),
                    'prefinal'  => $faculty->getNumberOfFailsAttribute('prefinal'),
                    'final'     => $faculty->getNumberOfFailsAttribute('final')
                ),

                'dropped' => array(
                    'prelim'    => $faculty->getNumberOfDropsAttribute('prelim'),
                    'midterm'   => $faculty->getNumberOfDropsAttribute('midterm'),
                    'prefinal'  => $faculty->getNumberOfDropsAttribute('prefinal'),
                    'final'     => $faculty->getNumberOfDropsAttribute('final'),
                )
            );
        }

        return view('/dashboard/index', $context);
    }

    /**
     * Dashboard user account settings
     * 
     * Url: /dashboard/account
     */
    public function account(Request $request)
    {
        $form = Form::create();

        $form->add('current_password', Type\PasswordType::class, array(
            'constraints' => new CustomAssert\PasswordMatch(array(
                'hash' => $this->user->getModel()->password
            ))
        ));

        $form->add('password', Type\RepeatedType::class, array(
            'type'              => Type\PasswordType::class,
            'first_options'     => array('label' => 'New Password'),
            'second_options'    => array('label' => 'Repeat Password'),
            
            'constraints'       => new Assert\Length(array(
                'min'           => 8,
                'minMessage'    => 'Password should be at least 8 characters long.'
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->user->getModel()->fill($form->getData());
            $this->user->getModel()->save();

            Session::flash('flash_message', 'success>>>Your account settings has been updated');

            return redirect()->route('dashboard.index');
        }

        return view('dashboard/account', array(
            'settings_form' => $form->createView()
        ));
    }
}
