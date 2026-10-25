<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AttachmentService
{
    public function store(UploadedFile $file, object $model, User $user): Attachment
    {
        $disk = 'local';
        $path = $file->store('attachments/'.$user->owner_id, $disk);

        return $model->attachments()->create(['owner_id' => $user->owner_id, 'disk' => $disk, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize(), 'uploaded_by' => $user->id, 'created_at' => now()]);
    }
}
