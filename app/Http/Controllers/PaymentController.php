<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Omnipay\Omnipay;
use App\BillingDetails;
use Illuminate\Http\Request;
use DrewM\MailChimp\MailChimp;
use App\Events\OneToOneConnection;
use Illuminate\Support\Facades\DB;
use Modules\Payment\Entities\Cart;
use Illuminate\Support\Facades\App;
use Modules\Gift\Entities\GiftCart;
use Modules\Survey\Entities\Survey;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Modules\Payment\Entities\Checkout;
use Illuminate\Support\Facades\Redirect;
use Modules\Appointment\Entities\Booking;
use Modules\CourseSetting\Entities\Course;
use Modules\Group\Events\GroupMemberCreate;
use Modules\Coupons\Entities\UserWiseCoupon;
use Modules\Affiliate\Events\ReferralPayment;
use Modules\Certificate\Entities\Certificate;
use Unicodeveloper\Paystack\Facades\Paystack;
use Bryceandy\Laravel_Pesapal\Facades\Pesapal;
use Modules\Payment\Entities\InstructorPayout;
use App\Library\SslCommerz\SslCommerzNotification;
use Modules\CourseSetting\Entities\CourseEnrolled;
use Modules\Coupons\Entities\UserWiseCouponSetting;
use Modules\Paytm\Http\Controllers\PaytmController;
use Modules\Payeer\Http\Controllers\PayeerController;
use Modules\Survey\Http\Controllers\SurveyController;
use Modules\Wallet\Http\Controllers\WalletController;
use Modules\BundleSubscription\Entities\BundleSetting;
use Modules\GoogleMeet\Events\MeetingAddAttendeeEvent;
use Modules\RazerMS\Http\Controllers\RazerMSController;
use Modules\BundleSubscription\Entities\BundleCoursePlan;
use Modules\Midtrans\Http\Controllers\MidtransController;
use Modules\Mobilpay\Http\Controllers\MobilpayController;
use Modules\Newsletter\Http\Controllers\AcelleController;
use Modules\Razorpay\Http\Controllers\RazorpayController;
use Modules\Instamojo\Http\Controllers\InstamojoController;
use Modules\MercadoPago\Http\Controllers\MercadoPagoController;
use Modules\GoogleCalendar\Events\GoogleCalendarEventAddAttendee;
use Modules\Invoice\Repositories\Interfaces\InvoiceRepositoryInterface;

class PaymentController extends Controller
{
    public $payPalGateway;

    public function __construct()
    {
        $this->middleware('maintenanceMode');

        $this->payPalGateway = Omnipay::create('PayPal_Rest');
        $this->payPalGateway->setClientId(getPaymentEnv('PAYPAL_CLIENT_ID'));
        $this->payPalGateway->setSecret(getPaymentEnv('PAYPAL_CLIENT_SECRET'));
        $this->payPalGateway->setTestMode(getPaymentEnv('IS_PAYPAL_LOCALHOST') == 'true'); //set it to 'false' when go live
    }

    public function makePlaceOrder(Request $request)
    {
        $carts = Cart::where('user_id', Auth::id())->count();
        if ($carts == 0) {
            return redirect('/');
        }
        $rules = [
            'old_billing' => 'required_if:billing_address,previous',
            'first_name' => 'required_if:billing_address,new',
            'last_name' => 'required_if:billing_address,new',
            'country' => 'required_if:billing_address,new',
            'address1' => 'required_if:billing_address,new',
            'phone' => 'required_if:billing_address,new',
            'email' => 'required_if:billing_address,new',
        ];
        $this->validate($request, $rules, validationMessage($rules));

        if ($request->billing_address == 'new') {
            $bill = BillingDetails::where('tracking_id', $request->tracking_id)->first();

            if (empty($bill)) {
                $bill = new BillingDetails();
            }

            $bill->user_id = Auth::id();
            $bill->tracking_id = $request->tracking_id;
            $bill->first_name = $request->first_name;
            $bill->last_name = $request->last_name;
            $bill->company_name = $request->company_name;
            $bill->country = $request->country;
            $bill->address1 = $request->address1;
            $bill->address2 = $request->address2;
            $bill->city = $request->city;
            $bill->state = (int)$request->state;
            $bill->zip_code = $request->zip_code;
            $bill->phone = $request->phone;
            $bill->email = $request->email;
            $bill->details = $request->details;
            $bill->payment_method = null;
            $bill->save();
        } else {

            $bill = BillingDetails::where('id', $request->old_billing)->first();
            if ($request->previous_address_edit == 1) {
                $bill->user_id = Auth::id();
                $bill->tracking_id = $request->tracking_id;
                $bill->first_name = $request->first_name;
                $bill->last_name = $request->last_name;
                $bill->company_name = $request->company_name;
                $bill->country = $request->country;
                $bill->address1 = $request->address1;
                $bill->address2 = $request->address2;
                $bill->city = $request->city;
                $bill->state = $request->state;

                $bill->zip_code = $request->zip_code;
                $bill->phone = $request->phone;
                $bill->email = $request->email;
                $bill->details = $request->details;
                $bill->payment_method = null;
                $bill->save();
            } else {
                $bill->tracking_id = $request->tracking_id;
                $bill->save();
            }

        }

        $tracking = Cart::where('user_id', Auth::id())->first()->tracking;
        $checkout_info = Checkout::where('tracking', $tracking)->where('user_id', Auth::id())->latest()->first();
        $carts = Cart::where('tracking', $checkout_info->tracking)->get();

        if ($checkout_info) {
            $checkout_info->billing_detail_id = $bill->id;
            $checkout_info->save();

            if ($checkout_info->purchase_price == 0) {
                $checkout_info->payment_method = 'None';
                $bill->payment_method = 'None';
                $checkout_info->save();
                foreach ($carts as $cart) {

                    if (isModuleActive('Gift') && $cart->is_gift == 1) {
                        $gift_cart = new GiftCart();
                        $gift_cart->course_id = $cart->course_id;
                        $gift_cart->user_id = $cart->user_id;
                        $gift_cart->price = $cart->price;
                        $gift_cart->instructor_id = $cart->instructor_id;
                        $gift_cart->tracking = $cart->tracking;
                        $gift_cart->gift_id = $cart->gift_id;
                        $gift_cart->save();

                        $cart->delete();
                    } else {
                        $this->directEnroll($cart->course_id, $checkout_info->tracking);
                    }

                    $cart->delete();
                }

                Toastr::success('Checkout Successfully Done', 'Success');
                if (Settings('frontend_active_theme') == 'tvt') {
                    return redirect('/');
                }
                return redirect(route('studentDashboard'));
            } else {
                return redirect()->route('orderPayment');

            }
        } else {
            Toastr::error("Something Went Wrong", 'Failed');
            return \redirect()->back();
        }
        //payment method start skip for now

    }

    public function payment()
    {
        try {
            $carts = Cart::where('user_id', Auth::id())->count();
            if ($carts == 0) {
                return redirect('/');
            }
            return view(theme('pages.payment'));
        } catch (\Exception$e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

        }
    }

