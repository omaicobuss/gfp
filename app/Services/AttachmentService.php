<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService
{
    /**
     * Store an attachment file and associate it with a model (FR-016, FR-018, FR-019).
     */
    public static function storeAttachment(
        UploadedFile $file,
        Model $attachable,
        string $type,
        int $userId
    ): Attachment {
        // Se já existir um anexo do mesmo tipo para o registro, remove o anterior (substituição limpa)
        $existing = Attachment::where('attachable_type', get_class($attachable))
            ->where('attachable_id', $attachable->getKey())
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
        }

        // Armazena no disco local privado (storage/app/attachments/...)
        $directory = "attachments/{$userId}/{$type}";
        $path = $file->store($directory, 'local');

        return Attachment::create([
            'user_id' => $userId,
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->getKey(),
            'type' => $type,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Download or stream the attachment safely (FR-024).
     */
    public static function download(Attachment $attachment): StreamedResponse
    {
        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
            ]
        );
    }
}
