<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Symfony\Component\Form\FormError;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    protected function flashFormMessage($identifier, $message)
    {
        session()->flash('form_message.' . $identifier, $message);
    }

    protected function dispatchFormFlashMessages($identifier, $form)
    {
        if ($message = session('form_message.' . $identifier)) {
            $form->addError(new FormError($message));
        }
    }
}
