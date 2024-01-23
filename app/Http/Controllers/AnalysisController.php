<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class AnalysisController extends Controller
{
    public function transactionStatus(){
        $failed = Transaction::where('status','failed')->count();
        $pending = Transaction::where('status','pending')->count();
        $successful = Transaction::where('status','success')->count();

        return view('analytics', compact('failed','pending','successful'));
    }
}
