<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $q = $request->get('q', '');
        $results = $search->search($q);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['results' => $results]);
        }
        return view('search.results', ['q' => $q, 'results' => $results]);
    }
}
