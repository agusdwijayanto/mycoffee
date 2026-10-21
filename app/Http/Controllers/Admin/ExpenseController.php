<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest('tanggal')->paginate(15);

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('admin.expenses.form', ['expense' => new Expense]);
    }

    public function store(Request $request)
    {
        Expense::create($this->validasi($request));

        return redirect()->route('admin.expenses.index')->with('sukses', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.form', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validasi($request));

        return redirect()->route('admin.expenses.index')->with('sukses', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('sukses', 'Pengeluaran berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:50'],
            'nominal' => ['required', 'integer', 'min:0'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
        ]);
    }
}
