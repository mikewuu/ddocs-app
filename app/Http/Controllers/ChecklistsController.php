<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ChecklistsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getMakeForm()
    {
        return view('checklist.make');
    }
}
