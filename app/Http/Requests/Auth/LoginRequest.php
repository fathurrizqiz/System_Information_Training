<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nrp' => ['nullable', 'string'],
            'employee_id' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Add custom validation after rules.
     */
    protected function prepareForValidation()
    {
        // Ensure at least one identifier is provided
        if (!$this->filled('nrp') && !$this->filled('employee_id')) {
            throw ValidationException::withMessages([
                'nrp' => 'PERIKSA KEMBALI NRP',
                'employee_id' => 'Either NRP or Employee ID is required.',
            ]);
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function authenticate(): void
    // {
    //     $this->ensureIsNotRateLimited();

    //     $nrp = $this->input('nrp');
    //     $employee_id = $this->input('employee_id');
    //     $password = $this->input('password');

    //     // Determine which identifier was actually provided
    //     $isNrpField = $this->filled('nrp');
    //     $isEmployeeIdField = $this->filled('employee_id');

    //     // Use the appropriate identifier
    //     $identifier = $isNrpField ? $nrp : $employee_id;

    //     \Log::info("Login attempt with identifier: {$identifier}, from field: " . ($isNrpField ? 'nrp' : 'employee_id'));

    //     // STEP 1: Check in users table first
    //     if ($isNrpField) {
    //         $user = User::where('nrp', $identifier)->first();
    //     } else {
    //         $user = User::where('employee_id', $identifier)->first();
    //     }

    //     if ($user) {
    //         \Log::info("User found in users table, checking password");

    //         // Verify password
    //         if (Hash::check($password, $user->password)) {
    //             \Log::info("Password correct, logging in existing user");
    //             Auth::login($user, $this->boolean('remember'));
    //             RateLimiter::clear($this->throttleKey());
    //             return;
    //         } else {
    //             RateLimiter::hit($this->throttleKey());
    //             throw ValidationException::withMessages([
    //                 $isNrpField ? 'nrp' : 'employee_id' => 'Password yang Anda masukkan salah. Silakan coba lagi.',
    //             ]);
    //         }
    //     }

    //     // STEP 2: User doesn't exist, check in karyawans table
    //     \Log::info("User not found in users table, checking source tables");

    //     $karyawan = null;
    //     if ($isNrpField) {
    //         $karyawan = DB::table('karyawans')->where('nrp', $identifier)->first();
    //     } else {
    //         // If using employee_id, check if there's a mapping to nrp in karyawans
    //         $karyawan = DB::table('karyawans')->where('employee_id', $identifier)->first();

    //         // If not found with employee_id, try with nrp field (in case employee_id is actually a nrp)
    //         if (!$karyawan) {
    //             $karyawan = DB::table('karyawans')->where('nrp', $identifier)->first();
    //         }
    //     }

    //     if ($karyawan) {
    //         \Log::info("Found in karyawans table, creating new user");

    //         // Create user with appropriate identifier field
    //         $userData = [
    //             'name' => $karyawan->nama_karyawan ?? $identifier,
    //             'password' => Hash::make($password),

    //         ];

    //         if ($isNrpField) {
    //             $userData['nrp'] = $identifier;
    //         } else {
    //             $userData['employee_id'] = $identifier;
    //         }

    //         $user->assignRole('karyawan');
    //     } else {
    //         // STEP 3: If not found in karyawans, check in tambah_karyawan
    //         \Log::info("Not found in karyawans, checking tambah_karyawan");

    //         $rekruter = null;
    //         $possibleFields = ['employee_id', 'emp_id', 'id_karyawan', 'karyawan_id', 'nrp'];

    //         foreach ($possibleFields as $field) {
    //             $rekruter = DB::table('tambah_karyawan')->where($field, $identifier)->first();
    //             if ($rekruter) {
    //                 \Log::info("Found in tambah_karyawan table using field: {$field}");
    //                 break;
    //             }
    //         }

    //         if ($rekruter) {
    //             \Log::info("Found in tambah_karyawan table, creating new user");

    //             // Create user with appropriate identifier field
    //             $userData = [
    //                 'name' => $rekruter->nama ?? $identifier,
    //                 'password' => Hash::make($password),

    //             ];

    //             if ($isNrpField) {
    //                 $userData['nrp'] = $identifier;
    //             } else {
    //                 $userData['employee_id'] = $identifier;
    //             }

    //             $user->assignRole('user');
    //         } else {
    //             // STEP 4: Not found in all tables
    //             \Log::warning("Identifier {$identifier} not found in all tables");

    //             RateLimiter::hit($this->throttleKey());

    //             throw ValidationException::withMessages([
    //                 $isNrpField ? 'nrp' : 'employee_id' => 'Identifier not found in database. Please check your NRP or Employee ID.',
    //             ]);
    //         }
    //     }

    //     // Login new user
    //     Auth::login($user, $this->boolean('remember'));

    //     \Log::info("New user {$identifier} created and logged in successfully");

    //     RateLimiter::clear($this->throttleKey());
    // }
    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $nrp = $this->input('nrp');
        $employee_id = $this->input('employee_id');
        $password = $this->input('password');

        // Determine which identifier was actually provided
        $isNrpField = $this->filled('nrp');

        // Use the appropriate identifier
        $identifier = $isNrpField ? $nrp : $employee_id;

        \Log::info("Login attempt with identifier: {$identifier}, from field: " . ($isNrpField ? 'nrp' : 'employee_id'));

        // STEP 1: Check in users table first
        if ($isNrpField) {
            $user = User::where('nrp', $identifier)->first();
        } else {
            $user = User::where('employee_id', $identifier)->first();
        }

        if ($user) {
            \Log::info("User found in users table, checking password");

            // Verify password
            if (Hash::check($password, $user->password)) {

                $role = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name' => 'karyawan',
                    'guard_name' => 'web',
                ]);

                if (!$user->hasRole('karyawan')) {
                    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                    $user->assignRole($role);
                }

                Auth::login($user, $this->boolean('remember'));

                RateLimiter::clear($this->throttleKey());
                return;
            } else {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    $isNrpField ? 'nrp' : 'employee_id' => 'Password yang Anda masukkan salah.',
                ]);
            }
        }

        // STEP 2: User doesn't exist, check in karyawans table (Divisi Kamu)
        \Log::info("User not found in users table, checking source tables");

        $karyawan = null;
        if ($isNrpField) {
            $karyawan = DB::table('karyawans')->where('nrp', $identifier)->first();
        } else {
            $karyawan = DB::table('karyawans')->where('employee_id', $identifier)->first();

            // If not found with employee_id, try with nrp field
            if (!$karyawan) {
                $karyawan = DB::table('karyawans')->where('nrp', $identifier)->first();
            }
        }

        if ($karyawan) {
            \Log::info("Found in karyawans table, creating new user");

            // Menyiapkan data untuk user baru
            $userData = [
                'name' => $karyawan->nama_karyawan ?? $identifier,
                'password' => Hash::make($password),
            ];

            if ($isNrpField) {
                $userData['nrp'] = $identifier;
            } else {
                $userData['employee_id'] = $identifier;
            }

            // Perbaikan: Eksekusi create user ke database agar objek $user tercipta
            $user = User::create($userData);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Create role if it doesn't exist, then assign
            $role = \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => 'karyawan',
                'guard_name' => 'web',
            ]);

            $user->assignRole($role);
        } else {
            // STEP 3: Data tidak ditemukan di semua tabel divisi kamu
            \Log::warning("Identifier {$identifier} not found in all allowed tables");

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $isNrpField ? 'nrp' : 'employee_id' => 'NRP atau Employee ID tidak terdaftar di sistem.',
            ]);
        }

        // Login new user yang baru saja dibuat dari tabel karyawan
        Auth::login($user, $this->boolean('remember'));

        \Log::info("New user {$identifier} created and logged in successfully");

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $field = $this->input('nrp') ? 'nrp' : 'employee_id';

        throw ValidationException::withMessages([
            $field => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = $this->input('nrp') ?? $this->input('employee_id');
        return Str::transliterate(Str::lower($identifier) . '|' . $this->ip());
    }
}