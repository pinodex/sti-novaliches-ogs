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

class EmailDeliveryController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    public function index(Request $request)
    {   
        $form = Form::create();

        $form->add('recipient_email', Type\TextType::class, [
            'constraints'   => new Assert\Email(),
            'data'          => Settings::get('email_delivery_recipient_email')
        ]);

        $form->add('recipient_name', Type\TextType::class, [
            'data' => Settings::get('email_delivery_recipient_name')
        ]);

        $form->add('subject', Type\TextType::class, [
            'data' => Settings::get('email_delivery_subject')
        ]);

        $form->add('body', Type\TextareaType::class, [
            'data' => Settings::get('email_delivery_body')
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            foreach ($data as $key => $value) {
                $data['email_delivery_' . $key] = $value;
                unset($data[$key]);
            }

            Settings::setArray($data);

            Session::flash('flash_message', 'success>>>Email delivery settings has been saved');

            return redirect()->route('dashboard.settings.emaildelivery.index');
        }

        return view('dashboard/settings/emaildelivery/index', [
            'form' => $form->createView()
        ]);
    }
}
