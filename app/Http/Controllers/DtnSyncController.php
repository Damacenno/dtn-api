<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DtnSyncController extends Controller
{
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:64'],
            'messages' => ['present', 'array', 'max:100'],
            'messages.*.id' => ['required', 'string', 'max:64'],
            'messages.*.originDevice' => ['required', 'string', 'max:64'],
            'messages.*.code' => [
                'required',
                'string',
                Rule::in([
                    '001',
                    '002',
                    '003',
                    '004',
                    '005',
                    '006',
                    '007',
                    '008',
                ]),
            ],
            'messages.*.createdAt' => ['required', 'integer'],
            'messages.*.expiresAt' => ['required', 'integer'],
            'known_ids' => ['present', 'array', 'max:200'],
            'known_ids.*' => ['required', 'string', 'max:64'],
        ]);

        $now = now();

        $incoming = collect($validated['messages'])
            ->filter(
                fn($message) =>
                    $message['expiresAt'] > $now->valueOf()
            );

        DB::transaction(function () use ($incoming, $now) {
            $rows = $incoming->map(function ($message) use ($now) {
                return [
                    'message_id' => $message['id'],
                    'origin_device_id' => $message['originDevice'],
                    'code' => $message['code'],
                    'message_created_at' =>
                        Carbon::createFromTimestampMs(
                            $message['createdAt']
                        ),
                    'expires_at' =>
                        Carbon::createFromTimestampMs(
                            $message['expiresAt']
                        ),
                    'received_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->values()->all();

            if (!empty($rows)) {
                DB::table('dtn_messages')->insertOrIgnore($rows);
            }
        });

        $idsToCheck = array_values(array_unique(array_merge(
            $validated['known_ids'],
            $incoming->pluck('id')->all()
        )));

        $deliveredIds = DB::table('dtn_messages')
            ->whereIn('message_id', $idsToCheck)
            ->pluck('message_id')
            ->all();

        return response()->json([
            'success' => true,
            'delivered_ids' => $deliveredIds,
            'server_time' => now()->valueOf(),
        ]);
    }

    public function deliveredIds(Request $request)
    {
        $validated = $request->validate([
            'after_id' => ['sometimes', 'integer', 'min:0'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ]);

        $afterId = (int) ($validated['after_id'] ?? 0);
        $limit = (int) ($validated['limit'] ?? 500);

        $rows = DB::table('dtn_messages')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->get([
                'id',
                'message_id',
            ]);

        $nextCursor = $rows->isNotEmpty()
            ? (int) $rows->last()->id
            : $afterId;

        return response()->json([
            'success' => true,
            'delivered_ids' => $rows->pluck('message_id')->all(),
            'next_cursor' => $nextCursor,
            'has_more' => $rows->count() === $limit,
        ]);
    }
}
