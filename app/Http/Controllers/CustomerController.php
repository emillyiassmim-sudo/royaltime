<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Support\BrazilianTaxId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        return view('account.dashboard', [
            'user' => $user,
            'orders' => $user->orders()->latest()->limit(5)->get(),
            'addresses' => $user->addresses()->latest()->limit(2)->get(),
        ]);
    }

    public function profile()
    {
        return view('account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'cpf' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! BrazilianTaxId::isValid((string) $value)) {
                        $fail('Informe um CPF válido.');
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9()\s+.-]{10,30}$/'],
        ]);

        $normalizedCpf = BrazilianTaxId::normalize($validated['cpf']);
        $cpfHash = hash('sha256', $normalizedCpf);
        Validator::make(
            ['cpf_hash' => $cpfHash],
            ['cpf_hash' => [Rule::unique('users', 'cpf_hash')->ignore($user->id)]],
            ['cpf_hash.unique' => 'Este CPF já está cadastrado em outra conta.'],
            ['cpf_hash' => 'CPF']
        )->validate();

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf' => $normalizedCpf,
            'cpf_hash' => $cpfHash,
            'phone' => $validated['phone'],
        ])->save();

        return redirect()->route('account.profile')->with('success', 'Dados pessoais atualizados.');
    }

    public function addresses()
    {
        return view('account.addresses', [
            'addresses' => Auth::user()->addresses()->latest()->get(),
        ]);
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'cep' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
        ]);

        $validated['user_id'] = Auth::id();

        Address::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'label' => $validated['label'],
            ],
            $validated
        );

        return redirect()->route('account.addresses')->with('success', 'Endereço salvo com sucesso.');
    }

    public function orders()
    {
        return view('account.orders', [
            'orders' => Auth::user()->orders()->latest()->get(),
        ]);
    }
}
