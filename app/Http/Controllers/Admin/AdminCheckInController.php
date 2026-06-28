<?php

namespace App\Http\Controllers\Admin;

use App\Features\CheckIns\Actions\ConfirmMemberQrCheckInAction;
use App\Features\CheckIns\Actions\PreviewMemberQrCheckInAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmQrCheckInRequest;
use App\Http\Requests\Admin\ScanQrRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use RuntimeException;

class AdminCheckInController extends Controller
{
    public function scan(ScanQrRequest $request, PreviewMemberQrCheckInAction $previewMemberQr): RedirectResponse
    {
        return $this->preview($request, $previewMemberQr);
    }

    public function preview(ScanQrRequest $request, PreviewMemberQrCheckInAction $previewMemberQr): RedirectResponse
    {
        try {
            $token = $request->validated('token');
            $preview = $previewMemberQr->handle($token);
            $previewKey = (string) Str::uuid();

            $request->session()->put('admin_check_in_preview_tokens.'.$previewKey, $token);
            $preview['preview_key'] = $previewKey;
        } catch (RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return back()
            ->with('status', 'QR valid. Cek data member lalu konfirmasi tindakan.')
            ->with('check_in_preview', $preview);
    }

    public function confirm(ConfirmQrCheckInRequest $request, ConfirmMemberQrCheckInAction $confirmMemberQr): RedirectResponse
    {
        $previewKey = (string) $request->validated('preview_key');
        $token = $request->session()->get('admin_check_in_preview_tokens.'.$previewKey);

        if (! is_string($token) || blank($token)) {
            return back()
                ->with('status', 'Preview check-in sudah tidak berlaku. Scan ulang QR member.')
                ->with('status_kind', 'error');
        }

        try {
            $result = $confirmMemberQr->handle(
                $token,
                (string) $request->validated('action'),
                $request->user()->id,
                $request->integer('member_package_session_id') ?: null,
                $previewKey,
            );
        } catch (RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error');
        }

        $request->session()->forget('admin_check_in_preview_tokens.'.$previewKey);

        return back()->with('status', 'Tindakan check-in berhasil untuk '.$result['member_name'].'.');
    }
}
