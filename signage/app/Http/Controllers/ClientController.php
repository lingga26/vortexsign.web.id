<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('id')->get();
        $count = fn ($table) => DB::table($table)->select('client_id', DB::raw('COUNT(*) as c'))
            ->groupBy('client_id')->pluck('c', 'client_id');

        return view('clients.index', [
            'clients' => $clients,
            'users'   => $count('users'),
            'toko'    => $count('toko'),
            'signage' => $count('digital_signage'),
        ]);
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'nama'           => 'required|string|max:255',
            'kode'           => 'required|alpha_dash|max:50|unique:clients,kode',
            'admin_name'     => 'required|string|max:255',
            'admin_username' => 'required|alpha_dash|max:50|unique:users,username',
            'admin_email'    => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($v) {
            $client = Client::create(['nama' => $v['nama'], 'kode' => strtolower($v['kode']), 'aktif' => true]);

            // password di-hash otomatis oleh cast 'hashed' pada model User
            User::create([
                'name'      => $v['admin_name'],
                'username'  => $v['admin_username'],
                'email'     => $v['admin_email'],
                'password'  => $v['admin_password'],
                'role'      => 'admin',
                'aktif'     => 1,
                'client_id' => $client->id,
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client dan akun admin berhasil dibuat.');
    }

    public function toggle(Client $client)
    {
        if ($client->id == 1) {
            return back()->with('error', 'Client default tidak boleh dinonaktifkan.');
        }
        $client->update(['aktif' => !$client->aktif]);
        return back()->with('success', 'Status client diperbarui.');
    }
}
