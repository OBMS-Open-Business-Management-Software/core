<?php

declare(strict_types=1);

use App\Emails\AccountEmailVerificationRequest;
use App\Emails\ProfileEmailVerificationRequest;
use App\Helpers\Products;
use App\Models\Content\Page;
use App\Models\Shop\Configurator\ShopConfiguratorCategory;
use App\Models\Shop\Configurator\ShopConfiguratorForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use OBMS\ModuleSDK\Products\Product;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
})->name('root');

Route::get('/css/app.css', App\Http\Controllers\DynamicStyles::class);

Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'changeLanguage'])->name('language.change');

Route::get('/email/verify/{id}/{hash}', function (AccountEmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('customer.home')->with('success', __('interface.messages.email_confirmed'));
})->middleware('signed')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', __('interface.messages.email_verification_sent'));
})->middleware('throttle:6,1')->name('verification.send');

Route::get('/email/verify', function () {
    return view('auth.verify');
})->name('verification.notice');

Route::get('/set-password', [App\Http\Controllers\DashboardController::class, 'setPassword'])->name('password-set');
Route::post('/submit-password', [App\Http\Controllers\DashboardController::class, 'submitPassword'])->name('password-set.submit');

Route::middleware([
    'auth',
    'verified',
    'unlocked',
    'password.check_reset',
])->group(function () {
    Route::get('/redirect', [App\Http\Controllers\DashboardController::class, 'redirect'])->name('redirect');

    Route::group([
        'prefix'     => 'customer',
        'middleware' => [
            'role.customer',
            'products.customer',
        ],
    ], function () {
        Route::get('/accept', [App\Http\Controllers\CustomerDashboardController::class, 'accept'])->name('customer.accept');
        Route::post('/accept/submit', [App\Http\Controllers\CustomerDashboardController::class, 'acceptSubmit'])->name('customer.accept.submit');

        Route::middleware([
            'accepted',
        ])->group(function () {
            Route::get('/home', [App\Http\Controllers\CustomerDashboardController::class, 'index'])->name('customer.home');

            Route::get('/support', [App\Http\Controllers\CustomerSupportController::class, 'support_index'])->name('customer.support');
            Route::get('/support/list/{type}', [App\Http\Controllers\CustomerSupportController::class, 'support_index_list'])->name('customer.support.list');
            Route::get('/support/{id}', [App\Http\Controllers\CustomerSupportController::class, 'support_details'])->name('customer.support.details');
            Route::post('/support/create', [App\Http\Controllers\CustomerSupportController::class, 'support_create'])->name('customer.support.create');
            Route::post('/support/{id}/answer', [App\Http\Controllers\CustomerSupportController::class, 'support_answer'])->name('customer.support.answer');
            Route::get('/support/{id}/close', [App\Http\Controllers\CustomerSupportController::class, 'support_close'])->name('customer.support.close');
            Route::get('/support/{id}/reopen', [App\Http\Controllers\CustomerSupportController::class, 'support_reopen'])->name('customer.support.reopen');
            Route::get('/support/{id}/file/download/{filelink_id}', [App\Http\Controllers\CustomerSupportController::class, 'support_file_download'])->name('customer.support.file.download');
            Route::get('/support/{id}/file/delete/{filelink_id}', [App\Http\Controllers\CustomerSupportController::class, 'support_file_delete'])->name('customer.support.file.delete');
            Route::post('/support/{id}/file/upload', [App\Http\Controllers\CustomerSupportController::class, 'support_file_upload'])->name('customer.support.file.upload');

            Route::get('/invoices', [App\Http\Controllers\CustomerInvoiceController::class, 'invoice_index'])->name('customer.invoices');
            Route::get('/invoices/list', [App\Http\Controllers\CustomerInvoiceController::class, 'invoice_list'])->name('customer.invoices.list');
            Route::get('/invoices/{id}/download', [App\Http\Controllers\CustomerInvoiceController::class, 'invoice_download'])->name('customer.invoices.download');
            Route::get('/invoices/{id}/reminder/{reminder_id}', [App\Http\Controllers\CustomerInvoiceController::class, 'invoice_reminder_download'])->name('customer.invoices.reminders.download');
            Route::get('/invoices/{id}/history', [App\Http\Controllers\CustomerInvoiceController::class, 'invoice_history'])->name('customer.invoices.history');
            Route::get('/invoices/{id}', [App\Http\Controllers\CustomerInvoiceController::class, 'invoice_details'])->name('customer.invoices.details');

            Route::get('/contracts', [App\Http\Controllers\CustomerContractController::class, 'contract_index'])->name('customer.contracts');
            Route::get('/contracts/list', [App\Http\Controllers\CustomerContractController::class, 'contract_list'])->name('customer.contracts.list');
            Route::get('/contracts/{id}/extend', [App\Http\Controllers\CustomerContractController::class, 'contract_extend'])->name('customer.contracts.extend');
            Route::get('/contracts/{id}/cancel', [App\Http\Controllers\CustomerContractController::class, 'contract_cancel'])->name('customer.contracts.cancel');
            Route::get('/contracts/{id}/revoke', [App\Http\Controllers\CustomerContractController::class, 'contract_cancellation_revoke'])->name('customer.contracts.revoke-cancellation');
            Route::get('/contracts/{id}', [App\Http\Controllers\CustomerContractController::class, 'contract_details'])->name('customer.contracts.details');

            Route::get('/orders', [App\Http\Controllers\CustomerShopController::class, 'shop_orders_index'])->name('customer.shop.orders');
            Route::get('/orders/list', [App\Http\Controllers\CustomerShopController::class, 'shop_orders_list'])->name('customer.shop.orders.list');

            Route::get('/profile', [App\Http\Controllers\CustomerProfileController::class, 'profile_index'])->name('customer.profile');
            Route::get('/profile/transactions', [App\Http\Controllers\CustomerProfileController::class, 'profile_transactions'])->name('customer.profile.transactions');
            Route::post('/profile/2fa-confirm', [App\Http\Controllers\CustomerProfileController::class, 'profile_2fa_confirm'])->name('two-factor.confirm');
            Route::post('/profile/update', [App\Http\Controllers\CustomerProfileController::class, 'profile_update'])->name('customer.profile.update');
            Route::post('/profile/details/update', [App\Http\Controllers\CustomerProfileController::class, 'profile_details_update'])->name('customer.profile.update.details');
            Route::post('/profile/password', [App\Http\Controllers\CustomerProfileController::class, 'profile_password'])->name('customer.profile.password');
            Route::post('/profile/complete', [App\Http\Controllers\CustomerProfileController::class, 'profile_complete'])->name('customer.profile.complete');
            Route::get('/profile/email/verify/{id}/{hash}', function (ProfileEmailVerificationRequest $request) {
                $request->fulfill();

                return redirect()->route('customer.profile')->with('success', __('interface.messages.email_confirmed'));
            })->middleware('signed')->name('customer.verification.verify');
            Route::post('/profile/emailaddresses/create', [App\Http\Controllers\CustomerProfileController::class, 'profile_email_create'])->name('customer.profile.email.create');
            Route::post('/profile/emailaddresses/update', [App\Http\Controllers\CustomerProfileController::class, 'profile_email_update'])->name('customer.profile.email.update');
            Route::get('/profile/emailaddresses/delete/{id}', [App\Http\Controllers\CustomerProfileController::class, 'profile_email_delete'])->name('customer.profile.email.delete');
            Route::get('/profile/emailaddresses/resend/{id}', [App\Http\Controllers\CustomerProfileController::class, 'profile_email_resend'])->name('customer.profile.email.resend');
            Route::get('/profile/list/emailaddresses', [App\Http\Controllers\CustomerProfileController::class, 'profile_email_list'])->name('customer.profile.email.list');
            Route::post('/profile/phonenumbers/create', [App\Http\Controllers\CustomerProfileController::class, 'profile_phone_create'])->name('customer.profile.phone.create');
            Route::post('/profile/phonenumbers/update', [App\Http\Controllers\CustomerProfileController::class, 'profile_phone_update'])->name('customer.profile.phone.update');
            Route::get('/profile/phonenumbers/delete/{id}', [App\Http\Controllers\CustomerProfileController::class, 'profile_phone_delete'])->name('customer.profile.phone.delete');
            Route::get('/profile/list/phonenumbers', [App\Http\Controllers\CustomerProfileController::class, 'profile_phone_list'])->name('customer.profile.phone.list');
            Route::post('/profile/addresses/create', [App\Http\Controllers\CustomerProfileController::class, 'profile_address_create'])->name('customer.profile.address.create');
            Route::post('/profile/addresses/update', [App\Http\Controllers\CustomerProfileController::class, 'profile_address_update'])->name('customer.profile.address.update');
            Route::get('/profile/addresses/delete/{id}', [App\Http\Controllers\CustomerProfileController::class, 'profile_address_delete'])->name('customer.profile.address.delete');
            Route::get('/profile/list/addresses', [App\Http\Controllers\CustomerProfileController::class, 'profile_address_list'])->name('customer.profile.address.list');
            Route::post('/profile/bankaccounts/create', [App\Http\Controllers\CustomerProfileController::class, 'profile_bank_create'])->name('customer.profile.bank.create');
            Route::post('/profile/bankaccounts/sepa', [App\Http\Controllers\CustomerProfileController::class, 'profile_bank_sepa'])->name('customer.profile.bank.sepa');
            Route::get('/profile/bankaccounts/primary/{id}', [App\Http\Controllers\CustomerProfileController::class, 'profile_bank_primary'])->name('customer.profile.bank.primary');
            Route::get('/profile/bankaccounts/delete/{id}', [App\Http\Controllers\CustomerProfileController::class, 'profile_bank_delete'])->name('customer.profile.bank.delete');
            Route::get('/profile/list/bankaccounts', [App\Http\Controllers\CustomerProfileController::class, 'profile_bank_list'])->name('customer.profile.bank.list');
            Route::get('/profile/list/transactions', [App\Http\Controllers\CustomerProfileController::class, 'profile_transactions_list'])->name('customer.profile.transactions.list');

            Route::any('/payment/check/{payment_method}/{payment_type}', [App\Http\Controllers\PaymentController::class, 'customer_check'])->name('customer.payment.check');
            Route::any('/payment/response/{payment_type}/{payment_status}', [App\Http\Controllers\PaymentController::class, 'customer_response'])->name('customer.payment.response');
            Route::post('/profile/transactions/initialize/deposit', [App\Http\Controllers\PaymentController::class, 'customer_initialize_deposit'])->name('customer.profile.transactions.deposit');
            Route::post('/profile/transactions/initialize/invoice/{id}', [App\Http\Controllers\PaymentController::class, 'customer_initialize_invoice'])->name('customer.profile.transactions.invoice');

            Products::list()->transform(function ($handler) {
                return (object) [
                    'instance'     => $handler,
                    'capabilities' => $handler->capabilities(),
                ];
            })->reject(function ($handler) {
                return !$handler->capabilities->contains('service') || !$handler->instance->ui()->customer;
            })->each(function ($handler) {
                App::bind(Product::class, function () use ($handler) {
                    return $handler->instance;
                });

                Route::get('/services/' . $handler->instance->technicalName(), [App\Http\Controllers\CustomerServiceController::class, 'service_index'])->name('customer.services.' . $handler->instance->technicalName());
                Route::get('/services/' . $handler->instance->technicalName() . '/list', [App\Http\Controllers\CustomerServiceController::class, 'service_list'])->name('customer.services.' . $handler->instance->technicalName() . '.list');
                Route::get('/services/' . $handler->instance->technicalName() . '/{id}', [App\Http\Controllers\CustomerServiceController::class, 'service_details'])->name('customer.services.' . $handler->instance->technicalName() . '.details');
                Route::get('/services/' . $handler->instance->technicalName() . '/{id}/statistics', [App\Http\Controllers\CustomerServiceController::class, 'service_statistics'])->name('customer.services.' . $handler->instance->technicalName() . '.statistics');

                if ($handler->capabilities->contains('service.start')) {
                    Route::get('/services/' . $handler->instance->technicalName() . '/{id}/start', [App\Http\Controllers\AdminServiceController::class, 'service_start'])->name('admin.services.' . $handler->instance->technicalName() . '.start');
                }

                if ($handler->capabilities->contains('service.stop')) {
                    Route::get('/services/' . $handler->instance->technicalName() . '/{id}/start', [App\Http\Controllers\AdminServiceController::class, 'service_stop'])->name('admin.services.' . $handler->instance->technicalName() . '.stop');
                }

                if ($handler->capabilities->contains('service.restart')) {
                    Route::get('/services/' . $handler->instance->technicalName() . '/{id}/start', [App\Http\Controllers\AdminServiceController::class, 'service_start'])->name('admin.services.' . $handler->instance->technicalName() . '.restart');
                }
            });
        });
    });

    Route::group([
        'prefix'     => 'admin',
        'middleware' => [
            'role.employee',
            'products.admin',
        ],
    ], function () {
        Route::get('/home', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.home');

        Route::get('/support/tickets', [App\Http\Controllers\AdminSupportController::class, 'support_index'])->name('admin.support');
        Route::get('/support/list/{category}/{type}', [App\Http\Controllers\AdminSupportController::class, 'support_index_list'])->name('admin.support.list');
        Route::get('/support/tickets/{id}', [App\Http\Controllers\AdminSupportController::class, 'support_details'])->name('admin.support.details');
        Route::post('/support/tickets/{id}/answer', [App\Http\Controllers\AdminSupportController::class, 'support_answer'])->name('admin.support.answer');
        Route::get('/support/tickets/{id}/close', [App\Http\Controllers\AdminSupportController::class, 'support_close'])->name('admin.support.close');
        Route::get('/support/tickets/{id}/reopen', [App\Http\Controllers\AdminSupportController::class, 'support_reopen'])->name('admin.support.reopen');
        Route::get('/support/tickets/{id}/join', [App\Http\Controllers\AdminSupportController::class, 'support_join'])->name('admin.support.join');
        Route::get('/support/tickets/{id}/leave', [App\Http\Controllers\AdminSupportController::class, 'support_leave'])->name('admin.support.leave');
        Route::get('/support/tickets/{id}/lock', [App\Http\Controllers\AdminSupportController::class, 'support_lock'])->name('admin.support.lock');
        Route::get('/support/tickets/{id}/unlock', [App\Http\Controllers\AdminSupportController::class, 'support_unlock'])->name('admin.support.unlock');
        Route::get('/support/tickets/{id}/escalate', [App\Http\Controllers\AdminSupportController::class, 'support_escalate'])->name('admin.support.escalate');
        Route::get('/support/tickets/{id}/deescalate', [App\Http\Controllers\AdminSupportController::class, 'support_deescalate'])->name('admin.support.deescalate');
        Route::get('/support/tickets/{id}/hold', [App\Http\Controllers\AdminSupportController::class, 'support_hold'])->name('admin.support.hold');
        Route::get('/support/tickets/{id}/unhold', [App\Http\Controllers\AdminSupportController::class, 'support_unhold'])->name('admin.support.unhold');
        Route::post('/support/tickets/{id}/move', [App\Http\Controllers\AdminSupportController::class, 'support_move'])->name('admin.support.move');
        Route::post('/support/tickets/{id}/priority', [App\Http\Controllers\AdminSupportController::class, 'support_priority'])->name('admin.support.priority');
        Route::post('/support/tickets/run/start', [App\Http\Controllers\AdminSupportController::class, 'support_run_start'])->name('admin.support.run.start');
        Route::get('/support/tickets/run/stop', [App\Http\Controllers\AdminSupportController::class, 'support_run_stop'])->name('admin.support.run.stop');
        Route::get('/support/tickets/run/next', [App\Http\Controllers\AdminSupportController::class, 'support_run_next'])->name('admin.support.run.next');
        Route::get('/support/tickets/{id}/file/download/{filelink_id}', [App\Http\Controllers\AdminSupportController::class, 'support_file_download'])->name('admin.support.file.download');
        Route::get('/support/tickets/{id}/file/delete/{filelink_id}', [App\Http\Controllers\AdminSupportController::class, 'support_file_delete'])->name('admin.support.file.delete');
        Route::post('/support/tickets/{id}/file/upload', [App\Http\Controllers\AdminSupportController::class, 'support_file_upload'])->name('admin.support.file.upload');

        Route::get('/crm/leads', [App\Http\Controllers\AdminCRMController::class, 'crm_index'])->name('admin.crm');
        Route::get('/crm/list/{category}/{type}', [App\Http\Controllers\AdminCRMController::class, 'crm_index_list'])->name('admin.crm.list');
        Route::get('/crm/leads/{id}', [App\Http\Controllers\AdminCRMController::class, 'crm_details'])->name('admin.crm.details');
        Route::get('/crm/leads/{id}/list', [App\Http\Controllers\AdminCRMController::class, 'crm_details_list'])->name('admin.crm.contacts.list');
        Route::post('/crm/leads/{id}/add', [App\Http\Controllers\AdminCRMController::class, 'crm_contacts_add'])->name('admin.crm.contacts.add');
        Route::post('/crm/leads/{id}/update/{contact_id}', [App\Http\Controllers\AdminCRMController::class, 'crm_contacts_update'])->name('admin.crm.contacts.update');
        Route::get('/crm/leads/{id}/delete/{contact_id}', [App\Http\Controllers\AdminCRMController::class, 'crm_contacts_delete'])->name('admin.crm.contacts.delete');

        Route::middleware('role.admin')->group(function () {
            Route::get('/settings', [App\Http\Controllers\AdminSettingsController::class, 'settings_index'])->name('admin.settings');
            Route::get('/settings/list', [App\Http\Controllers\AdminSettingsController::class, 'settings_list'])->name('admin.settings.list');
            Route::post('/settings/assets/update', [App\Http\Controllers\AdminSettingsController::class, 'settings_assets_update'])->name('admin.settings.assets');
            Route::get('/settings/remove/{setting}', [App\Http\Controllers\AdminSettingsController::class, 'settings_assets_remove'])->name('admin.settings.assets.remove');
            Route::post('/settings/{id}/update', [App\Http\Controllers\AdminSettingsController::class, 'settings_update'])->name('admin.settings.update');

            Route::get('/support/categories', [App\Http\Controllers\AdminSupportController::class, 'support_categories'])->name('admin.support.categories');
            Route::get('/support/categories/list', [App\Http\Controllers\AdminSupportController::class, 'support_categories_list'])->name('admin.support.categories.list');
            Route::get('/support/categories/list/{id}', [App\Http\Controllers\AdminSupportController::class, 'support_category_user_list'])->name('admin.support.categories.list.users');
            Route::post('/support/categories/add', [App\Http\Controllers\AdminSupportController::class, 'support_categories_add'])->name('admin.support.categories.add');
            Route::post('/support/categories/{id}/update', [App\Http\Controllers\AdminSupportController::class, 'support_categories_update'])->name('admin.support.categories.update');
            Route::get('/support/categories/{id}/delete', [App\Http\Controllers\AdminSupportController::class, 'support_categories_delete'])->name('admin.support.categories.delete');
            Route::post('/support/categories/{id}/add', [App\Http\Controllers\AdminSupportController::class, 'support_category_user_add'])->name('admin.support.categories.user.add');
            Route::get('/support/categories/{id}/delete/{category_link_id}', [App\Http\Controllers\AdminSupportController::class, 'support_category_user_delete'])->name('admin.support.categories.user.delete');

            Route::get('/crm/categories', [App\Http\Controllers\AdminCRMController::class, 'crm_categories'])->name('admin.crm.categories');
            Route::get('/crm/categories/list', [App\Http\Controllers\AdminCRMController::class, 'crm_categories_list'])->name('admin.crm.categories.list');
            Route::get('/crm/categories/list/{id}', [App\Http\Controllers\AdminCRMController::class, 'crm_category_lead_list'])->name('admin.crm.categories.list.leads');
            Route::post('/crm/categories/add', [App\Http\Controllers\AdminCRMController::class, 'crm_categories_add'])->name('admin.crm.categories.add');
            Route::post('/crm/categories/{id}/update', [App\Http\Controllers\AdminCRMController::class, 'crm_categories_update'])->name('admin.crm.categories.update');
            Route::get('/crm/categories/{id}/delete', [App\Http\Controllers\AdminCRMController::class, 'crm_categories_delete'])->name('admin.crm.categories.delete');
            Route::post('/crm/categories/{id}/add', [App\Http\Controllers\AdminCRMController::class, 'crm_category_lead_add'])->name('admin.crm.categories.lead.add');
            Route::get('/crm/categories/{id}/delete/{category_link_id}', [App\Http\Controllers\AdminCRMController::class, 'crm_category_lead_delete'])->name('admin.crm.categories.lead.delete');

            Route::get('/invoices/types', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_types_index'])->name('admin.invoices.types');
            Route::get('/invoices/types/list', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_types_list'])->name('admin.invoices.types.list');
            Route::post('/invoices/types/add', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_types_add'])->name('admin.invoices.types.add');
            Route::post('/invoices/types/{id}/update', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_types_update'])->name('admin.invoices.types.update');
            Route::get('/invoices/types/{id}/delete', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_types_delete'])->name('admin.invoices.types.delete');
            Route::get('/invoices/types/{id}', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_types_details'])->name('admin.invoices.types.details');
            Route::get('/invoices/types/{id}/dunning/list', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_dunning_list'])->name('admin.invoices.dunning.list');
            Route::post('/invoices/types/{id}/dunning/add', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_dunning_add'])->name('admin.invoices.dunning.add');
            Route::post('/invoices/types/{id}/dunning/{dunning_id}/update', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_dunning_update'])->name('admin.invoices.dunning.update');
            Route::get('/invoices/types/{id}/dunning/{dunning_id}/delete', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_dunning_delete'])->name('admin.invoices.dunning.delete');
            Route::get('/invoices/discounts', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_discounts_index'])->name('admin.invoices.discounts');
            Route::get('/invoices/discounts/list', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_discounts_list'])->name('admin.invoices.discounts.list');
            Route::post('/invoices/discounts/add', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_discounts_add'])->name('admin.invoices.discounts.add');
            Route::post('/invoices/discounts/{id}/update', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_discounts_update'])->name('admin.invoices.discounts.update');
            Route::get('/invoices/discounts/{id}/delete', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_discounts_delete'])->name('admin.invoices.discounts.delete');

            Route::get('/contracts/types', [App\Http\Controllers\AdminContractController::class, 'contract_types_index'])->name('admin.contracts.types');
            Route::get('/contracts/types/list', [App\Http\Controllers\AdminContractController::class, 'contract_types_list'])->name('admin.contracts.types.list');
            Route::post('/contracts/types/add', [App\Http\Controllers\AdminContractController::class, 'contract_types_add'])->name('admin.contracts.types.add');
            Route::post('/contracts/types/{id}/update', [App\Http\Controllers\AdminContractController::class, 'contract_types_update'])->name('admin.contracts.types.update');
            Route::get('/contracts/types/{id}/delete', [App\Http\Controllers\AdminContractController::class, 'contract_types_delete'])->name('admin.contracts.types.delete');

            Route::get('/contracts/trackers', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_index'])->name('admin.contracts.trackers');
            Route::get('/contracts/trackers/list', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_list'])->name('admin.contracts.trackers.list');
            Route::post('/contracts/trackers/add', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_add'])->name('admin.contracts.trackers.add');
            Route::post('/contracts/trackers/{id}/update', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_update'])->name('admin.contracts.trackers.update');
            Route::get('/contracts/trackers/{id}/delete', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_delete'])->name('admin.contracts.trackers.delete');
            Route::get('/contracts/trackers/{id}', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_details'])->name('admin.contracts.trackers.details');
            Route::get('/contracts/trackers/{id}/items/list', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_items_list'])->name('admin.contracts.trackers.items.list');
            Route::post('/contracts/trackers/{id}/items/add', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_items_add'])->name('admin.contracts.trackers.items.add');
            Route::post('/contracts/trackers/{id}/items/{item_id}/update', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_items_update'])->name('admin.contracts.trackers.items.update');
            Route::get('/contracts/trackers/{id}/items/{item_id}/delete', [App\Http\Controllers\AdminContractController::class, 'contract_trackers_items_delete'])->name('admin.contracts.trackers.items.delete');

            Route::get('/discounts', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'discount_index'])->name('admin.discounts');
            Route::get('/discounts/list', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'discount_list'])->name('admin.discounts.list');
            Route::post('/discounts/add', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'discount_add'])->name('admin.discounts.add');
            Route::post('/discounts/{id}/update', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'discount_update'])->name('admin.discounts.update');
            Route::get('/discounts/{id}/delete', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'discount_delete'])->name('admin.discounts.delete');

            Route::get('/paymentgateways', [App\Http\Controllers\AdminPaymentGatewayController::class, 'gateway_index'])->name('admin.paymentgateways');
            Route::post('/paymentgateways/save', [App\Http\Controllers\AdminPaymentGatewayController::class, 'gateway_save'])->name('admin.paymentgateways.save');

            Route::get('/products', [App\Http\Controllers\AdminShopController::class, 'products_index'])->name('admin.products');
            Route::post('/products/save', [App\Http\Controllers\AdminShopController::class, 'products_save'])->name('admin.products.save');

            Route::get('/api/users', [App\Http\Controllers\AdminAPIController::class, 'apiuser_index'])->name('admin.api.users');
            Route::get('/api/users/list', [App\Http\Controllers\AdminAPIController::class, 'apiuser_list'])->name('admin.api.users.list');
            Route::post('/api/users/create', [App\Http\Controllers\AdminAPIController::class, 'apiuser_create'])->name('admin.api.users.create');
            Route::post('/api/users/{id}/update', [App\Http\Controllers\AdminAPIController::class, 'apiuser_update'])->name('admin.api.users.update');
            Route::get('/api/users/{id}/delete', [App\Http\Controllers\AdminAPIController::class, 'apiuser_delete'])->name('admin.api.users.delete');
            Route::get('/api/users/{id}/lock', [App\Http\Controllers\AdminAPIController::class, 'apiuser_lock'])->name('admin.api.users.lock');

            Route::get('/api/oauth-clients', [App\Http\Controllers\AdminAPIController::class, 'apiclient_index'])->name('admin.api.oauth-clients');
            Route::get('/api/oauth-clients/list', [App\Http\Controllers\AdminAPIController::class, 'apiclient_list'])->name('admin.api.oauth-clients.list');
            Route::post('/api/oauth-clients/create', [App\Http\Controllers\AdminAPIController::class, 'apiclient_create'])->name('admin.api.oauth-clients.create');
            Route::get('/api/oauth-clients/{id}/delete', [App\Http\Controllers\AdminAPIController::class, 'apiclient_delete'])->name('admin.api.oauth-clients.delete');
        });

        Route::get('/invoices/customers', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_index'])->name('admin.invoices.customers');
        Route::get('/invoices/customers/list/{user_id}', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_list'])->name('admin.invoices.customers.list');
        Route::get('/invoices/customers/list', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_list'])->name('admin.invoices.customers.list');
        Route::post('/invoices/customers/add', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_add'])->name('admin.invoices.customers.add');
        Route::post('/invoices/customers/{id}/update', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_update'])->name('admin.invoices.customers.update');
        Route::get('/invoices/customers/{id}/delete', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_delete'])->name('admin.invoices.customers.delete');
        Route::get('/invoices/customers/{id}/publish', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_publish'])->name('admin.invoices.customers.publish');
        Route::get('/invoices/customers/{id}/download', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_download'])->name('admin.invoices.customers.download');
        Route::get('/invoices/customers/{id}/reminder/{reminder_id}', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_reminder_download'])->name('admin.invoices.customers.reminders.download');
        Route::get('/invoices/customers/{id}/unpaid', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_unpaid'])->name('admin.invoices.customers.unpaid');
        Route::get('/invoices/customers/{id}/paid', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_paid'])->name('admin.invoices.customers.paid');
        Route::get('/invoices/customers/{id}/revoke', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_revoke'])->name('admin.invoices.customers.revoke');
        Route::get('/invoices/customers/{id}/refund', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_refund'])->name('admin.invoices.customers.refund');
        Route::get('/invoices/customers/{id}/resend', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_resend'])->name('admin.invoices.customers.resend');
        Route::post('/invoices/customers/{id}/position/add', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_positions_add'])->name('admin.invoices.customers.positions.add');
        Route::post('/invoices/customers/{id}/position/update/{position_id}', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_positions_update'])->name('admin.invoices.customers.positions.update');
        Route::get('/invoices/customers/{id}/position/delete/{position_id}', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_positions_delete'])->name('admin.invoices.customers.positions.delete');
        Route::get('/invoices/customers/{id}/history', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_history'])->name('admin.invoices.customers.history');
        Route::get('/invoices/customers/{id}', [App\Http\Controllers\AdminInvoiceCustomerController::class, 'invoice_details'])->name('admin.invoices.customers.details');

        Route::get('/invoices/suppliers', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_index'])->name('admin.invoices.suppliers');
        Route::get('/invoices/suppliers/list/{user_id}', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_list'])->name('admin.invoices.suppliers.list');
        Route::get('/invoices/suppliers/list', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_list'])->name('admin.invoices.suppliers.list');
        Route::post('/invoices/suppliers/add', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_add'])->name('admin.invoices.suppliers.add');
        Route::post('/invoices/suppliers/{id}/update', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_update'])->name('admin.invoices.suppliers.update');
        Route::get('/invoices/suppliers/{id}/delete', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_delete'])->name('admin.invoices.suppliers.delete');
        Route::get('/invoices/suppliers/{id}/publish', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_publish'])->name('admin.invoices.suppliers.publish');
        Route::get('/invoices/suppliers/{id}/download', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_download'])->name('admin.invoices.suppliers.download');
        Route::get('/invoices/suppliers/{id}/unpaid', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_unpaid'])->name('admin.invoices.suppliers.unpaid');
        Route::get('/invoices/suppliers/{id}/paid', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_paid'])->name('admin.invoices.suppliers.paid');
        Route::get('/invoices/suppliers/{id}/revoke', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_revoke'])->name('admin.invoices.suppliers.revoke');
        Route::post('/invoices/suppliers/{id}/refund', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_refund'])->name('admin.invoices.suppliers.refund');
        Route::post('/invoices/suppliers/{id}/position/add', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_positions_add'])->name('admin.invoices.suppliers.positions.add');
        Route::post('/invoices/suppliers/{id}/position/update/{position_id}', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_positions_update'])->name('admin.invoices.suppliers.positions.update');
        Route::get('/invoices/suppliers/{id}/position/delete/{position_id}', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_positions_delete'])->name('admin.invoices.suppliers.positions.delete');
        Route::get('/invoices/suppliers/{id}/history', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_history'])->name('admin.invoices.suppliers.history');
        Route::get('/invoices/suppliers/{id}', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_details'])->name('admin.invoices.suppliers.details');

        Route::get('/invoices/importers', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_index'])->name('admin.invoices.importers');
        Route::get('/invoices/importers/list', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_list'])->name('admin.invoices.importers.list');
        Route::post('/invoices/importers/add', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_add'])->name('admin.invoices.importers.add');
        Route::post('/invoices/importers/{id}/update', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_update'])->name('admin.invoices.importers.update');
        Route::get('/invoices/importers/{id}/delete', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_delete'])->name('admin.invoices.importers.delete');
        Route::get('/invoices/importers/{id}/log', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_log'])->name('admin.invoices.importers.log');
        Route::get('/invoices/importers/{id}/log/list', [App\Http\Controllers\AdminInvoiceSupplierController::class, 'invoice_importers_log_list'])->name('admin.invoices.importers.log.list');

        Route::get('/contracts', [App\Http\Controllers\AdminContractController::class, 'contract_index'])->name('admin.contracts');
        Route::get('/contracts/list/{user_id}', [App\Http\Controllers\AdminContractController::class, 'contract_list'])->name('admin.contracts.list.extended');
        Route::get('/contracts/list', [App\Http\Controllers\AdminContractController::class, 'contract_list'])->name('admin.contracts.list');
        Route::post('/contracts/add', [App\Http\Controllers\AdminContractController::class, 'contract_add'])->name('admin.contracts.add');
        Route::post('/contracts/{id}/update', [App\Http\Controllers\AdminContractController::class, 'contract_update'])->name('admin.contracts.update');
        Route::get('/contracts/{id}/delete', [App\Http\Controllers\AdminContractController::class, 'contract_delete'])->name('admin.contracts.delete');
        Route::get('/contracts/{id}/start', [App\Http\Controllers\AdminContractController::class, 'contract_start'])->name('admin.contracts.start');
        Route::get('/contracts/{id}/extend', [App\Http\Controllers\AdminContractController::class, 'contract_extend'])->name('admin.contracts.extend');
        Route::get('/contracts/{id}/stop', [App\Http\Controllers\AdminContractController::class, 'contract_stop'])->name('admin.contracts.stop');
        Route::get('/contracts/{id}/cancel', [App\Http\Controllers\AdminContractController::class, 'contract_cancel'])->name('admin.contracts.cancel');
        Route::get('/contracts/{id}/restart', [App\Http\Controllers\AdminContractController::class, 'contract_restart'])->name('admin.contracts.restart');
        Route::get('/contracts/{id}/revoke', [App\Http\Controllers\AdminContractController::class, 'contract_cancellation_revoke'])->name('admin.contracts.revoke-cancellation');
        Route::post('/contracts/{id}/position/add', [App\Http\Controllers\AdminContractController::class, 'contract_positions_add'])->name('admin.contracts.positions.add');
        Route::post('/contracts/{id}/position/update/{position_id}', [App\Http\Controllers\AdminContractController::class, 'contract_positions_update'])->name('admin.contracts.positions.update');
        Route::get('/contracts/{id}/position/delete/{position_id}', [App\Http\Controllers\AdminContractController::class, 'contract_positions_delete'])->name('admin.contracts.positions.delete');
        Route::get('/contracts/{id}', [App\Http\Controllers\AdminContractController::class, 'contract_details'])->name('admin.contracts.details');

        Route::get('/profile', [App\Http\Controllers\AdminProfileController::class, 'profile_index'])->name('admin.profile');
        Route::post('/profile/update', [App\Http\Controllers\AdminProfileController::class, 'profile_update'])->name('admin.profile.update');
        Route::post('/profile/password', [App\Http\Controllers\AdminProfileController::class, 'profile_password'])->name('admin.profile.password');
        Route::post('/profile/2fa-confirm', [App\Http\Controllers\AdminProfileController::class, 'profile_2fa_confirm'])->name('two-factor.confirm.admin');

        Route::get('/customers', [App\Http\Controllers\AdminCustomerController::class, 'customer_index'])->name('admin.customers');
        Route::get('/customers/list', [App\Http\Controllers\AdminCustomerController::class, 'customer_list'])->name('admin.customers.list');
        Route::post('/customers/create', [App\Http\Controllers\AdminCustomerController::class, 'customer_create'])->name('admin.customers.create');
        Route::get('/customers/{user_id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_index'])->name('admin.customers.profile');
        Route::get('/customers/{user_id}/twofactor/disable', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_2fa_disable'])->name('admin.customers.profile.2fa.disable');
        Route::post('/customers/{user_id}/update', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_update'])->name('admin.customers.profile.update');
        Route::post('/customers/{user_id}/details/update', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_details_update'])->name('admin.customers.profile.update.details');
        Route::get('/customers/{user_id}/lock', [App\Http\Controllers\AdminCustomerController::class, 'customer_lock'])->name('admin.customers.lock');
        Route::post('/customers/{user_id}/password', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_password'])->name('admin.customers.profile.password');
        Route::post('/customers/{user_id}/complete', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_complete'])->name('admin.customers.profile.complete');
        Route::post('/customers/{user_id}/emailaddresses/create', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_email_create'])->name('admin.customers.profile.email.create');
        Route::post('/customers/{user_id}/emailaddresses/update', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_email_update'])->name('admin.customers.profile.email.update');
        Route::get('/customers/{user_id}/emailaddresses/delete/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_email_delete'])->name('admin.customers.profile.email.delete');
        Route::get('/customers/{user_id}/emailaddresses/resend/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_email_resend'])->name('admin.customers.profile.email.resend');
        Route::get('/customers/{user_id}/list/emailaddresses', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_email_list'])->name('admin.customers.profile.email.list');
        Route::post('/customers/{user_id}/phonenumbers/create', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_phone_create'])->name('admin.customers.profile.phone.create');
        Route::post('/customers/{user_id}/phonenumbers/update', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_phone_update'])->name('admin.customers.profile.phone.update');
        Route::get('/customers/{user_id}/phonenumbers/delete/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_phone_delete'])->name('admin.customers.profile.phone.delete');
        Route::get('/customers/{user_id}/list/phonenumbers', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_phone_list'])->name('admin.customers.profile.phone.list');
        Route::post('/customers/{user_id}/addresses/create', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_address_create'])->name('admin.customers.profile.address.create');
        Route::post('/customers/{user_id}/addresses/update', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_address_update'])->name('admin.customers.profile.address.update');
        Route::get('/customers/{user_id}/addresses/delete/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_address_delete'])->name('admin.customers.profile.address.delete');
        Route::get('/customers/{user_id}/list/addresses', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_address_list'])->name('admin.customers.profile.address.list');
        Route::post('/customers/{user_id}/bankaccounts/create', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_bank_create'])->name('admin.customers.profile.bank.create');
        Route::post('/customers/{user_id}/bankaccounts/sepa', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_bank_sepa'])->name('admin.customers.profile.bank.sepa');
        Route::get('/customers/{user_id}/bankaccounts/primary/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_bank_primary'])->name('admin.customers.profile.bank.primary');
        Route::get('/customers/{user_id}/bankaccounts/delete/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_bank_delete'])->name('admin.customers.profile.bank.delete');
        Route::get('/customers/{user_id}/list/bankaccounts', [App\Http\Controllers\AdminCustomerController::class, 'customer_profile_bank_list'])->name('admin.customers.profile.bank.list');
        Route::post('/customers/{user_id}/transactions/create', [App\Http\Controllers\AdminCustomerController::class, 'customer_transaction_create'])->name('admin.customers.transactions.create');
        Route::post('/customers/{user_id}/transactions/update', [App\Http\Controllers\AdminCustomerController::class, 'customer_transaction_update'])->name('admin.customers.transactions.update');
        Route::get('/customers/{user_id}/transactions/delete/{id}', [App\Http\Controllers\AdminCustomerController::class, 'customer_transaction_delete'])->name('admin.customers.transactions.delete');
        Route::get('/customers/{user_id}/list/transactions', [App\Http\Controllers\AdminCustomerController::class, 'customer_transaction_list'])->name('admin.customers.transactions.list');

        Route::get('/suppliers', [App\Http\Controllers\AdminSupplierController::class, 'supplier_index'])->name('admin.suppliers');
        Route::get('/suppliers/list', [App\Http\Controllers\AdminSupplierController::class, 'supplier_list'])->name('admin.suppliers.list');
        Route::post('/suppliers/create', [App\Http\Controllers\AdminSupplierController::class, 'supplier_create'])->name('admin.suppliers.create');
        Route::get('/suppliers/{user_id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_index'])->name('admin.suppliers.profile');
        Route::post('/suppliers/{user_id}/update', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_update'])->name('admin.suppliers.profile.update');
        Route::post('/suppliers/{user_id}/details/update', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_details_update'])->name('admin.suppliers.profile.update.details');
        Route::post('/suppliers/{user_id}/complete', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_complete'])->name('admin.suppliers.profile.complete');
        Route::post('/suppliers/{user_id}/emailaddresses/create', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_email_create'])->name('admin.suppliers.profile.email.create');
        Route::post('/suppliers/{user_id}/emailaddresses/update', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_email_update'])->name('admin.suppliers.profile.email.update');
        Route::get('/suppliers/{user_id}/emailaddresses/delete/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_email_delete'])->name('admin.suppliers.profile.email.delete');
        Route::get('/suppliers/{user_id}/emailaddresses/resend/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_email_resend'])->name('admin.suppliers.profile.email.resend');
        Route::get('/suppliers/{user_id}/list/emailaddresses', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_email_list'])->name('admin.suppliers.profile.email.list');
        Route::post('/suppliers/{user_id}/phonenumbers/create', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_phone_create'])->name('admin.suppliers.profile.phone.create');
        Route::post('/suppliers/{user_id}/phonenumbers/update', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_phone_update'])->name('admin.suppliers.profile.phone.update');
        Route::get('/suppliers/{user_id}/phonenumbers/delete/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_phone_delete'])->name('admin.suppliers.profile.phone.delete');
        Route::get('/suppliers/{user_id}/list/phonenumbers', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_phone_list'])->name('admin.suppliers.profile.phone.list');
        Route::post('/suppliers/{user_id}/addresses/create', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_address_create'])->name('admin.suppliers.profile.address.create');
        Route::post('/suppliers/{user_id}/addresses/update', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_address_update'])->name('admin.suppliers.profile.address.update');
        Route::get('/suppliers/{user_id}/addresses/delete/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_address_delete'])->name('admin.suppliers.profile.address.delete');
        Route::get('/suppliers/{user_id}/list/addresses', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_address_list'])->name('admin.suppliers.profile.address.list');
        Route::post('/suppliers/{user_id}/bankaccounts/create', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_bank_create'])->name('admin.suppliers.profile.bank.create');
        Route::post('/suppliers/{user_id}/bankaccounts/sepa', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_bank_sepa'])->name('admin.suppliers.profile.bank.sepa');
        Route::get('/suppliers/{user_id}/bankaccounts/primary/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_bank_primary'])->name('admin.suppliers.profile.bank.primary');
        Route::get('/suppliers/{user_id}/bankaccounts/delete/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_bank_delete'])->name('admin.suppliers.profile.bank.delete');
        Route::get('/suppliers/{user_id}/list/bankaccounts', [App\Http\Controllers\AdminSupplierController::class, 'supplier_profile_bank_list'])->name('admin.suppliers.profile.bank.list');
        Route::post('/suppliers/{user_id}/transactions/create', [App\Http\Controllers\AdminSupplierController::class, 'supplier_transaction_create'])->name('admin.suppliers.transactions.create');
        Route::post('/suppliers/{user_id}/transactions/update', [App\Http\Controllers\AdminSupplierController::class, 'supplier_transaction_update'])->name('admin.suppliers.transactions.update');
        Route::get('/suppliers/{user_id}/transactions/delete/{id}', [App\Http\Controllers\AdminSupplierController::class, 'supplier_transaction_delete'])->name('admin.suppliers.transactions.delete');
        Route::get('/suppliers/{user_id}/list/transactions', [App\Http\Controllers\AdminSupplierController::class, 'supplier_transaction_list'])->name('admin.suppliers.transactions.list');

        Route::get('/filemanager', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_index'])->name('admin.filemanager');
        Route::get('/filemanager/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_index'])->name('admin.filemanager.folder');
        Route::get('/filemanager/list/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_list'])->name('admin.filemanager.list');
        Route::post('/filemanager/folder/create', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_folder_create'])->name('admin.filemanager.folder.create');
        Route::post('/filemanager/folder/update/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_folder_update'])->name('admin.filemanager.folder.update');
        Route::get('/filemanager/folder/delete/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_folder_delete'])->name('admin.filemanager.folder.delete');
        Route::post('/filemanager/file/create', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_file_create'])->name('admin.filemanager.file.create');
        Route::post('/filemanager/file/update/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_file_update'])->name('admin.filemanager.file.update');
        Route::get('/filemanager/file/delete/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_file_delete'])->name('admin.filemanager.file.delete');
        Route::get('/filemanager/file/download/{id}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_file_download'])->name('admin.filemanager.file.download');

        Route::get('/pages', [App\Http\Controllers\AdminPageController::class, 'page_index'])->name('admin.pages');
        Route::get('/pages/list/{id}', [App\Http\Controllers\AdminPageController::class, 'page_version_list'])->name('admin.pages.versions.list');
        Route::get('/pages/list', [App\Http\Controllers\AdminPageController::class, 'page_list'])->name('admin.pages.list');
        Route::post('/pages/add', [App\Http\Controllers\AdminPageController::class, 'page_add'])->name('admin.pages.add');
        Route::post('/pages/{id}/update', [App\Http\Controllers\AdminPageController::class, 'page_update'])->name('admin.pages.update');
        Route::get('/pages/{id}/delete', [App\Http\Controllers\AdminPageController::class, 'page_delete'])->name('admin.pages.delete');
        Route::post('/pages/{id}/versions/add', [App\Http\Controllers\AdminPageController::class, 'page_version_add'])->name('admin.pages.versions.add');
        Route::post('/pages/{id}/versions/{version_id}/update', [App\Http\Controllers\AdminPageController::class, 'page_version_update'])->name('admin.pages.versions.update');
        Route::get('/pages/{id}/versions/{version_id}/delete', [App\Http\Controllers\AdminPageController::class, 'page_version_delete'])->name('admin.pages.versions.delete');
        Route::get('/pages/{id}', [App\Http\Controllers\AdminPageController::class, 'page_details'])->name('admin.pages.details');

        Route::get('/shop', [App\Http\Controllers\AdminShopController::class, 'shop_categories_index'])->name('admin.shop.categories');
        Route::post('/shop/categories/add', [App\Http\Controllers\AdminShopController::class, 'shop_categories_add'])->name('admin.shop.categories.add');
        Route::post('/shop/categories/update/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_categories_update'])->name('admin.shop.categories.update');
        Route::get('/shop/categories/delete/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_categories_delete'])->name('admin.shop.categories.delete');
        Route::post('/shop/forms/add', [App\Http\Controllers\AdminShopController::class, 'shop_forms_add'])->name('admin.shop.forms.add');
        Route::post('/shop/forms/update/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_forms_update'])->name('admin.shop.forms.update');
        Route::get('/shop/forms/delete/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_forms_delete'])->name('admin.shop.forms.delete');
        Route::post('/shop/fields/add', [App\Http\Controllers\AdminShopController::class, 'shop_fields_add'])->name('admin.shop.fields.add');
        Route::post('/shop/fields/update/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_fields_update'])->name('admin.shop.fields.update');
        Route::get('/shop/fields/delete/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_fields_delete'])->name('admin.shop.fields.delete');
        Route::get('/shop/forms/list', [App\Http\Controllers\AdminShopController::class, 'shop_forms_list'])->name('admin.shop.forms.list');
        Route::get('/shop/forms/list/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_forms_list'])->name('admin.shop.forms.list.details');
        Route::get('/shop/fields/list/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_fields_list'])->name('admin.shop.fields.list');
        Route::get('/shop/categories/list', [App\Http\Controllers\AdminShopController::class, 'shop_categories_list'])->name('admin.shop.categories.list');
        Route::get('/shop/categories/list/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_categories_list'])->name('admin.shop.categories.list');
        Route::get('/shop/{category_id}/forms/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_forms_index'])->name('admin.shop.forms.details');
        Route::get('/shop/forms/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_forms_index'])->name('admin.shop.forms');

        Route::get('/shop/orders', [App\Http\Controllers\AdminShopController::class, 'shop_orders_index'])->name('admin.shop.orders');
        Route::get('/shop/orders/list/{user_id}', [App\Http\Controllers\AdminShopController::class, 'shop_orders_list'])->name('admin.orders.list');
        Route::get('/shop/orders/list', [App\Http\Controllers\AdminShopController::class, 'shop_orders_list'])->name('admin.shop.orders.list');
        Route::post('/shop/orders/update/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_orders_update'])->name('admin.shop.orders.update');
        Route::get('/shop/orders/approve/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_orders_approve'])->name('admin.shop.orders.approve');
        Route::get('/shop/orders/disapprove/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_orders_disapprove'])->name('admin.shop.orders.disapprove');
        Route::get('/shop/orders/delete/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_orders_delete'])->name('admin.shop.orders.delete');

        Route::get('/shop/{id}', [App\Http\Controllers\AdminShopController::class, 'shop_categories_index'])->name('admin.shop.categories.details');

        Products::list()->transform(function ($handler) {
            return (object) [
                'instance'     => $handler,
                'capabilities' => $handler->capabilities(),
            ];
        })->reject(function ($handler) {
            return !$handler->capabilities->contains('service') || !$handler->instance->ui()->admin;
        })->each(function ($handler) {
            App::bind(Product::class, function () use ($handler) {
                return $handler->instance;
            });

            Route::get('/services/' . $handler->instance->technicalName(), [App\Http\Controllers\AdminServiceController::class, 'service_index'])->name('admin.services.' . $handler->instance->technicalName());
            Route::get('/services/' . $handler->instance->technicalName() . '/list', [App\Http\Controllers\AdminServiceController::class, 'service_list'])->name('admin.services.' . $handler->instance->technicalName() . '.list');
            Route::get('/services/' . $handler->instance->technicalName() . '/{id}', [App\Http\Controllers\AdminServiceController::class, 'service_details'])->name('admin.services.' . $handler->instance->technicalName() . '.details');
            Route::get('/services/' . $handler->instance->technicalName() . '/{id}/statistics', [App\Http\Controllers\AdminServiceController::class, 'service_statistics'])->name('admin.services.' . $handler->instance->technicalName() . '.statistics');

            if ($handler->capabilities->contains('service.start')) {
                Route::get('/services/' . $handler->instance->technicalName() . '/{id}/start', [App\Http\Controllers\AdminServiceController::class, 'service_start'])->name('admin.services.' . $handler->instance->technicalName() . '.start');
            }

            if ($handler->capabilities->contains('service.stop')) {
                Route::get('/services/' . $handler->instance->technicalName() . '/{id}/start', [App\Http\Controllers\AdminServiceController::class, 'service_stop'])->name('admin.services.' . $handler->instance->technicalName() . '.stop');
            }

            if ($handler->capabilities->contains('service.restart')) {
                Route::get('/services/' . $handler->instance->technicalName() . '/{id}/start', [App\Http\Controllers\AdminServiceController::class, 'service_start'])->name('admin.services.' . $handler->instance->technicalName() . '.restart');
            }
        });

        Route::group([
            'middleware' => [
                'tenant.prohibit',
            ],
        ], function () {
            Route::get('/tenants', [App\Http\Controllers\AdminTenantController::class, 'tenant_index'])->name('admin.tenants');
            Route::get('/tenants/list', [App\Http\Controllers\AdminTenantController::class, 'tenant_list'])->name('admin.tenants.list');
            Route::post('/tenants/add', [App\Http\Controllers\AdminTenantController::class, 'tenant_add'])->name('admin.tenants.add');
            Route::post('/tenants/update/{id}', [App\Http\Controllers\AdminTenantController::class, 'tenant_update'])->name('admin.tenants.update');
            Route::get('/tenants/delete/{id}', [App\Http\Controllers\AdminTenantController::class, 'tenant_delete'])->name('admin.tenants.delete');
        });
    });
});

Route::any('/api/webdav', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_file_webdav'])->name('admin.filemanager.webdav');
Route::any('/api/webdav/{path?}', [App\Http\Controllers\AdminFilemanagerController::class, 'filemanager_file_webdav'])->where('path', '(.*)')->name('admin.filemanager.webdav.path');

Route::any('/payment/pingback/{payment_method}/{payment_type}', [App\Http\Controllers\PaymentController::class, 'pingback'])->name('customer.payment.pingback');

try {
    Route::group([
        'middleware' => [
            'products.customer',
        ],
    ], function () {
        Page::query()->each(function (Page $page) {
            Route::get(__($page->route), [App\Http\Controllers\PublicPageController::class, 'render'])->name('cms.page.' . $page->id);
            Route::get(__($page->route) . '/{id}', [App\Http\Controllers\PublicPageController::class, 'render_version'])->name('cms.page.' . $page->id . '.version');
        });
    });
} catch (Exception $exception) {
}

Route::group([
    'middleware' => [
        'shop.categoryOrProduct',
        'products.customer',
    ],
], function () {
    Route::get('/shop', [App\Http\Controllers\CustomerShopController::class, 'render_category'])->name('public.shop');

    try {
        ShopConfiguratorCategory::query()
            ->where('public', '=', true)
            ->each(function (ShopConfiguratorCategory $category) {
                Route::get($category->fullRoute, [App\Http\Controllers\CustomerShopController::class, 'render_category'])->name('public.shop.categories.' . $category->id);
            });
    } catch (Exception $exception) {
    }

    try {
        ShopConfiguratorForm::query()
            ->where('public', '=', true)
            ->each(function (ShopConfiguratorForm $form) {
                Route::get($form->fullRoute, [App\Http\Controllers\CustomerShopController::class, 'render_form'])->name('public.shop.forms.' . $form->id);
            });
    } catch (Exception $exception) {
    }

    Route::middleware([
        'auth',
        'verified',
        'unlocked',
        'password.check_reset',
        'role.customer',
    ])->group(function () {
        Route::post('/shop/process', [App\Http\Controllers\CustomerShopController::class, 'process'])->name('customer.shop.process');
        Route::get('/shop/success/{id}', [App\Http\Controllers\CustomerShopController::class, 'render_success'])->name('customer.shop.success');
    });
});
