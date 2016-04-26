<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services\Session;

use App\Models\Session;

/**
 * Provides session handler for database
 * 
 * This session handler uses eloquent as the ORM
 */
class EloquentSessionHandler implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        if ($session = Session::find($id)) {
            return $session->data;
        }
    }

    public function write($id, $data)
    {
        $lifetime = (int) ini_get('session.gc_maxlifetime');
        
        $values = array(
            'id'        => $id,
            'data'      => $data,
            'expiry'    => $lifetime + time()
        );
        
        $session = Session::findOrNew($id);
        
        $session->fill($values);
        $session->save();
        
        return true;
    }

    public function gc($maxLifeTime)
    {
        Session::where('expiry', '<', $maxLifeTime)->delete();

        return true;
    }

    public function destroy($id)
    {
        if ($session = Session::find($id)) {
            $session->delete();
        }

        return true;
    }
}
