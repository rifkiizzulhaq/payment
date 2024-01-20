<?php

namespace App\Http\Controllers;

use App\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class DonationController extends Controller
{
    public function index()
    {
        $donations = Donation::orderBy('id', 'desc')->paginate(8);
        return view('home', compact('donations'));
    }

    public function create()
    {
        return view('donation');
    }

    public function __construct()
        {
            \Midtrans\Config::$serverKey = config('services.midtrans.serverKey');
            \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');
            \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');
            \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');
        }

    public function store(Request $request)
    {
        \DB::transaction(function() use ($request)
        {
            $donation = Donation::create([
                'donation_code' => 'SANBOX-' . uniqid(), // 'SANBOX-' . uniqid() => 'SANBOX-5f8b4b3b2b3d1
                'donor_name' => $request->donor_name,
                'donor_email' => $request->donor_email,
                'donation_type' => $request->donation_type,
                'amount' => floatval($request->amount),
                'note' => $request->note,
            ]);

            $payload = [
                'transaction_details' => [
                    'order_id' => $donation->donation_code,
                    'gross_amount' => $donation->amount,
                ],
                'customer_details' => [
                    'first_name' => $donation->donor_name,
                    'email' => $donation->donor_email,
                ],
                'item_details' => [
                    [
                        'id' => $donation->donation_type,
                        'price' => $donation->amount,
                        'quantity' => 1,
                        'name' => ucwords(str_replace('_', ' ', $donation->donation_type))
                    ]
                ]
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            $donation->snap_token = $snapToken;
            $donation->save();

            $this->response['snap_token'] = $snapToken;
        });

        return response()->json($this->response);
    }

    public function notification(){
        $notif = new \Midtrans\Notification();
        DB::transaction(function() use ($notif){
            $transcationStatus = $notif->transaction_status;
            $paymentType = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraudStatus = $notif->fraud_status;
            $donation = Donation::where('donation_code', $orderId)->first();

            if($transcationStatus == 'capture'){
                if($paymentType == 'credit_card'){
                    if($fraudStatus == 'challenge'){
                        $donation->setStatusPending();
                    } else {
                        $donation->setStatusSuccess();
                    }
                }
            } elseif ($transcationStatus == 'settlement'){
                $donation->setStatusSuccess();
            } elseif ($transcationStatus == 'pending'){
                $donation->setStatusPending();
            } elseif ($transcationStatus == 'deny'){
                $donation->setStatusFailed();
            } elseif ($transcationStatus == 'expire'){
                $donation->setStatusExpired();
            } elseif ($transcationStatus == 'cancel'){
                $donation->setStatusFailed();

            }
        });

        return;
    }
}
