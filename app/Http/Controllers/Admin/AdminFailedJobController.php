<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminFailedJobController extends Controller
{
    public function index()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(20);

        return view('admin.failed-jobs.index', [
            'failedJobs' => $failedJobs,
        ]);
    }

    public function retry(string $id)
    {
        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', "Kuyruk işi (#{$id}) yeniden deneme kuyruğuna alındı.");
    }

    public function retryAll()
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'Tüm başarısız kuyruk işleri yeniden deneme kuyruğuna alındı.');
    }

    public function destroy(string $id)
    {
        Artisan::call('queue:forget', ['id' => $id]);

        return back()->with('success', "Başarısız iş (#{$id}) kuyruktan silindi.");
    }

    public function destroyAll()
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'Tüm başarısız işler kalıcı olarak temizlendi.');
    }
}