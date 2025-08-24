<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Exception;
use Illuminate\Http\Request;

class ContactController extends Controller
{
     public function contact(Request $request)
    {
        try {
           $data = $request->validate([
            'email'=> 'required|email',
            'subject'=> 'required|string|max:255',
            'message'=> 'required|string|max:1000',

           ]);
           $contact = Contact::create($data);
           return ResponseHelper::success($contact, "Conact Successfully");
        } catch (Exception $ex) {
            return ResponseHelper::error('User is not contact' . $ex);
        }
    }
}
