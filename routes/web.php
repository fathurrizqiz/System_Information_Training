<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Evaluasi\EvaluasiController;
use App\Http\Controllers\Inbox\InboxController;
use App\Http\Controllers\JadwalDiklat\JadwalEksternalController;
use App\Http\Controllers\JadwalDiklat\JadwalHLCController;
use App\Http\Controllers\JadwalDiklat\JadwalInternalController;
use App\Http\Controllers\KaryawanDiklat\ApprovDiklateController;
use App\Http\Controllers\KaryawanDiklat\DiklatController;
use App\Http\Controllers\jadwalDiklat\JadwalDiklatController;
use App\Http\Controllers\KaryawanDiklat\InternalController;
use App\Http\Controllers\MasterData\MasterDataController;
use App\Http\Controllers\Materi\MateriController;
use App\Http\Controllers\no_hp\NohpController;
use App\Http\Controllers\Persetujuan\EksternalAdminController;
use App\Http\Controllers\Persetujuan\HLCAdminController;
use App\Http\Controllers\RencanaDiklat\RPT\DetailPeriodeController;
use App\Http\Controllers\RencanaDiklat\RPT\DiklatInternalController;
use App\Http\Controllers\RencanaDiklat\RPT\DokumentasiController;
use App\Http\Controllers\RencanaDiklat\RPT\NonFormalController;
use App\Http\Controllers\RencanaDiklat\RPT\pendidikanController;
use App\Http\Controllers\RencanaDiklat\RPT\PostPreeController;
use App\Http\Controllers\RencanaDiklat\RPT\PresensiDetailController;
use App\Http\Controllers\RencanaDiklat\RPT\SertifikatController;
use App\Http\Controllers\report\GenerateReportController;
use App\Http\Controllers\report\ReportController;
use App\Http\Controllers\SettingsMenu\SettingsController;
use App\Http\Controllers\Silabus\SilabusController;
use App\Http\Controllers\RencanaDiklat\HLC\HLCController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Template_WA\TemplateController;
use App\Http\Controllers\WaLog\WaLogController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});
// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/user', [DashboardController::class, 'dashboardUser'])->middleware(['auth', 'verified'])->name('dashboard.user');

Route::get('/Diklat', [DiklatController::class, 'index'])->name('diklat.home');
Route::get('/Diklat/create', [DiklatController::class, 'create'])->name('diklat.create');
Route::post('/Diklat/store', [DiklatController::class, 'store'])->name('diklat.store');
Route::get('/Diklat/pdf/preview/{id}', [DiklatController::class, 'preview'])->name('diklat.preview');
Route::get('/Diklat/edit/{id}', [DiklatController::class, 'edit'])->name('diklat.edit');
Route::put('/Diklat/update/{id}', [DiklatController::class, 'update'])->name('diklat.update');
Route::delete('/Diklat/destroy/{id}', [DiklatController::class, 'destroy'])->name('diklat.destroy');

// Persetujuan
// Eksternal
Route::get('/Persetujuan', [EksternalAdminController::class, 'Persetujuan'])->name('persetujuan.index');
Route::get('/Persetujuan/Eksternal', [EksternalAdminController::class, 'EksternalAdmin'])->name('persetujuan.eksternal');
Route::put('/Persetujuan/Eksternal/konfirmasi/{id}', [NonFormalController::class, 'approveKehadiran'])->name('konfirmasi.persetujuan.eksternal');
// HLC
Route::get('/Persetujuan/HLC', [HLCAdminController::class, 'index'])->name('persetujuan.hlc');
Route::put('/Persetujuan/HLC/konfirmasi/{id}', [HLCController::class, 'approveKehadiran'])->name('konfirmasi.persetujuan.hlc');

// halaman lihat Internal
Route::get('/DiklatInternal/user', [InternalController::class, 'index'])->name('diklat.internal.index');

// Appprove HLC dan Eksternal
Route::get('/Approve/Diklat', [ApprovDiklateController::class, 'index'])->name('diklat.approve.index');
Route::put('/diklat/{id}/approve', [ApprovDiklateController::class, 'approve'])->name('diklat.approve');
Route::put('/diklat/{id}/reject', [ApprovDiklateController::class, 'reject'])->name('diklat.reject');