    public function paymentSubmit(Request $request)
    {
        $type = session()->get('order_type');
        $checkout_info = Checkout::where('id', $request->id)->where('tracking', $request->tracking_id)->with('user')->first();

        if (!empty($checkout_info)) {
            if ($request->payment_method == "Sslcommerz") {
                $post_data = array();
                $post_data['total_amount'] = $checkout_info->purchase_price; # You cant not pay less than 10
                $post_data['currency'] = Settings('currency_code') ?? 'USD';
                $post_data['tran_id'] = uniqid(); // tran_id must be unique

                # CUSTOMER INFORMATION
                $post_data['cus_name'] = $request->first_name ?? 'Customer Name';
                $post_data['cus_email'] = $request->email ?? 'customer@mail.com';
                $post_data['cus_add1'] = $request->address1 ?? 'Customer Address';
                $post_data['cus_add2'] = $request->address2 ?? '';
                $post_data['cus_city'] = $request->city ?? 'Dhaka';
                $post_data['cus_state'] = "";
                $post_data['cus_postcode'] = $request->zip_code ?? '';
                $post_data['cus_country'] = $request->country ?? '';
                $post_data['cus_phone'] = $request->phone ?? '8801XXXXXXXXX';
                $post_data['cus_fax'] = "";

                # SHIPMENT INFORMATION
                $post_data['ship_name'] = "Store Test";
                $post_data['ship_add1'] = "Dhaka";
                $post_data['ship_add2'] = "Dhaka";
                $post_data['ship_city'] = "Dhaka";
                $post_data['ship_state'] = "Dhaka";
                $post_data['ship_postcode'] = "1000";
                $post_data['ship_phone'] = "";
                $post_data['ship_country'] = "Bangladesh";

                $post_data['shipping_method'] = "NO";
                $post_data['product_name'] = "Computer";
                $post_data['product_category'] = "Goods";
                $post_data['product_profile'] = "physical-goods";

                # OPTIONAL PARAMETERS
                $post_data['value_a'] = $checkout_info->id;
                $post_data['value_b'] = $checkout_info->tracking;
                $post_data['value_c'] = "ref003";
                $post_data['value_d'] = "ref004";

                #Before  going to initiate the payment order status need to update as Pending.
                $update_product = DB::table('orders')
                    ->where('transaction_id', $post_data['tran_id'])
                    ->updateOrInsert([
                        'user_id' => $checkout_info->user->id,
                        'tracking' => $checkout_info->tracking,
                        'name' => $post_data['cus_name'] ?? '',
                        'email' => $post_data['cus_email'] ?? '',
                        'phone' => $post_data['cus_phone'] ?? '',
                        'amount' => $post_data['total_amount'] ?? '',
                        'status' => 'Pending',
                        'address' => $post_data['cus_add1'] ?? '',
                        'transaction_id' => $post_data['tran_id'],
                        'currency' => $post_data['currency'],
                    ]);
                $sslc = new SslCommerzNotification();
                # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
                $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');
                $payment_options = \GuzzleHttp\json_decode($payment_options);

                if ($payment_options->status == "success") {
                    return Redirect::to($payment_options->data);
                } else {
                    Toastr::error('Something went wrong', 'Failed');
                    return Redirect::back();
                }

            } elseif ($request->payment_method == "PayPal") {

                try {
                    $response = $this->payPalGateway->purchase(array(
                        'amount' => convertCurrency(Settings('currency_code') ?? 'BDT', Settings('currency_code'), $checkout_info->purchase_price),
                        'currency' => Settings('currency_code'),
                        'returnUrl' => route('paypalSuccess'),
                        'cancelUrl' => route('paypalFailed'),

                    ))->send();

                    if ($response->isRedirect()) {
                        $response->redirect(); // this will automatically forward the customer
                    } else {
                        Toastr::error($response->getMessage(), trans('common.Failed'));
                        return \redirect()->back();
                    }
                } catch (\Exception$e) {
                    GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

                }
            } elseif ($request->payment_method == "Payeer") {

                try {
                    $payeer = new PayeerController();
                    $request->merge(['type' => 'Payment']);
                    $request->merge(['amount' => $checkout_info->purchase_price]);
                    $response = $payeer->makePayment($request);

                    if ($response) {
                        return \redirect()->to($response);
                    } else {
                        Toastr::error('Something went wrong', 'Failed');
                        return \redirect()->back();
                    }
                } catch (\Exception$e) {
                    GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

                }

            } elseif ($request->payment_method == "Midtrans") {

                try {
                    $midtrans = new MidtransController();
                    $request->merge(['type' => 'Payment']);
                    $request->merge(['amount' => $checkout_info->purchase_price]);
                    $response = $midtrans->makePayment($request);

                    if ($response) {
                        return $response;
                    } else {
                        Toastr::error('Something went wrong', 'Failed');
                        return \redirect()->back();
                    }
                } catch (\Exception$e) {
                    GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

                }

            } elseif ($request->payment_method == "MercadoPago") {
                $mercadoPagoController = new MercadoPagoController();
                $response = $mercadoPagoController->payment($request->all());
                return response()->json(['target_url' => $response]);
            } elseif ($request->payment_method == "Instamojo") {

                $amount = convertCurrency(Settings('currency_code') ?? 'BDT', 'INR', $checkout_info->purchase_price);
                $instamojo = new InstamojoController();
                $response = $instamojo->paymentProcess($amount);
                if ($response) {
                    return \redirect()->to($response);
                } else {
                    Toastr::error('Something went wrong', 'Failed');
                    return \redirect()->back();
                }

            } elseif ($request->payment_method == "Mobilpay") {

                $amount = convertCurrency(Settings('currency_code') ?? Settings('currency_code'), 'RON', $checkout_info->purchase_price);
                $mobilpay = new MobilpayController();
                $mobilpay->paymentProcess($amount);

            } elseif ($request->payment_method == 'Jazz Cash') {
                try {
                    $jazz = new \Modules\JazzCash\Repositories\JazzcashRepository();
                    $payment_info = [
                        "billref" => $checkout_info->tracking,
                        "description" => "Purchase Payment",
                        "email" => Auth::user()->email,
                        "mobile" => Auth::user()->phone,
                    ];
                    session()->put('from', 'purchase');
                    $result = $jazz->getArray($payment_info, $request->deposit_amount);
                    $post_url = $jazz->getPostUrl();

                    return view('jazzcash::payment', compact('post_url'));
                } catch (\Exception $e) {
                    Toastr::error($e->getMessage(), 'Error');
                    return back();
                }
            } elseif ($request->payment_method == 'Coinbase') {
                $coinbase = new \Modules\Coinbase\Repositories\CoinbaseRepository();
                $payment_info = [
                    "name" => "Purchase payment",
                    "description" => "Payment for Course or Class Purchase",
                    "currency" => "USD",
                    "charge_type" => "purchase"

                ];
                $result = $coinbase->makePayment($payment_info, $checkout_info->purchase_price);

                session()->get('pay_from', 'purchase');
                session()->get('charge_id', $result['data']['id']);


                if (isset($result['data']) && isset($result['data']['hosted_url'])) {
                    return redirect()->to($result['data']['hosted_url']);
                } else {
                    Toastr::error("Something went wrong", 'Error');
                    return back();
                }
            } elseif ($request->payment_method == "Stripe") {

                $request->validate([
                    'stripeToken' => 'required',
                ]);
                $token = $request->stripeToken ?? '';
                $gatewayStripe = Omnipay::create('Stripe');
                $gatewayStripe->setApiKey(getPaymentEnv('STRIPE_SECRET'));

                //$formData = array('number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2030', 'cvv' => '123');
                $response = $gatewayStripe->purchase(array(
                    'amount' => convertCurrency(Settings('currency_code') ?? 'BDT', Settings('currency_code'), $checkout_info->purchase_price),
                    'currency' => Settings('currency_code'),
                    'token' => $token,
                ))->send();

                if ($response->isRedirect()) {
                    // redirect to offsite payment gateway
                    $response->redirect();
                } elseif ($response->isSuccessful()) {
                    // payment was successful: update database

                    $payWithStripe = $this->payWithGateWay($response->getData(), "Stripe", $user = null, session()->get('invoice'));
                    if ($payWithStripe) {
                        Toastr::success('Payment done successfully', 'Success');
                        if (Settings('frontend_active_theme') == 'tvt') {
                            return redirect('/');
                        }
                        return redirect(route('studentDashboard'));
                    } else {
                        Toastr::error('Something Went Wrong', 'Error');
                        return \redirect()->back();
                    }
                } else {

                    if ($response->getCode() == "amount_too_small") {
                        $amount = round(convertCurrency(Settings('currency_code'), strtoupper(Settings('currency_code') ?? 'BDT'), 0.5));
                        $message = "Amount must be at least " . Settings('currency_symbol') . ' ' . $amount;
                        Toastr::error($message, 'Error');
                    } else {
                        Toastr::error($response->getMessage(), 'Error');
                    }
                    return redirect()->back();
                }

            } //payment getway
            elseif ($request->payment_method == "RazorPay") {

                if (empty($request->razorpay_payment_id)) {
                    Toastr::error('Something Went Wrong', 'Error');
                    return \redirect()->back();
                }

                $payment = new RazorpayController();
                $response = $payment->payment($request->razorpay_payment_id);

                if ($response['type'] == "error") {
                    Toastr::error($response['message'], 'Error');
                    return \redirect()->back();
                }

                $payWithRazorPay = $this->payWithGateWay($response['response'], "RazorPay", $user = null, session()->get('invoice'));

                if ($payWithRazorPay) {
                    Toastr::success('Payment done successfully', 'Success');
                    if (Settings('frontend_active_theme') == 'tvt') {
                        return redirect('/');
                    }
                    return redirect(route('studentDashboard'));
                } else {
                    Toastr::error('Something Went Wrong', 'Error');
                    return \redirect()->back();
                }

            } //payment getway
            elseif ($request->payment_method == "PayTM") {

                $userData = [
                    'user' => $checkout_info['tracking'],
                    'mobile' => $checkout_info->billing->phone,
                    'email' => $checkout_info->billing->email,
                    'amount' => convertCurrency(Settings('currency_code') ?? 'BDT', 'INR', $checkout_info->purchase_price),
                    'order' => $checkout_info->billing->phone . "_" . rand(1, 1000),
                ];

                $payment = new PaytmController();
                return $payment->payment($userData);

            } //payment getway

            elseif ($request->payment_method == "PayStack") {

                try {
                    return Paystack::getAuthorizationUrl()->redirectNow();

                } catch (\Exception$e) {
                    GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

                }

            } elseif ($request->payment_method == "Pesapal") {

                try {
                    $paymentData = [
                        'amount' => $checkout_info->purchase_price,
                        'currency' => Settings('currency_code'),
                        'description' => 'Payment',
                        'type' => 'MERCHANT',
                        'reference' => 'Payment|' . $checkout_info->purchase_price,
                        'first_name' => Auth::user()->first_name,
                        'last_name' => Auth::user()->last_name,
                        'email' => Auth::user()->email,
                    ];

                    $iframe_src = Pesapal::getIframeSource($paymentData);

                    return view('laravel_pesapal::iframe', compact('iframe_src'));
                } catch (\Exception$e) {
                    GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
                }

            } elseif ($request->payment_method == "RazerMS") {

                try {
                    $payment = new RazerMSController();
                    $url = $payment->generatePaymentUrl($checkout_info->purchase_price, 'payment');
                    return redirect($url);
                } catch (\Exception $e) {
                    GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

                }
            } elseif ($request->payment_method == "Wallet") {

                $payment = new WalletController();
                $response = $payment->payment($request);

                if ($response['type'] == "error") {
                    Toastr::error($response['message'], 'Error');
                    return \redirect()->back();
                }

                $payWithWallet = $this->payWithGateWay($response['response'], "Wallet", $user = null, session()->get('invoice'));

                if ($payWithWallet) {
                    Toastr::success('Payment done successfully', 'Success');
                    if (Settings('frontend_active_theme') == 'tvt') {
                        return redirect('/');
                    }

                    return redirect(route('studentDashboard'));
                } else {
                    Toastr::error('Something Went Wrong', 'Error');
                    return \redirect()->back();
                }

            } elseif ($request->payment_method == 'Authorize.Net') {
                $authorizeNet = new  \Modules\AuthorizeNet\Repositories\AuthorizeNetRepository();
                $response = $authorizeNet->payNow($request, $request->deposit_amount);
                if ($response != null) {
                    if ($response->getMessages()->getResultCode() == "Ok") {
                        $tresponse = $response->getTransactionResponse();
                        if ($tresponse != null && $tresponse->getMessages() != null) {
                            $payWithAuthore = $this->payWithGateWay($response, "Authore.Net", $user = null, session()->get('invoice'));
                            if ($payWithAuthore) {
                                Toastr::success('Payment done successfully', 'Success');
                                if (Settings('frontend_active_theme') == 'tvt') {
                                    return redirect('/');
                                }
                                return redirect(route('studentDashboard'));
                            } else {
                                Toastr::error('Something Went Wrong', 'Error');
                                return \redirect()->back();
                            }
                        } else {
                            $msg = 'here were some issue with the payment. Please try again later.';
                            if ($tresponse->getErrors() != null) {
                                $msg = $tresponse->getErrors()[0]->getErrorText();
                            }
                            Toastr::error($msg, 'Failed');
                            return Redirect::back();
                        }
                    } else {
                        Toastr::error('Something went wrong', 'Failed');
                        return Redirect::back();
                    }
                } else {
                    Toastr::error('Something went wrong', 'Failed');
                    return Redirect::back();
                }
            } elseif ($request->payment_method == 'Braintree') {
                $braintree = new  \Modules\Braintree\Repositories\BraintreeRepository();
                $result = $braintree->payNow($request, $request->deposit_amount);
                if ($result->success) {

                    $payWithBrainTree = $this->payWithGateWay($result, "Braintree", $user = null, session()->get('invoice'));

                    if ($payWithBrainTree) {
                        Toastr::success('Payment done successfully', 'Success');
                        return redirect(route('studentDashboard'));
                    } else {
                        Toastr::error('Something went wrong', 'Failed');
                        return Redirect::back();
                    }
                } else {
                    Toastr::error('Something went wrong', 'Failed');
                    return Redirect::back();
                }
            } elseif ($request->payment_method == 'Mollie') {
                $mollie = new \Modules\Mollie\Repositories\MollieRepository();

                $pay_info = [
                    'success_url' => route('mollieSuccess'),
                    "order_id" => $checkout_info->traking,
                    "amount" => $checkout_info->purchase_price,
                    "details" => "Purchase Payment",
                ];
                $result = $mollie->preparePayment($pay_info);
                session()->put('mollie_id', $result->id);
                session()->put('checkout_id', $request->id);
                session()->put('tracking', $request->tracking_id);
                return redirect($result->getCheckoutUrl(), 303);
            } elseif ($request->payment_method == 'Flutterwave') {
                $flutterwave = new \Modules\Flutterwave\Repositories\FlutterwaveRepository();
                $pay_info = [
                    "name" => Auth::user()->name,
                    "phone" => Auth::user()->phone,
                    "email" => Auth::user()->email,
                    "title" => "Course Purchase payment",
                    "description" => "Payment of course purchase",
                    "webhook" => route('flutterwavePurchaseWebhook')
                ];
                $payment = $flutterwave->payNow($pay_info, $checkout_info->purchase_price);
                if ($payment->status == 'success') {
                    return redirect()->to($payment->data->link);
                } else {
                    Toastr::error("Something went wrong", 'Error');
                    return back();
                }
            }

        } else {
            Toastr::error('Something went wrong', 'Failed');
            return Redirect::back();
        }

    }

