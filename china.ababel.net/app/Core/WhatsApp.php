<?php
// app/Core/WhatsApp.php
namespace App\Core;

class WhatsApp
{
    /**
     * Send receipt via WhatsApp using WhatsApp Business API
     * You can use CallMeBot or WhatsApp Business API
     */
    public static function sendReceipt($phone, $message)
    {
        // Remove any non-numeric characters from phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present (Sudan: +249)
        if (!str_starts_with($phone, '249') && strlen($phone) == 9) {
            $phone = '249' . $phone;
        }
        
        // Method 1: Using WhatsApp URL Scheme (opens WhatsApp with pre-filled message)
        $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($message);
        
        return $whatsappUrl;
    }
    
    /**
     * Format receipt message for WhatsApp
     */
    public static function formatReceiptMessage($transaction, $client, $company = [])
    {
        $companyName = $company['name'] ?? __('company_name');
        $date = date('Y-m-d', strtotime($transaction['transaction_date']));
        
        $message = "🧾 *{$companyName}*\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "📋 *" . __('transactions.transaction_no') . ":* {$transaction['transaction_no']}\n";
        $message .= "📅 *" . __('date') . ":* {$date}\n";
        $message .= "👤 *" . __('clients.name') . ":* {$client['name']}\n\n";
        
        if ($transaction['invoice_no']) {
            $message .= "🔢 *" . __('transactions.invoice_no') . ":* {$transaction['invoice_no']}\n";
        }
        
        $message .= "\n💰 *" . __('transactions.total_amount') . ":*\n";
        
        if ($transaction['total_amount_rmb'] > 0) {
            $message .= "• RMB: ¥" . number_format($transaction['total_amount_rmb'], 2) . "\n";
        }
        
        if ($transaction['shipping_usd'] > 0) {
            $message .= "• USD: $" . number_format($transaction['shipping_usd'], 2) . "\n";
        }
        
        $message .= "\n💳 *" . __('transactions.payment') . ":*\n";
        
        if ($transaction['payment_rmb'] > 0) {
            $message .= "• RMB: ¥" . number_format($transaction['payment_rmb'], 2) . "\n";
        }
        
        if ($transaction['payment_usd'] > 0) {
            $message .= "• USD: $" . number_format($transaction['payment_usd'], 2) . "\n";
        }
        
        if ($transaction['payment_sdg'] > 0) {
            $message .= "• SDG: " . number_format($transaction['payment_sdg'], 2) . "\n";
        }
        
        $message .= "\n📊 *" . __('balance') . ":*\n";
        
        if ($transaction['balance_rmb'] != 0) {
            $message .= "• RMB: ¥" . number_format($transaction['balance_rmb'], 2) . "\n";
        }
        
        if ($transaction['balance_usd'] != 0) {
            $message .= "• USD: $" . number_format($transaction['balance_usd'], 2) . "\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━\n";
        $message .= "✅ " . __('messages.saved_successfully') . "\n";
        $message .= "📞 " . ($company['phone'] ?? '') . "\n";
        
        return $message;
    }
}

// Helper function for WhatsApp sharing
function whatsapp_share_url($phone, $message)
{
    return \App\Core\WhatsApp::sendReceipt($phone, $message);
}