//Materi Diklat
// Route::get('/Materi', [MateriController::class, 'index']);
Route::get('/Materi', [MateriController::class, 'index']);
Route::get('/Materi/folder/{folderId}', [MateriController::class, 'index']);
Route::get('/Materi/view', [MateriController::class, 'getAll']);
Route::post('/Materi/store', [MateriController::class, 'store']);
Route::put('/Materi/verify/{id}', [MateriController::class, 'verify']);
Route::put('/Materi/reject/{id}', [MateriController::class, 'reject']);
Route::delete('/Materi/delete/{id}', [MateriController::class, 'delete']);

//Internal
Route::get('/RencanaDiklat/RPT/PF', [DiklatInternalController::class, 'index'])->name('PF.index');
Route::post('/RencanaDiklat/RPT/PF/store', [DiklatInternalController::class, 'storeProgram'])->name('PF.store');
Route::delete('/RencanaDiklat/RPT/PF/delete/{id}', [DiklatInternalController::class, 'destroyProgram'])->name('PF.destroy');
Route::delete('/RencanaDiklat/RPT/PF/detail/delete/{id}', [DiklatInternalController::class, 'destroyDetail'])->name('PF.destroy-detail');
Route::post('/RencanaDiklat/RPT/PF/DetailStore', [DiklatInternalController::class, 'storeDetail'])->name('diklat.detail-internal');
// aksi detail internal
Route::get('/RencanaDiklat/Internal/detail/aksi/{id}', [DiklatInternalController::class, 'aksi'])->name('aksi-internal');
// Program Detail Internal
Route::get('/RencanaDiklat/Internal/detail/periode/{id}', [DiklatInternalController::class, 'periode'])->name('periode-internal');
Route::post('/RencanaDiklat/Internal/detail/periode/store', [DiklatInternalController::class, 'storePeriode'])->name('periode-internal.store');
Route::delete('/RencanaDiklat/Internal/detail/periode/delete/{id}', [DiklatInternalController::class, 'destroyPeriod'])->name('periode-internal.delete');
// PostTest
Route::get('/DiklatInternal/pree/{detailId}', [PostPreeController::class, 'preTest']);
Route::get('/DiklatInternal/post/{detailId}', [PostPreeController::class, 'postTest']);
Route::post('/DiklatInternal/preetest', [PostPreeController::class, 'savePre']);
Route::post('/DiklatInternal/posttest', [PostPreeController::class, 'savePost']);
// Route::get('/DiklatInternal/evaluasi', [PostPreeController::class, 'openEvaluasiByToken']);
// user post and pree
Route::get('/DiklatInternal/test/{type}/{detail_id}', [PostPreeController::class, 'showTest']);
Route::post('/DiklatInternal/test/submit', [PostPreeController::class, 'submitTest']);
// by token
Route::post('/DiklatInternal/periode/start', [PostPreeController::class, 'startPeriode']);
Route::post('/DiklatInternal/periode/end', [PostPreeController::class, 'endPeriode']);
Route::get('/test/token/evaluasi/{token}', [PostPreeController::class, 'openEvaluasiByToken']);
Route::get('/test/token/{type}/{token}', [PostPreeController::class, 'openByToken']);
// post evaluasi
Route::post('/test/evaluasi/post', [PostPreeController::class, 'submitEvaluasi']);
// Route Detail Periode
Route::get('/DiklatInternal/detailperiod/list/{detail_id}', [DetailPeriodeController::class, 'index'])->name('Detail.periode');
Route::post('/DiklatInternal/detailperiod/list/store', [DetailPeriodeController::class, 'store'])->name('Detail.periode-store');
Route::delete('/DiklatInternal/detailperiod/list/delete', [DetailPeriodeController::class, 'bulkDelete'])->name('Detail.periode-store');
// Presensi Detail
Route::get('/DiklatInternal/detail/presensi/{periode_id}', [PresensiDetailController::class, 'index']);
// Template Pembahasan Sertifikat
Route::get('/DiklatInternal/detail/pembahasan/template/{periode}', [SertifikatController::class, 'template']);
Route::post('/DiklatInternal/detail/pembahasan/template/store', [SertifikatController::class, 'storeTemplate']);
Route::post('/sertifikat/generate/{peserta}', [SertifikatController::class, 'generate']);
Route::get('/sertifikat/download/{peserta}', [SertifikatController::class, 'download']);

