<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function show(int $attachment)
    {
        $attachment = Attachment::withoutGlobalScopes()->findOrFail($attachment);
        abort_unless(auth()->user()?->owner_id === $attachment->owner_id, 403);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name, ['Content-Type' => $attachment->mime]);
    }
}
