<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function __construct(protected User $model) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $members = $this->model
            ->query()
            ->select('id', 'name', 'idcard', 'phone', 'gender', 'birth_date', 'image', 'created_at')
            ->when($search !== '', function ($query) use ($search) {
                $normalizedSearch = strtolower(str_replace(' ', '', $search));

                $query->where(function ($q) use ($search, $normalizedSearch) {
                    $q->whereRaw("LOWER(REPLACE(name, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhereRaw("LOWER(REPLACE(idcard, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhereRaw("LOWER(REPLACE(phone, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('idcard', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(function ($member) {
                $member->image = $member->image ? url('storage/profile_images/'.$member->image) : null;

                return $member;
            });

        $user = Auth::guard('admin')->user();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
            ],
            'user' => $user,
        ]);
    }

    public function destroy($id)
    {
        info($id);

        return redirect()->route('members')->with('success', 'Member deleted successfully');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $fromDate = Carbon::parse($validated['from_date'])->startOfDay();
        $toDate = Carbon::parse($validated['to_date'])->endOfDay();
        $filename = "members_{$fromDate->toDateString()}_to_{$toDate->toDateString()}.csv";

        return response()->streamDownload(function () use ($fromDate, $toDate) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM lets Microsoft Excel display Burmese and other Unicode text correctly.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Name', 'ID Card', 'Phone', 'Gender', 'Birth Date', 'Registered Date']);

            $this->model
                ->query()
                ->select('name', 'idcard', 'phone', 'gender', 'birth_date', 'created_at')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->orderBy('created_at')
                ->chunk(500, function ($members) use ($output) {
                    foreach ($members as $member) {
                        fputcsv($output, [
                            $member->name,
                            $member->idcard,
                            $member->phone,
                            $member->gender,
                            $member->birth_date,
                            $member->created_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
