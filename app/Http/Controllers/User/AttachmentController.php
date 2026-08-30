<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Download an attachment ensuring strict user ownership (FR-024).
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        if ($attachment->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a este anexo.');
        }

        return AttachmentService::download($attachment);
    }

    /**
     * Delete an attachment ensuring strict user ownership (FR-024).
     */
    public function destroy(Attachment $attachment): RedirectResponse
    {
        if ($attachment->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $attachment->delete();

        return back()->with('status', 'Anexo removido com sucesso.');
    }
}
