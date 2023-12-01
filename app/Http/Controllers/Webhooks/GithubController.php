<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Libraries\Result\Result;
use App\Libraries\Sync\Sync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GithubController extends Controller
{
    public function handle(Request $request)
    {
        // I have a javascript example from github
        // https://docs.github.com/en/webhooks/using-webhooks/handling-webhook-deliveries#javascript-example-write-the-code
        // But I have to do it in PHP

        // Accept the webhook with 202 Accepted
        $statusCode = 202;
        $response = response()->json([
            'message' => 'Accepted',
        ], $statusCode);
        $response->send();

        // Get the event type from the header
        $event = $request->header('X-GitHub-Event');
        $result = Result::ok();
        switch ($event) {
            case 'push':
                $result = $this->handlePushEvent($request);
            // I think that's all I need to handle for now
        }
        if ($result->isErr()) {
            Log::error('Failed to handle GitHub push event', $result->getErr());
        }
        return $result;
    }

    protected function handlePushEvent(Request $request): Result
    {
        // If there was a push, then we need to pull the articles from github
        $sync = Sync::getInstance();
        return $sync->downloadAndSync();
    }
}
