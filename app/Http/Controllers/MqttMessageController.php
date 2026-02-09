<?php

namespace App\Http\Controllers;

use App\Services\MqttMessageIngestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MqttMessageController extends Controller
{
    public function __construct(private MqttMessageIngestService $ingestService)
    {
    }

    /**
     * Ingest MQTT message data from broker webhook.
     * Expected fields: username (project_key), clientid (device_id), topic, payload, qos, retain
     */
    public function store(Request $request)
    {
        Log::info('MQTT MESSAGE RAW', [
            'all' => $request->all(),
            'input' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);

        $projectKey = $request->input('username');
        $deviceId = $request->input('clientid');
        $topicName = $request->input('topic');

        if (!$projectKey || !$deviceId || !$topicName) {
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        $payload = $request->input('payload');
        $qos = (int) $request->input('qos', 0);
        $retained = (int) $request->input('retain', 0) === 1;
        $createdAt = $request->input('timestamp')
            ? Carbon::parse($request->input('timestamp'))
            : Carbon::now();

        try {
            $message = $this->ingestService->ingest(
                $projectKey,
                $deviceId,
                $topicName,
                $payload,
                $qos,
                $retained,
                $createdAt
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Project or device not found'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        return response()->json([
            'status' => 'stored',
            'message_id' => $message->id,
        ]);
    }

}