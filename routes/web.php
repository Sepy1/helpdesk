<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketCommentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| - Root -> login
| - /dashboard redirect sesuai role
| - Cabang: buat & lihat tiket sendiri
| - IT: list, take, release, close, reopen, stats, vendor followup, eskalasi
| - Keduanya: lihat detail & komentar, unduh lampiran
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'))->name('home');

Route::middleware(['auth'])->group(function () {
    // ===== Profile =====
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== Dashboard role-based =====
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return $user->role === 'IT'
            ? redirect()->route('it.dashboard')
            : redirect()->route('cabang.dashboard');
    })->name('dashboard');

    // ===== Shared: detail, komentar, lampiran =====
    
Route::get('/ticket/{ticket}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/ticket/{ticket}/download', [TicketController::class, 'downloadAttachment'])->name('ticket.download');

// Komentar tiket
Route::post('/ticket/{ticket}/comment', [TicketCommentController::class, 'store'])->name('ticket.comment');

Route::delete('/comment/{comment}', [TicketController::class, 'deleteComment'])
    ->name('comment.delete');

Route::get('/ticket/comment/{comment}/download', [TicketController::class, 'downloadCommentAttachment'])
    ->name('comment.download');

    // ===== CABANG =====
    Route::middleware(['role:CABANG'])->group(function () {
        Route::get('/cabang/dashboard', [TicketController::class, 'create'])->name('cabang.dashboard'); // form buat tiket
        Route::post('/cabang/ticket', [TicketController::class, 'store'])->name('cabang.ticket.store'); // simpan tiket
        Route::get('/cabang/tickets', [TicketController::class, 'myTickets'])->name('cabang.tickets');  // daftar tiket saya
    });

    // ===== IT =====
    Route::middleware(['role:IT'])->group(function () {
        Route::get('/it/dashboard', [TicketController::class, 'index'])->name('it.dashboard');           // semua tiket + filter
        Route::get('/it/my-tickets', [TicketController::class, 'myAssigned'])->name('it.my');            // tiket saya (IT)
        Route::get('/it/stats', [TicketController::class, 'stats'])->name('it.stats');                   // statistik

        Route::post('/it/ticket/{ticket}/take',            [TicketController::class, 'take'])->name('it.ticket.take');
        Route::post('/it/ticket/{ticket}/release',         [TicketController::class, 'release'])->name('it.ticket.release');
        Route::post('/it/ticket/{ticket}/reopen',          [TicketController::class, 'reopen'])->name('it.ticket.reopen');
        Route::post('/it/ticket/{ticket}/close',           [TicketController::class, 'close'])->name('it.ticket.close');
        Route::post('/it/ticket/{ticket}/eskalasi',        [TicketController::class, 'setEskalasi'])->name('it.ticket.eskalasi');
        Route::post('/it/ticket/{ticket}/vendor-followup', [TicketController::class, 'vendorFollowup'])->name('it.ticket.vendor_followup');
        Route::post('/it/ticket/{ticket}/progress', [TicketController::class, 'saveProgress'])
     ->name('it.ticket.progress');
  
    });
});

// Auth routes (Breeze/Fortify/Jetstream)
require __DIR__ . '/auth.php';
