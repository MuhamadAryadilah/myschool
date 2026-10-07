<?php

use App\Http\Controllers\Admin\BulkInvoiceController;
use App\Http\Controllers\Admin\ExtracurricularController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [NewsController::class, 'home'])->name('home');

Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{news:slug}', [NewsController::class, 'show'])->name('news.show');

// Link lama
Route::redirect('/news', '/berita', 301);

// Pendaftaran siswa baru
Route::get('/pendaftaran', [RegistrationController::class, 'create'])->name('registration.create');
Route::post('/pendaftaran', [RegistrationController::class, 'store'])
    ->middleware('throttle:20,10')
    ->name('registration.store');
Route::get('/pendaftaran/terima-kasih', [RegistrationController::class, 'thanks'])->name('registration.thanks');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    $students = $user->students()->with('invoices.payments')->get();

    return view('dashboard', compact('students'));
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        // Siswa
        Route::get('students/export', [StudentController::class, 'export'])
            ->name('students.export');
        Route::resource('students', StudentController::class)->except('show');

        // Orang tua
        Route::resource('parents', ParentController::class)->except('show');
        Route::patch('parents/{parent}/reset-password', [ParentController::class, 'resetPassword'])
            ->name('parents.reset-password');

        // Pendaftaran siswa baru
        Route::resource('registrations', AdminRegistrationController::class)->only(['index', 'show']);
        Route::patch('registrations/{registration}/approve', [AdminRegistrationController::class, 'approve'])
            ->name('registrations.approve');
        Route::patch('registrations/{registration}/reject', [AdminRegistrationController::class, 'reject'])
            ->name('registrations.reject');

        // Tagihan massal (harus di atas resource invoices)
        Route::get('invoices/export', [InvoiceController::class, 'export'])
            ->name('invoices.export');

        Route::get('payments/export', [PaymentVerificationController::class, 'export'])
            ->name('payments.export');

        Route::get('invoices/bulk', [BulkInvoiceController::class, 'create'])
            ->name('invoices.bulk.create');
        Route::post('invoices/bulk', [BulkInvoiceController::class, 'store'])
            ->name('invoices.bulk.store');

        // Tagihan
        Route::resource('invoices', InvoiceController::class)->except('show');
        Route::patch('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])
            ->name('invoices.pay');

        // Verifikasi pembayaran
        Route::get('payments', [PaymentVerificationController::class, 'index'])
            ->name('payments.index');
        Route::patch('payments/{payment}/approve', [PaymentVerificationController::class, 'approve'])
            ->name('payments.approve');
        Route::patch('payments/{payment}/reject', [PaymentVerificationController::class, 'reject'])
            ->name('payments.reject');

        // Berita
        Route::resource('news', AdminNewsController::class)->except('show');

        // Pengaturan pendaftaran: batas usia dan alur setelah mendaftar
        Route::get('registration-settings', [\App\Http\Controllers\Admin\RegistrationSettingsController::class, 'edit'])
            ->name('registration-settings.edit');
        Route::put('registration-settings', [\App\Http\Controllers\Admin\RegistrationSettingsController::class, 'update'])
            ->name('registration-settings.update');
        Route::resource('registration-steps', \App\Http\Controllers\Admin\RegistrationStepController::class)
            ->except('show')
            ->parameters(['registration-steps' => 'step']);
        // Ekstrakurikuler
        Route::resource('extracurriculars', ExtracurricularController::class)
            ->parameters(['extracurriculars' => 'extracurricular']);
        Route::post('extracurriculars/{extracurricular}/members', [ExtracurricularController::class, 'addMember'])
            ->name('extracurriculars.members.store');
        Route::patch('students/{student}/extracurricular', [ExtracurricularController::class, 'updateStudent'])
            ->name('students.extracurricular.update');
    });
});

/*
|--------------------------------------------------------------------------
| Orang tua: pembayaran dan kuitansi
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/riwayat-pembayaran', [PaymentController::class, 'history'])
        ->name('payments.history');
    Route::view('/cara-pembayaran', 'payments.methods')
        ->name('payments.methods');
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])
        ->name('payments.store');
    Route::get('/invoices/{invoice}/receipt', [ReceiptController::class, 'show'])
        ->name('receipts.show');
    Route::get('/bukti-bayar/{payment}', [PaymentController::class, 'proof'])
        ->name('payments.proof');
});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';