// Dokumentasi
Route::get('/DetailInternal/Dokumentasi/view/{periode_id}', [DokumentasiController::class, 'index']);
Route::post('/DetailInternal/Dokumentasi/store', [DokumentasiController::class, 'storeDokumentasi']);

// Evaluasi
Route::get('/Diklat/Evaluasi', [EvaluasiController::class, 'index']);
Route::get('/Diklat/Evaluasi/detail/{id}', [EvaluasiController::class, 'show']);

// Laporan
Route::get('/Laporan/Diklat', [ReportController::class, 'index'])->name('laporan.diklat');
// generate laporan excel
Route::get('/Laporan/Diklat/Export', [GenerateReportController::class, 'generateReport'])->name('laporan.diklat.export');

//Pendidikan Non Formal / Eksternal
Route::get('/RencanaDiklat/RPT/PN', [NonFormalController::class, 'index'])->name('Diklat.eksternal');
Route::post('/RencanaDiklat/RPT/PN/Program', [NonFormalController::class, 'storeProgram'])->name('Diklat.eksternal-program');
Route::post('/RencanaDiklat/RPT/PN/Detail', [NonFormalController::class, 'storeDetail'])->name('Diklat.eksternal-detail');
Route::put('/RencanaDiklat/RPT/PN/Detail/{id}', [NonFormalController::class, 'updateDetail'])->name('Diklat.eksternal-detail-update');
Route::delete('/RencanaDiklat/RPT/PN/Detail/{id}', [NonFormalController::class, 'destroyDetail'])->name('Diklat.eksternal-detail-destroy');
Route::delete('/RencanaDiklat/RPT/PN/program/{id}', [NonFormalController::class, 'destroyProgram'])->name('Diklat.eksternal-program-destroy');
Route::post('/diklat-eksternal/upload-bukti/{id}', [NonFormalController::class, 'uploadBukti'])->name('diklat.eksternal.upload-bukti');

//jadwal diklat
Route::get('/JadwalDiklat/Internal', [JadwalInternalController::class, 'index'])->name('jadwal.internal');
Route::get('/JadwalDiklat/Histori', [JadwalInternalController::class, 'history'])->name('jadwal.history');
Route::post('/jadwal-internal/send-wa', [JadwalInternalController::class, 'sendWhatsappNotification'])->name('jadwal.send-wa');

// ABSEN
Route::post('/diklat-eksternal/{id}/hadir',[NonFormalController::class, 'hadir'])->name('diklat.eksternal.absen');
Route::post('/diklat-hlc/{id}/hadir',[HLCController::class, 'hadirHLC'])->name('diklat.hlc.absen');

// Nomor HP Karyawan
Route::get('/NoHP', [NohpController::class, 'index'])->name('nohp.index');
Route::post('/NoHP/store', [NohpController::class, 'store'])->name('nohp.store');
Route::delete('/NoHP/delete/{id}', [NohpController::class, 'destroy'])->name('nohp.destroy');
Route::get('/NoHP/user', [NohpController::class, 'userrequest'])->name('nohp.userrequest');
Route::post('/NoHP/user/store', [NohpController::class, 'storeuser'])->name('nohp.store.user');

// template WA
Route::get('/Template/WA', [TemplateController::class, 'index'])->name('template.index');
Route::post('/Template/WA/store', [TemplateController::class, 'store'])->name('template.store');
Route::delete('/Template/WA/delete/{id}', [TemplateController::class, 'destroy'])->name('template.destroy');

// Settings UI
Route::get('/Settings', [SettingsController::class, 'index'])->name('settings.index');

//Silabus
Route::get('/silabus/diklat', [SilabusController::class, 'index']);