    public function directEnroll($id, $tracking = '')
    {
        try {
            $success = trans('lang.Enrolled') . ' ' . trans('lang.Successfully');
            $user = Auth::user();


            $checkout = new Checkout();

            $checkout->discount = 0;
            $checkout->coupon_id = null;
            $checkout->purchase_price = 0;


            $checkout->tracking = $tracking;
            $checkout->user_id = Auth::id();
            $checkout->price = 0;
            $checkout->status = 0;
            $checkout->save();


            $this->payWithGateway([], 'None', $user);
            return response()->json([
                'success' => $success,
            ]);
        } catch (\Exception$e) {
            return response()->json(['error' => trans("lang.Operation Failed")]);
        }

    }

    public function paypalSuccess(Request $request)
    {

        // Once the transaction has been approved, we need to complete it.
        if ($request->input('paymentId') && $request->input('PayerID')) {
            $transaction = $this->payPalGateway->completePurchase(array(
                'payer_id' => $request->input('PayerID'),
                'transactionReference' => $request->input('paymentId'),
            ));
            $response = $transaction->send();

            if ($response->isSuccessful()) {
                // The customer has successfully paid.
                $arr_body = $response->getData();
                $payWithPapal = $this->payWithGateWay($arr_body, "PayPal", $user = null, session()->get('invoice'));
                if ($payWithPapal) {
                    Toastr::success('Payment done successfully', 'Success');
                    if (Settings('frontend_active_theme') == 'tvt') {
                        return redirect('/');
                    }
                    return redirect(route('studentDashboard'));
                } else {
                    Toastr::error('Something Went Wrong', 'Error');
                    if (Settings('frontend_active_theme') == 'tvt') {
                        return redirect('/');
                    }
                    return redirect(route('studentDashboard'));
                }

            } else {
                $msg = str_replace("'", " ", $response->getMessage());
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        } else {
            Toastr::error('Transaction is declined');
            return redirect()->back();
        }

    }

    public function paypalFailed()
    {
        Toastr::error('User is canceled the payment.', 'Failed');
        if (Settings('frontend_active_theme') == 'tvt') {
            return redirect('/');
        }
        return redirect(route('studentDashboard'));
    }

    public function payWithGateWay($response, $gateWayName, $user = null, $invoice = null)
    {
        try {

            if (Auth::check()) {
                if (!$user) {
                    $user = Auth::user();
                }
            }

            if ($user) {
                $certificate = session()->get('certificate_order') ?? null;
                $checkout_info = Checkout::where('user_id', $user->id)->latest()->first();
                if ($invoice) {
                    $checkout_info = Checkout::where('user_id', $invoice->user_id)
                        ->where('tracking', $invoice->tracking)
                        ->where('invoice_id', $invoice->id)->first();
                }
                if ($certificate) {
                    $checkout_info = Checkout::where('user_id', $certificate->user_id)
                        ->where('tracking', $certificate->tracking)
                        ->where('type', 'certificate')
                        ->where('order_certificate_id', $certificate->id)->first();
                }
                if (isset($checkout_info)) {

                    $discount = $checkout_info->discount;

                    $carts = Cart::where('user_id', $user->id)->latest()->get();

                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                    if (isset($backtrace[1])) {
                        $caller = $backtrace[1];
                        $callerMethod = $caller['function'];
                        if ($callerMethod == 'directEnroll') {
                            $carts = $carts->where('price', 0);
                        }
                    }
                    if ($invoice) {
                        $carts = $invoice->courses;
                    } elseif ($certificate) {
                        $carts = $certificate;
                    }
                    $courseType = collect();
                    $renew = 0;
                    $bundleId = 0;

                    foreach ($carts as $cartKey => $cart) {


                        if (isModuleActive('Gift') && $cart->is_gift == 1) {
                            $checkout_data = [
                                'payment_checkout_info' => $checkout_info,
                                'payment_user' => $user,
                                'payment_discount' => $discount,
                                'payment_cart' => $cart,
                                'payment_carts' => $carts,
                                'payment_courseType' => $courseType,
                                'payment_gateWayName' => $gateWayName,
                            ];
                            $cart->cart_gift->update([
                                'gift_status' => 2,
                                'checkout_data' => $checkout_data,
                            ]);
                            continue;

                        }


                        if ($checkout_info->type == 'appointment') {
                            $instructor = User::find($cart->instructor_id);
                            $this->forAppointment($checkout_info, $instructor, $discount, $cart, $carts, $gateWayName);
                        } elseif ($certificate) {

                        } else {
                            $this->defaultPayment($checkout_info, $user, $discount, $cart, $carts, $courseType, $gateWayName);
                        }
                    }

                    // out of foreach
                    $checkout_info->payment_method = $gateWayName;
                    $checkout_info->status = 1;
                    $checkout_info->response = json_encode($response);
                    if ($certificate || $invoice) {
                        if ($checkout_info) {
                            // for invoice
                            if (isModuleActive('Invoice') && $invoice) {
                                $invoice->update([
                                    'payment_method' => $gateWayName,
                                    'status' => 'paid',
                                    'checkout_id' => $checkout_info->id,
                                ]);
                            }
                            // end invoice
                            if (isModuleActive('Invoice') && $certificate) {
                                $certificate->update([
                                    'status' => 'ordered',
                                    'checkout_id' => $checkout_info->id,
                                    'payment_status' => 1,
                                ]);

                                $admin = User::where('role_id', 1)->first();
                                $shortCodes = [
                                    'student_name' => $certificate->user->name,
                                    'course' => $certificate->course->title
                                ];
                                if (UserBrowserNotificationSetup('certificate_order', $admin)) {
                                    send_browser_notification($admin, $type = 'certificate_order', $shortCodes);
                                }
                                if (UserEmailNotificationSetup('certificate_order', $admin)) {
                                    send_email($admin, 'certificate_order', $shortCodes);
                                }
                            }
                        }
                    } else {
                        // bundlesSubscription
                        if (isModuleActive('BundleSubscription')) {
                            $checkout_info->bundle_id = $bundleId;
                            $checkout_info->renew = $renew;

                            if (isset($courseType->bundle) && $courseType->bundle == 1 && isset($courseType->single) && $courseType->single == 1) {
                                $checkout_info->course_type = 'multi';
                            } elseif (isset($courseType->single) && $courseType->single == 1) {
                                $checkout_info->course_type = 'single';
                            } else {
                                $checkout_info->course_type = 'bundle';
                            }
                        }
                        $checkout_info->save();
                        // end bundle Subscription
                    }

                    if ($checkout_info->user->status == 1 && !$invoice && !$certificate) {
                        foreach ($carts as $old) {
                            $old->delete();
                        }
                    }

                    if (isModuleActive('RegistrationBonus')) {

                        $bonus = new \Modules\RegistrationBonus\Repositories\RegistrationbonusRepositroy();
                        $invited_user_id = UserWiseCoupon::where('invite_accept_by', Auth::id())->first();
                        if ($invited_user_id) {
                            $invited_user = User::find($invited_user_id->invite_by);
                            if ($invited_user) {
                                $invited_user->total_referrer_amount = $checkout_info->purchase_price;
                                $invited_user->save();
                                $bonus->bonusOnPurchase($invited_user);
                            }
                        }
                    }

                    if ($gateWayName != 'None') {
                        Toastr::success('Checkout Successfully Done', 'Success');
                    }
                    return true;
                } else {
                    Toastr::error('Something Went Wrong', 'Error');
                    return false;
                }

            } else {
                Toastr::error('Something Went Wrong', 'Error');
                return false;
            }

        } catch (\Exception $e) {

            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent(), true);

        }
    }

