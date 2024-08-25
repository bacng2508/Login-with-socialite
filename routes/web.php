<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/', [AuthController::class, 'postLogin'])->name('login');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        // Route::get('/create', [CategoryController::class, 'create'])->name('create');
        // Route::post('/', [CategoryController::class, 'store'])->name('store');
        // Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        // Route::patch('/{category}', [CategoryController::class, 'update'])->name('update');
        // Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});

Route::get('auth/google', function() {
    return Socialite::driver('google')->redirect();
})->name('social.login');

Route::get('/auth/google/callback', function () {
    // $user = Socialite::driver('google')->user();
    
    $socialiteProfile = Socialite::driver('google')->user();
    $user = User::where('email', $socialiteProfile->email)->first();
    // dd($socialiteProfile);
    //lấy thông tin username, email, avatar, social_id để lưu vào bảng users (nếu bạn cần thêm thông tin gì thì có thể lấy thêm)
    // $data = [
    //     'name' => $socialiteProfile->name,
    //     'avatar' => optional($user)->avatar_url ? $user->avatar_url : $socialiteProfile->avatar,
    //     'email' => $socialiteProfile->email,
    //     'socialite_id' => $socialiteProfile->id,
    // ];

    // $user = User::updateOrCreate(['email' => $socialiteProfile->email], $data);

    if (!$user) {
        $user = User::create([
            'name' => $socialiteProfile->name,
            'email' => $socialiteProfile->email,
            'password' => Hash::make(rand()),
            'avatar' => $socialiteProfile->avatar,
            'social_provider' => 'google',
            'social_id' => $socialiteProfile->id,
        ]);
    } else {
        User::where('email', $socialiteProfile->email)->update([
            'social_provider' => 'google',
            'social_id' => $socialiteProfile->id,
        ]);
    }

    Auth::login($user, true);

    return redirect()->route('admin.categories.index');
});