// HLC
Route::get('/HLC/Home/manajemen', [HLCController::class, 'index'])->name('diklat.hlc.admin');
Route::post('/HLC/Home/storeProgram', [HLCController::class, 'storeProgram'])->name('diklat.hlc.admin.store-program');
Route::post('/HLC/Home/storeDetail', [HLCController::class, 'storeDetail'])->name('diklat.hlc.admin.store-detail');
Route::post('/HLC/Home/updateProgram/{id}', [HLCController::class, 'updateProgram'])->name('diklat.hlc.admin.update-program');
Route::post('/HLC/Home/updateDetail/{id}', [HLCController::class, 'updateDetail'])->name('diklat.hlc.admin.update-detail');
Route::post('/HLC/Home/destroyProgram/{id}', [HLCController::class, 'destroyProgram'])->name('diklat.hlc.admin.destroy-program');
Route::post('/HLC/Home/destroyDetail/{id}', [HLCController::class, 'destroyDetail'])->name('diklat.hlc.admin.destroy-detail');
Route::post('/diklat-hlc/upload-bukti/{id}', [HLCController::class, 'uploadBuktiHLC'])->name('diklat.hlc.upload-bukti');


// User
// HLC
Route::get('/HLC/Home/user', [InboxController::class, 'index'])->name('diklat.inbox.user');
Route::post('/HLC/Home/konfirmasi/{id}', [InboxController::class, 'setujuRekomendasihlc'])->name('diklat.hlc.setujuRekomendasi');
Route::post('/HLC/Home/tolak/{id}', [InboxController::class, 'tolakRekomendasihlc'])->name('diklat.hlc.tolakRekomendasi');
Route::post('/Eksternal/Home/konfirmasi/{id}', [InboxController::class, 'setujuRekomendasieksternal'])->name('diklat.eksternal.setujuRekomendasi');
Route::post('/Eksternal/Home/tolak/{id}', [InboxController::class, 'tolakRekomendasieksternal'])->name('diklat.eksternal.tolakRekomendasi');
// impersonate response user
Route::post('/impersonation/respond/{requestId}', [InboxController::class, 'respondImpersonate'])->name('impersonation.respond');

// Master Data
Route::get('/MasterData/home', [MasterDataController::class, 'index'])->name('masterdata.home');
Route::get('/MasterData/create', [MasterDataController::class, 'pages']);
Route::post('/MasterData/store', [MasterDataController::class, 'store']);
Route::put('/MasterData/update', [MasterDataController::class, 'updateTargetJam'])->name('update.masterdata');
Route::post('/MasterData/store/karyawan', [MasterDataController::class, 'storekaryawan'])->name('store.masterdata');
Route::put('/MasterData/update-karyawan/{id}', [MasterDataController::class, 'updatekaryawan']);
Route::delete('/MasterData/destroy-karyawan/{id}', [MasterDataController::class, 'destroykaryawan']);

// Super Admin
Route::middleware(['auth'])->group(function () {
    Route::get('/super-admin', [SuperAdminController::class, 'index'])->name('superadmin.index');
    Route::get('/super-admin/home', [SuperAdminController::class, 'home'])->name('superadmin.home');
    Route::post('/super-admin/roles', [SuperAdminController::class, 'storeRole'])->name('superadmin.storeRole');
    Route::post('/super-admin/assign/{user}', [SuperAdminController::class, 'assignRole'])->name('superadmin.assign');

    Route::get('/super-admin/users', [UserController::class, 'index'])->name('superadmin.users.index');
    Route::post('/super-admin/users/store', [UserController::class, 'store'])->name('superadmin.users.store');
    Route::put('/super-admin/users/reset-password/{id}', [UserController::class, 'resetPassword'])->name('superadmin.users.reset-password');
    Route::delete('/super-admin/users/destroy/{id}', [UserController::class, 'destroy'])->name('superadmin.users.destroy');

    // login as
    Route::post('/super-admin/login-as/{id}', [UserController::class, 'impersonate'])->name('superadmin.login-as');
    Route::get('/super-admin/impersonate-status/{requestId}', [UserController::class, 'checkImpersonateStatus'])->name('superadmin.impersonate-status');
    Route::post('/impersonation/leave', [UserController::class, 'stopImpersonate'])->name('impersonation.leave');
    
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