    private function forAppointment($checkout_info, $instructor, $discount = 0, $cart, $carts, $gateWayName)
    {
        if (isModuleActive('Appointment') && $cart->schedule_id) {
            if ($discount != 0 || !empty($discount)) {
                $itemPrice = $instructor->hour_rate - ($discount / count($carts));
                $discount_amount = $instructor->hour_rate - $itemPrice;
            } else {
                $itemPrice = $instructor->hour_rate;
                $discount_amount = 0.00;
            }
            $exit = Booking::where('schedule_id', $cart->schedule_id)
                ->where('user_id', auth()->user()->id)->first();

            // for appointment  module
            if (!$exit) {
                $scheduleBooking = new Booking;
                $scheduleBooking->schedule_id = $cart->schedule_id;
                $scheduleBooking->tracking = $cart->tracking;
                $scheduleBooking->user_id = auth()->user()->id;
                $scheduleBooking->instructor_id = $cart->instructor_id;
                $scheduleBooking->purchase_price = $itemPrice ?? 0;
                $scheduleBooking->coupon = null;
                $scheduleBooking->discount_amount = $discount_amount;
                $scheduleBooking->timezone = $cart->timezone;
                $scheduleBooking->status = 0;
                $scheduleBooking->save();

                if (!is_null($instructor->appointment_special_commission) && $instructor->appointment_special_commission != 0) {
                    $commission = $instructor->appointment_special_commission;
                    $revenue = ($itemPrice * $commission) / 100;
                } else {
                    $commission = 100 - Settings('appointment_commission');
                    $revenue = ($itemPrice * $commission) / 100;
                }

                $payout = new InstructorPayout();
                $payout->instructor_id = $cart->instructor_id;
                $payout->reveune = $revenue;
                $payout->status = 0;
                $payout->save();
                $this->emailNotification($checkout_info, $cart, $gateWayName);
            }

        }
    }

