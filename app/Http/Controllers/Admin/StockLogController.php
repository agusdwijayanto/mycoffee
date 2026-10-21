<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\StockLog;
use Illuminate\Http\Request;

class StockLogController extends Controller
{
    public function index(Request $request)
    {
        $menus = Menu::orderBy('nama')->get();

        $logs = StockLog::with(['menu', 'user'])
            ->when($request->filled('menu_id'), fn ($q) => $q->where('menu_id', $request->menu_id))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.stock-logs.index', compact('logs', 'menus'));
    }
}
