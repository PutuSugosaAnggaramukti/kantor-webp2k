<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Auth;

class PengaturanController extends Controller
{
   
   public function indexContent()
    {
        return view('kunjungan.partials.pengaturan_content');
    }

}