    public function emailNotification($checkout_info, $cart, $gateWayName)
    {
        $start_time = date('h:i A', strtotime($cart->schedule->slotInfo->start_time));
        $end_time = date('h:i A', strtotime($cart->schedule->slotInfo->end_time));
        $hour_rate = $cart->schedule->userInfo->hour_rate;
        $timeSlot = $cart->schedule->schedule_date . '(' . $start_time . '-' . $end_time . ')';
        $topic = $cart->schedule->category->name . '(' . $cart->schedule->subCategory->name . ')';
        $admin = User::where('role_id', 1)->first();

        $shortCodes = [
            'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
            'timeSlot' => $timeSlot,
            'topic' => $topic,
            'currency' => $cart->instructor->currency->symbol ?? '$',
            'price' => $hour_rate,
            'student_name' => $cart->student->name,
            'gateway' => $gateWayName,
        ];
        // for instructor

        if (UserEmailNotificationSetup('Appointment_Enroll_Instructor', $cart->instructor)) {
            send_email($cart->instructor, 'Appointment_Enroll_Instructor', $shortCodes);
        }
        // for student
        if (UserEmailNotificationSetup('Appointment_Enroll_Payment', $cart->student)) {
            send_email($cart->student, 'Appointment_Enroll_Payment', [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'timeSlot' => $timeSlot,
                'topic' => $topic,
                'currency' => $cart->student->currency->symbol ?? '$',
                'price' => $hour_rate,
                'instructor' => $cart->schedule->userInfo->name,
                'gateway' => $gateWayName,
            ]);
        }

        // for admin
        if (UserEmailNotificationSetup('Appointment_Enroll_Admin', $admin)) {
            send_email($admin, 'Appointment_Enroll_Admin', [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'timeSlot' => $timeSlot,
                'topic' => $topic,
                'currency' => $admin->currency->symbol ?? '$',
                'price' => $hour_rate,
                'instructor' => $cart->schedule->userInfo->name,
                'student_name' => $cart->student->name,
                'gateway' => $gateWayName,
            ]);
        }

        // browser student notification
        if (UserBrowserNotificationSetup('Appointment_Enroll_Payment', $cart->student)) {
            send_browser_notification($cart->student, $type = 'Appointment_Enroll_Payment', $shortcodes = [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'timeSlot' => $timeSlot,
                'topic' => $topic,
                'currency' => $cart->student->currency->symbol ?? '$',
                'price' => $hour_rate,
                'instructor' => $cart->schedule->userInfo->name,
                'gateway' => $gateWayName,
            ],
                '', //actionText
                '' //actionUrl
            );
        }
        // browser admin notification
        if (UserBrowserNotificationSetup('Appointment_Enroll_Admin', $admin)) {
            send_browser_notification($admin, $type = 'Appointment_Enroll_Admin', $shortcodes = [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'timeSlot' => $timeSlot,
                'topic' => $topic,
                'currency' => $admin->currency->symbol ?? '$',
                'price' => $hour_rate,
                'instructor' => $cart->schedule->userInfo->name,
                'student_name' => $cart->student->name,
                'gateway' => $gateWayName,
            ],
                '', //actionText
                '' //actionUrl
            );
        }
        // browser instructor notification
        if (UserBrowserNotificationSetup('Appointment_Enroll_Instructor', $cart->instructor)) {
            send_browser_notification($cart->instructor, $type = 'Appointment_Enroll_Instructor', $shortcodes = [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'timeSlot' => $timeSlot,
                'topic' => $topic,
                'currency' => $cart->instructor->currency->symbol ?? '$',
                'price' => $hour_rate,
                'student_name' => $cart->student->name,
                'gateway' => $gateWayName,
            ],
                '', //actionText
                '' //actionUrl
            );
        }

    }

