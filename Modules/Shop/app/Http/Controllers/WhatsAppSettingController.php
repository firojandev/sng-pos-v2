<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Kstmostofa\LaravelWhatsApp\Exceptions\SidecarException;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;
use Kstmostofa\LaravelWhatsApp\Models\WaMessage;
use Kstmostofa\LaravelWhatsApp\Web\SidecarManager;

class WhatsAppSettingController extends Controller
{
    protected SidecarManager $sidecarManager;

    public function __construct()
    {
        $this->sidecarManager = new SidecarManager(config('laravel-whatsapp.web', []));
    }

    public function getSessionId(): string
    {
        $shopId = auth()->user()?->shop_id;

        return $shopId ? "shop_{$shopId}" : 'main';
    }

    /**
     * Show WhatsApp integration settings and connection status.
     */
    public function index(): View
    {
        $isInstalled = $this->sidecarManager->isInstalled();
        $isRunning = $this->sidecarManager->isRunning();
        $isReachable = $this->isReachable($isRunning);
        $sessionId = $this->getSessionId();

        $sessionStatus = 'disconnected';
        $qrCode = null;
        $sessionInfo = null;

        if ($isReachable) {
            try {
                $session = WhatsApp::web($sessionId);
                $state = $session->state();
                $sessionStatus = $state['status'] ?? 'disconnected';

                if ($sessionStatus === 'qr') {
                    $qrData = $session->qr();
                    $qrCode = $qrData['qr'] ?? null;
                } elseif ($sessionStatus === 'ready') {
                    $sessionInfo = $session->info();
                }
            } catch (\Throwable) {
                $sessionStatus = 'error';
            }
        }

        return view('shop::whatsapp-settings.index', compact(
            'isInstalled',
            'isRunning',
            'isReachable',
            'sessionStatus',
            'qrCode',
            'sessionInfo',
            'sessionId'
        ));
    }

    /**
     * Start sidecar and WhatsApp session to generate/retrieve QR code.
     */
    public function start(): JsonResponse
    {
        try {
            if (! $this->sidecarManager->isInstalled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'হোয়াটসঅ্যাপ সাইডকার ইনস্টল করা নেই। টার্মিনালে `php artisan whatsapp:sidecar:install` কমান্ড চালান।',
                ], 422);
            }

            if (! $this->sidecarManager->isRunning()) {
                try {
                    $this->sidecarManager->start();
                    sleep(2);
                } catch (SidecarException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'সাইডকার চালু করতে ব্যর্থ হয়েছে: '.$e->getMessage(),
                    ], 500);
                }
            }

            $session = WhatsApp::web($this->getSessionId());
            $startResponse = $session->start();
            $state = $session->state();
            $status = $state['status'] ?? ($startResponse['status'] ?? 'qr');
            $qr = $startResponse['qr'] ?? null;

            if (! $qr && $status === 'qr') {
                $qrData = $session->qr();
                $qr = $qrData['qr'] ?? null;
            }

            $info = $status === 'ready' ? $session->info() : null;

            return response()->json([
                'success' => true,
                'status' => $status,
                'qr' => $qr,
                'info' => $info,
                'message' => $status === 'ready'
                    ? 'হোয়াটসঅ্যাপ ইতিমধ্যে সংযুক্ত আছে।'
                    : 'QR কোড তৈরি হয়েছে, মোবাইল দিয়ে স্ক্যান করুন।',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'হোয়াটসঅ্যাপ সেশন শুরু করতে সমস্যা হয়েছে: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Poll current status of the WhatsApp session.
     */
    public function status(): JsonResponse
    {
        $isRunning = $this->sidecarManager->isRunning();
        $isReachable = $this->isReachable($isRunning);

        if (! $isReachable) {
            return response()->json([
                'success' => true,
                'is_running' => false,
                'status' => 'stopped',
                'message' => 'সাইডকার সার্ভিস বন্ধ আছে।',
            ]);
        }

        try {
            $session = WhatsApp::web($this->getSessionId());
            $state = $session->state();
            $status = $state['status'] ?? 'unknown';
            $qr = null;
            $info = null;

            if ($status === 'qr') {
                $qrData = $session->qr();
                $qr = $qrData['qr'] ?? null;
            } elseif ($status === 'ready') {
                $info = $session->info();
            }

            return response()->json([
                'success' => true,
                'is_running' => true,
                'status' => $status,
                'qr' => $qr,
                'info' => $info,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'is_running' => true,
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Disconnect / destroy the active WhatsApp session.
     */
    public function disconnect(): JsonResponse
    {
        try {
            $session = WhatsApp::web($this->getSessionId());
            $session->destroy();

            return response()->json([
                'success' => true,
                'message' => 'হোয়াটসঅ্যাপ সংযোগ সফলভাবে বিচ্ছিন্ন করা হয়েছে।',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'সংযোগ বিচ্ছিন্ন করতে সমস্যা হয়েছে: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a test message to verify the connection.
     */
    public function sendTestMessage(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $rawPhone = preg_replace('/[^0-9]/', '', (string) $request->input('phone'));
        if (empty($rawPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'সঠিক ফোন নম্বর প্রদান করুন।',
            ], 422);
        }

        if (! str_starts_with($rawPhone, '88')) {
            $rawPhone = '88'.$rawPhone;
        }

        try {
            $sessionId = $this->getSessionId();
            $session = WhatsApp::web($sessionId);
            $state = $session->state();

            if (($state['status'] ?? '') !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'হোয়াটসঅ্যাপ এখনও সংযুক্ত নেই। অনুগ্রহ করে QR কোড স্ক্যান করুন।',
                ], 422);
            }

            $message = "অভিনন্দন! আপনার SNG POS এর ব্যক্তিগত হোয়াটসঅ্যাপ সংযোগ সফলভাবে সক্রিয় হয়েছে।\nতারিখ ও সময়: ".now()->format('d/m/Y h:i A');
            $result = $session->messages()->sendText('+'.$rawPhone, $message);

            // Store in database
            WaMessage::create([
                'shop_id' => auth()->user()?->shop_id,
                'backend' => 'web',
                'session_id' => $sessionId,
                'wa_message_id' => is_array($result) ? ($result['id'] ?? null) : null,
                'direction' => 'outbound',
                'chat_id' => $rawPhone.'@c.us',
                'from_id' => $sessionId,
                'to_id' => '+'.$rawPhone,
                'type' => 'text',
                'body' => $message,
                'payload' => ['is_test' => true, 'result' => $result],
                'status' => 'sent',
                'ack' => 1,
                'wa_timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'টেস্ট বার্তা সফলভাবে পাঠানো হয়েছে!',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'টেস্ট বার্তা পাঠাতে সমস্যা হয়েছে: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if the WhatsApp sidecar is reachable over HTTP.
     */
    protected function isReachable(bool $isRunning): bool
    {
        if (! $isRunning) {
            return false;
        }

        try {
            return WhatsApp::webClient()->ping();
        } catch (\Throwable) {
            return false;
        }
    }
}