    public function defaultPayment($checkout_info, $user, $discount = 0, $cart, $carts, $courseType, $gateWayName = null)
    {
        if ($cart->course_id != 0) {
            $courseType->single = 1;

            $course = Course::find($cart->course_id);
            $enrolled = $course->total_enrolled;
            $course->total_enrolled = ($enrolled + 1);

            //==========================Start Referral========================
            $purchase_history = CourseEnrolled::where('user_id', $user->id)->first();
            $referral_check = UserWiseCoupon::where('invite_accept_by', $user->id)->where('category_id', null)->where('course_id', null)->first();
            $referral_settings = UserWiseCouponSetting::where('status', 1)->where('role_id', $user->role_id)->first();

            if ($referral_settings && $purchase_history == null && $referral_check != null) {
                $referral_check->category_id = $course->category_id;
                $referral_check->subcategory_id = $course->subcategory_id;
                $referral_check->course_id = $course->id;
                $referral_check->save();
                $percentage_cal = ($referral_settings->amount / 100) * $checkout_info->price;

                if ($referral_settings->type == 1) {
                    if ($checkout_info->price > $referral_settings->max_limit) {
                        $bonus_amount = $referral_settings->max_limit;
                    } else {
                        $bonus_amount = $referral_settings->amount;
                    }
                } else {
                    if ($percentage_cal > $referral_settings->max_limit) {
                        $bonus_amount = $referral_settings->max_limit;
                    } else {
                        $bonus_amount = $percentage_cal;
                    }
                }

                $referral_check->bonus_amount = $bonus_amount;
                $referral_check->save();

                $invite_by = User::find($referral_check->invite_by);
                $invite_by->balance += $bonus_amount;
                $invite_by->save();

                $invite_accept_by = User::find($referral_check->invite_accept_by);
                $invite_accept_by->balance += $bonus_amount;
                $invite_accept_by->save();
            }
            //==========================End Referral========================
            if ($discount != 0 || !empty($discount)) {
                $itemPrice = $cart->price - ($discount / count($carts));
                $discount_amount = $cart->price - $itemPrice;
            } else {
                $itemPrice = $cart->price;
                $discount_amount = 0.00;
            }
            $enroll = new CourseEnrolled();
            $instractor = User::find($cart->instructor_id);
            $enroll->user_id = $user->id;
            $enroll->tracking = $checkout_info->tracking;
            $enroll->course_id = $course->id;
            $enroll->purchase_price = $itemPrice;
            $enroll->coupon = null;
            $enroll->discount_amount = $discount_amount;
            $enroll->status = 1;

            if (!is_null($course->special_commission) && $course->special_commission != 0) {
                $commission = $course->special_commission;
                $reveune = ($itemPrice * $commission) / 100;
                $enroll->reveune = $reveune;
            } elseif (!is_null($instractor->special_commission) && $instractor->special_commission != 0) {
                $commission = $instractor->special_commission;
                $reveune = ($itemPrice * $commission) / 100;
                $enroll->reveune = $reveune;
            } else {
                $commission = 100 - Settings('commission');
                $reveune = ($itemPrice * $commission) / 100;
                $enroll->reveune = $reveune;
            }

            $payout = new InstructorPayout();
            $payout->instructor_id = $course->user_id;
            $payout->reveune = $reveune;
            $payout->status = 0;
            $payout->save();
            $enroll->save();
            if (isModuleActive('Group')) {
                if ($course->isGroupCourse) {
                    Event::dispatch(new GroupMemberCreate($course->id, $user->id));
                }
            }

            $course->reveune = (($course->reveune) + ($enroll->reveune));

            $course->save();
            checkGamification('each_enroll', 'sales', $instractor);

            if (isModuleActive('Chat')) {
                event(new OneToOneConnection($instractor, $user, $course));
            }
            if (isModuleActive('Survey')) {
                $hasSurvey = Survey::where('course_id', $course->id)->get();
                foreach ($hasSurvey as $survey) {
                    $surveyController = new SurveyController();
                    $surveyController->assignSurvey($survey->id, $user->id);
                }
            }

            if (isModuleActive('Affiliate')) {
                if ($user->isReferralUser) {
                    Event::dispatch(new ReferralPayment($user->id, $course->id, $course->price));
                }
            }
            if (isModuleActive('Invoice')) {
                if ($checkout_info->invoice_id) {
                    $interface = App::make(InvoiceRepositoryInterface::class);
                    $interface->sendInvoice($checkout_info->user->id, null, $checkout_info);
                }
            }
            $shortCodes = [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'course' => $course->title,
                'currency' => $checkout_info->user->currency->symbol ?? '$',
                'price' => ($checkout_info->user->currency->conversion_rate * $itemPrice),
                'instructor' => $course->user->name,
                'gateway' => $gateWayName,
            ];
            if (UserEmailNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {
                send_email($checkout_info->user, 'Course_Enroll_Payment', $shortCodes);
            }
            if (UserBrowserNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {
                send_browser_notification($checkout_info->user, $type = 'Course_Enroll_Payment', $shortCodes);
            }
            $browserNotificationShortCodes = [
                'time' => Carbon::now()->format('d-M-Y ,s:i A'),
                'course' => $course->title,
                'currency' => $instractor->currency->symbol ?? '$',
                'price' => ($instractor->currency->conversion_rate * $itemPrice),
                'rev' => @$reveune,
            ];
            if (UserEmailNotificationSetup('Enroll_notify_Instructor', $instractor)) {
                send_email($instractor, 'Enroll_notify_Instructor', $browserNotificationShortCodes);

            }
            if (UserBrowserNotificationSetup('Enroll_notify_Instructor', $instractor)) {
                send_browser_notification($instractor, $type = 'Enroll_notify_Instructor', $browserNotificationShortCodes,
                    '', //actionText
                    '' //actionUrl
                );
            }
            if (isModuleActive('GoogleCalendar') && $course->type == 3 && $course->class->host != 'GoogleMeet') {
                Event::dispatch(new GoogleCalendarEventAddAttendee($course->class_id, $user->email));
            }

            if (isModuleActive('GoogleMeet') && $course->type == 3 && $course->class->host == 'GoogleMeet') {
                Event::dispatch(new MeetingAddAttendeeEvent($course->class_id, $user->email));
            }

            //start email subscription
            if ($instractor->subscription_api_status == 1) {
                try {
                    if ($instractor->subscription_method == "Mailchimp") {
                        $list = $course->subscription_list;
                        $MailChimp = new MailChimp($instractor->subscription_api_key);
                        $MailChimp->post("lists/$list/members", [
                            'email_address' => $user->email,
                            'status' => 'subscribed',
                        ]);

                    } elseif ($instractor->subscription_method == "GetResponse") {

                        $list = $course->subscription_list;
                        $getResponse = new \GetResponse($instractor->subscription_api_key);
                        $getResponse->addContact(array(
                            'email' => $user->email,
                            'campaign' => array('campaignId' => $list),

                        ));
                    } elseif ($instractor->subscription_method == "Acelle") {

                        $list = $course->subscription_list;
                        $email = $user->email;
                        $make_action_url = '/subscribers?list_uid=' . $list . '&EMAIL=' . $email;
                        $acelleController = new AcelleController();
                        $response = $acelleController->curlPostRequest($make_action_url);
                    }
                } catch (\Exception$exception) {
                    GettingError($exception->getMessage(), url()->current(), request()->ip(), request()->userAgent(), true);

                }
            }

        } else {
            $bundleCheck = BundleCoursePlan::find($cart->bundle_course_id);

            $totalCount = count($bundleCheck->course);
            $price = $bundleCheck->price;
            if ($price != 0) {
                $price = $price / $totalCount;
            }

            $courseType->bundle = 1;
            if ($cart->renew != 1) {
                foreach ($bundleCheck->course as $course) {

                    $enrolled = $course->course->total_enrolled;
                    $course->course->total_enrolled = ($enrolled + 1);

                    $enroll = new CourseEnrolled();
                    $instractor = User::find($cart->instructor_id);
                    $enroll->user_id = $user->id;
                    $enroll->tracking = $checkout_info->tracking;
                    $enroll->course_id = $course->course->id;
                    $enroll->purchase_price = $price;
                    $enroll->coupon = null;
                    $enroll->discount_amount = 0;
                    $enroll->status = 1;
                    $enroll->bundle_course_id = $cart->bundle_course_id;
                    $enroll->bundle_course_validity = $cart->bundle_course_validity;
                    //$enroll->reveune = $reveune / count($bundleCheck->course);
                    $enroll->save();

                    $course->course->save();

                }
            } else {
                $enrollBundleCourse = CourseEnrolled::where('bundle_course_id', $cart->bundle_course_id)->where('user_id', Auth::id())->get();
                foreach ($enrollBundleCourse as $enroll) {

                    $instractor = User::find($cart->user_id);
                    $enroll->bundle_course_id = $cart->bundle_course_id;
                    $enroll->bundle_course_validity = $cart->bundle_course_validity;
                    //$enroll->reveune = $enroll->reveune + $reveune / count($enrollBundleCourse);
                    $enroll->save();
                }
                $bundleId = $cart->bundle_course_id;
                $renew = 1;
                $checkout_info->renew = $renew;
                $checkout_info->bundle_id = $bundleId;
                $checkout_info->save();
            }
            $bundleCommission = BundleSetting::getData();
            if ($bundleCommission) {
                $commission = $bundleCommission->commission_rate;
                $reveune = ($bundleCheck->price * $commission) / 100;
                $bundleCheck->reveune += $reveune;
                $bundleCheck->student += 1;
                $bundleCheck->save();
            }
            $payout = new InstructorPayout();
            $payout->instructor_id = $bundleCheck->user_id;
            $payout->reveune = $reveune;
            $payout->status = 0;
            $payout->save();

            if (UserEmailNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {
                send_email($checkout_info->user, 'Course_Enroll_Payment', [
                    'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $checkout_info->user->currency->symbol ?? '$',
                    'price' => ($checkout_info->user->currency->conversion_rate * $bundleCheck->price),
                    'instructor' => $bundleCheck->user->name,
                    'gateway' => 'Sslcommerz',
                ]);

            }
            if (isModuleActive('Invoice')) {
                $interface = App::make(InvoiceRepositoryInterface::class);
                $interface->sendInvoice($checkout_info->user->id, null, $checkout_info);
            }
            if (UserBrowserNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {

                send_browser_notification($checkout_info->user, $type = 'Course_Enroll_Payment', $shortcodes = [
                    'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $checkout_info->user->currency->symbol ?? '$',
                    'price' => ($checkout_info->user->currency->conversion_rate * $bundleCheck->price),
                    'instructor' => $bundleCheck->user->name,
                    'gateway' => $gateWayName,
                ],
                    '', //actionText
                    '' //actionUrl
                );
            }

            if (UserEmailNotificationSetup('Enroll_notify_Instructor', $instractor)) {
                send_email($instractor, 'Enroll_notify_Instructor', [
                    'time' => Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $instractor->currency->symbol ?? '$',
                    'price' => ($instractor->currency->conversion_rate * $bundleCheck->price),
                    'rev' => @$reveune,
                ]);

            }
            if (UserBrowserNotificationSetup('Enroll_notify_Instructor', $instractor)) {

                send_browser_notification($instractor, $type = 'Enroll_notify_Instructor', $shortcodes = [
                    'time' => Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $instractor->currency->symbol ?? '$',
                    'price' => ($instractor->currency->conversion_rate * $bundleCheck->price),
                    'rev' => @$reveune,
                ],
                    '', //actionText
                    '' //actionUrl
                );
            }

            if (isModuleActive('Chat')) {
                event(new OneToOneConnection($instractor, $user, $course));
            }

            if (isModuleActive('Survey')) {
                $hasSurvey = Survey::where('course_id', $course->id)->get();
                foreach ($hasSurvey as $survey) {
                    $surveyController = new SurveyController();
                    $surveyController->assignSurvey($survey->id, $user->id);
                }
            }

            //start email subscription
            if ($instractor->subscription_api_status == 1) {
                try {
                    if ($instractor->subscription_method == "Mailchimp") {
                        $list = $course->subscription_list;
                        $MailChimp = new MailChimp($instractor->subscription_api_key);
                        $MailChimp->post("lists/$list/members", [
                            'email_address' => Auth::user()->email,
                            'status' => 'subscribed',
                        ]);

                    } elseif ($instractor->subscription_method == "GetResponse") {

                        $list = $course->subscription_list;
                        $getResponse = new \GetResponse($instractor->subscription_api_key);
                        $getResponse->addContact(array(
                            'email' => Auth::user()->email,
                            'campaign' => array('campaignId' => $list),

                        ));
                    }
                } catch (\Exception$exception) {
                    GettingError($exception->getMessage(), url()->current(), request()->ip(), request()->userAgent(), true);

                }
            }
        }
    }

    public function giftEnrollment($checkout_info, $user, $discount = 0, $cart, $carts, $courseType, $gateWayName = null)
    {
        if ($cart->course_id != 0) {
            $courseType->single = 1;

            $course = Course::find($cart->course_id);
            $enrolled = $course->total_enrolled;
            $course->total_enrolled = ($enrolled + 1);

            //==========================Start Referral========================
            $purchase_history = CourseEnrolled::where('user_id', $user->id)->first();
            $referral_check = UserWiseCoupon::where('invite_accept_by', $user->id)->where('category_id', null)->where('course_id', null)->first();
            $referral_settings = UserWiseCouponSetting::where('role_id', $user->role_id)->first();

            if ($purchase_history == null && $referral_check != null) {
                $referral_check->category_id = $course->category_id;
                $referral_check->subcategory_id = $course->subcategory_id;
                $referral_check->course_id = $course->id;
                $referral_check->save();
                $percentage_cal = ($referral_settings->amount / 100) * $checkout_info->price;

                if ($referral_settings->type == 1) {
                    if ($checkout_info->price > $referral_settings->max_limit) {
                        $bonus_amount = $referral_settings->max_limit;
                    } else {
                        $bonus_amount = $referral_settings->amount;
                    }
                } else {
                    if ($percentage_cal > $referral_settings->max_limit) {
                        $bonus_amount = $referral_settings->max_limit;
                    } else {
                        $bonus_amount = $percentage_cal;
                    }
                }

                $referral_check->bonus_amount = $bonus_amount;
                $referral_check->save();

                $invite_by = User::find($referral_check->invite_by);
                $invite_by->balance += $bonus_amount;
                $invite_by->save();

                $invite_accept_by = User::find($referral_check->invite_accept_by);
                $invite_accept_by->balance += $bonus_amount;
                $invite_accept_by->save();
            }
            //==========================End Referral========================
            if ($discount != 0 || !empty($discount)) {
                $itemPrice = $cart->price - ($discount / count($carts));
                $discount_amount = $cart->price - $itemPrice;
            } else {
                $itemPrice = $cart->price;
                $discount_amount = 0.00;
            }
            $enroll = new CourseEnrolled();
            $instractor = User::find($cart->instructor_id);
            $enroll->user_id = $user->id;
            $enroll->tracking = $checkout_info->tracking;
            $enroll->course_id = $course->id;
            $enroll->purchase_price = $itemPrice;
            $enroll->coupon = null;
            $enroll->discount_amount = $discount_amount;
            $enroll->status = 1;

            if (!is_null($course->special_commission) && $course->special_commission != 0) {
                $commission = $course->special_commission;
                $reveune = ($itemPrice * $commission) / 100;
                $enroll->reveune = $reveune;
            } elseif (!is_null($instractor->special_commission) && $instractor->special_commission != 0) {
                $commission = $instractor->special_commission;
                $reveune = ($itemPrice * $commission) / 100;
                $enroll->reveune = $reveune;
            } else {
                $commission = 100 - Settings('commission');
                $reveune = ($itemPrice * $commission) / 100;
                $enroll->reveune = $reveune;
            }

            $payout = new InstructorPayout();
            $payout->instructor_id = $course->user_id;
            $payout->reveune = $reveune;
            $payout->status = 0;
            $payout->save();
            $enroll->save();
            if (isModuleActive('Group')) {
                if ($course->isGroupCourse) {
                    Event::dispatch(new GroupMemberCreate($course->id, $user->id));
                }
            }

            $course->reveune = (($course->reveune) + ($enroll->reveune));

            $course->save();
            checkGamification('each_enroll', 'sales', $instractor);

            if (isModuleActive('Chat')) {
                event(new OneToOneConnection($instractor, $user, $course));
            }
            if (isModuleActive('Survey')) {
                $hasSurvey = Survey::where('course_id', $course->id)->get();
                foreach ($hasSurvey as $survey) {
                    $surveyController = new SurveyController();
                    $surveyController->assignSurvey($survey->id, $user->id);
                }
            }

            if (isModuleActive('Affiliate')) {
                if ($user->isReferralUser) {
                    Event::dispatch(new ReferralPayment($user->id, $course->id, $course->price));
                }
            }
            if (isModuleActive('Invoice')) {
                if ($checkout_info->invoice_id) {
                    $interface = App::make(InvoiceRepositoryInterface::class);
                    $interface->sendInvoice($checkout_info->user->id, null, $checkout_info);
                }
            }
            $shortCodes = [
                'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                'course' => $course->title,
                'currency' => $checkout_info->user->currency->symbol ?? '$',
                'price' => ($checkout_info->user->currency->conversion_rate * $itemPrice),
                'instructor' => $course->user->name,
                'gateway' => $gateWayName,
            ];
            if (UserEmailNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {
                send_email($checkout_info->user, 'Course_Enroll_Payment', $shortCodes);
            }
            if (UserBrowserNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {
                send_browser_notification($checkout_info->user, $type = 'Course_Enroll_Payment', $shortCodes);
            }
            $browserNotificationShortCodes = [
                'time' => Carbon::now()->format('d-M-Y ,s:i A'),
                'course' => $course->title,
                'currency' => $instractor->currency->symbol ?? '$',
                'price' => ($instractor->currency->conversion_rate * $itemPrice),
                'rev' => @$reveune,
            ];
            if (UserEmailNotificationSetup('Enroll_notify_Instructor', $instractor)) {
                send_email($instractor, 'Enroll_notify_Instructor', $browserNotificationShortCodes);

            }
            if (UserBrowserNotificationSetup('Enroll_notify_Instructor', $instractor)) {
                send_browser_notification($instractor, $type = 'Enroll_notify_Instructor', $browserNotificationShortCodes,
                    '', //actionText
                    '' //actionUrl
                );
            }
            if (isModuleActive('GoogleCalendar') && $course->type == 3 && $course->class->host != 'GoogleMeet') {
                Event::dispatch(new GoogleCalendarEventAddAttendee($course->class_id, $user->email));
            }

            if (isModuleActive('GoogleMeet') && $course->type == 3 && $course->class->host == 'GoogleMeet') {
                Event::dispatch(new MeetingAddAttendeeEvent($course->class_id, $user->email));
            }

            //start email subscription
            if ($instractor->subscription_api_status == 1) {
                try {
                    if ($instractor->subscription_method == "Mailchimp") {
                        $list = $course->subscription_list;
                        $MailChimp = new MailChimp($instractor->subscription_api_key);
                        $MailChimp->post("lists/$list/members", [
                            'email_address' => $user->email,
                            'status' => 'subscribed',
                        ]);

                    } elseif ($instractor->subscription_method == "GetResponse") {

                        $list = $course->subscription_list;
                        $getResponse = new \GetResponse($instractor->subscription_api_key);
                        $getResponse->addContact(array(
                            'email' => $user->email,
                            'campaign' => array('campaignId' => $list),

                        ));
                    } elseif ($instractor->subscription_method == "Acelle") {

                        $list = $course->subscription_list;
                        $email = $user->email;
                        $make_action_url = '/subscribers?list_uid=' . $list . '&EMAIL=' . $email;
                        $acelleController = new AcelleController();
                        $response = $acelleController->curlPostRequest($make_action_url);
                    }
                } catch (\Exception$exception) {
                    GettingError($exception->getMessage(), url()->current(), request()->ip(), request()->userAgent(), true);

                }
            }

        } else {
            $bundleCheck = BundleCoursePlan::find($cart->bundle_course_id);

            $totalCount = count($bundleCheck->course);
            $price = $bundleCheck->price;
            if ($price != 0) {
                $price = $price / $totalCount;
            }

            $courseType->bundle = 1;
            if ($cart->renew != 1) {
                foreach ($bundleCheck->course as $course) {

                    $enrolled = $course->course->total_enrolled;
                    $course->course->total_enrolled = ($enrolled + 1);

                    $enroll = new CourseEnrolled();
                    $instractor = User::find($cart->instructor_id);
                    $enroll->user_id = $user->id;
                    $enroll->tracking = $checkout_info->tracking;
                    $enroll->course_id = $course->course->id;
                    $enroll->purchase_price = $price;
                    $enroll->coupon = null;
                    $enroll->discount_amount = 0;
                    $enroll->status = 1;
                    $enroll->bundle_course_id = $cart->bundle_course_id;
                    $enroll->bundle_course_validity = $cart->bundle_course_validity;
                    //$enroll->reveune = $reveune / count($bundleCheck->course);
                    $enroll->save();

                    $course->course->save();

                }
            } else {
                $enrollBundleCourse = CourseEnrolled::where('bundle_course_id', $cart->bundle_course_id)->where('user_id', Auth::id())->get();
                foreach ($enrollBundleCourse as $enroll) {

                    $instractor = User::find($cart->user_id);
                    $enroll->bundle_course_id = $cart->bundle_course_id;
                    $enroll->bundle_course_validity = $cart->bundle_course_validity;
                    //$enroll->reveune = $enroll->reveune + $reveune / count($enrollBundleCourse);
                    $enroll->save();
                }
                $bundleId = $cart->bundle_course_id;
                $renew = 1;
                $checkout_info->renew = $renew;
                $checkout_info->bundle_id = $bundleId;
                $checkout_info->save();
            }
            $bundleCommission = BundleSetting::getData();
            if ($bundleCommission) {
                $commission = $bundleCommission->commission_rate;
                $reveune = ($bundleCheck->price * $commission) / 100;
                $bundleCheck->reveune += $reveune;
                $bundleCheck->student += 1;
                $bundleCheck->save();
            }
            $payout = new InstructorPayout();
            $payout->instructor_id = $bundleCheck->user_id;
            $payout->reveune = $reveune;
            $payout->status = 0;
            $payout->save();

            if (UserEmailNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {
                send_email($checkout_info->user, 'Course_Enroll_Payment', [
                    'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $checkout_info->user->currency->symbol ?? '$',
                    'price' => ($checkout_info->user->currency->conversion_rate * $bundleCheck->price),
                    'instructor' => $bundleCheck->user->name,
                    'gateway' => 'Sslcommerz',
                ]);

            }
            if (isModuleActive('Invoice')) {
                $interface = App::make(InvoiceRepositoryInterface::class);
                $interface->sendInvoice($checkout_info->user->id, null, $checkout_info);
            }
            if (UserBrowserNotificationSetup('Course_Enroll_Payment', $checkout_info->user)) {

                send_browser_notification($checkout_info->user, $type = 'Course_Enroll_Payment', $shortcodes = [
                    'time' => \Illuminate\Support\Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $checkout_info->user->currency->symbol ?? '$',
                    'price' => ($checkout_info->user->currency->conversion_rate * $bundleCheck->price),
                    'instructor' => $bundleCheck->user->name,
                    'gateway' => $gateWayName,
                ],
                    '', //actionText
                    '' //actionUrl
                );
            }

            if (UserEmailNotificationSetup('Enroll_notify_Instructor', $instractor)) {
                send_email($instractor, 'Enroll_notify_Instructor', [
                    'time' => Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $instractor->currency->symbol ?? '$',
                    'price' => ($instractor->currency->conversion_rate * $bundleCheck->price),
                    'rev' => @$reveune,
                ]);

            }
            if (UserBrowserNotificationSetup('Enroll_notify_Instructor', $instractor)) {

                send_browser_notification($instractor, $type = 'Enroll_notify_Instructor', $shortcodes = [
                    'time' => Carbon::now()->format('d-M-Y ,s:i A'),
                    'course' => $bundleCheck->title,
                    'currency' => $instractor->currency->symbol ?? '$',
                    'price' => ($instractor->currency->conversion_rate * $bundleCheck->price),
                    'rev' => @$reveune,
                ],
                    '', //actionText
                    '' //actionUrl
                );
            }

            if (isModuleActive('Chat')) {
                event(new OneToOneConnection($instractor, $user, $course));
            }

            if (isModuleActive('Survey')) {
                $hasSurvey = Survey::where('course_id', $course->id)->get();
                foreach ($hasSurvey as $survey) {
                    $surveyController = new SurveyController();
                    $surveyController->assignSurvey($survey->id, $user->id);
                }
            }

            //start email subscription
            if ($instractor->subscription_api_status == 1) {
                try {
                    if ($instractor->subscription_method == "Mailchimp") {
                        $list = $course->subscription_list;
                        $MailChimp = new MailChimp($instractor->subscription_api_key);
                        $MailChimp->post("lists/$list/members", [
                            'email_address' => Auth::user()->email,
                            'status' => 'subscribed',
                        ]);

                    } elseif ($instractor->subscription_method == "GetResponse") {

                        $list = $course->subscription_list;
                        $getResponse = new \GetResponse($instractor->subscription_api_key);
                        $getResponse->addContact(array(
                            'email' => Auth::user()->email,
                            'campaign' => array('campaignId' => $list),

                        ));
                    }
                } catch (\Exception$exception) {
                    GettingError($exception->getMessage(), url()->current(), request()->ip(), request()->userAgent(), true);

                }
            }
        }
    }
}
