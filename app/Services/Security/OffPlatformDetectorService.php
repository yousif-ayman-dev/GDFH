<?php

namespace App\Services\Security;

class OffPlatformDetectorService
{
    /**
     * Inspect text for off-platform contact info and mask forbidden patterns.
     *
     * @return array{clean_text: string, is_flagged: bool, flagged_reasons: array<int, string>}
     */
    public function inspectAndFilter(string $text): array
    {
        $flaggedReasons = [];
        $isFlagged = false;
        $cleanText = $text;

        // 1. Detect Phone Numbers (e.g. +966..., 05..., 00966..., etc.)
        $phoneRegex = '/(?:\+?\d{1,3}[-.\s]?)?\(?\d{2,4}\)?[-.\s]?\d{3,4}[-.\s]?\d{3,4}/u';
        if (preg_match($phoneRegex, $cleanText)) {
            $isFlagged = true;
            $flaggedReasons[] = 'رقم هاتف خراج المنصة';
            $cleanText = preg_replace($phoneRegex, '[تم حجب رقم الهاتف لحمايتك - المعاملات موثقة ومحمية عبر Tasker 🛡️]', $cleanText);
        }

        // 2. Detect Email Addresses
        $emailRegex = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/u';
        if (preg_match($emailRegex, $cleanText)) {
            $isFlagged = true;
            $flaggedReasons[] = 'بريد إلكتروني خارجي';
            $cleanText = preg_replace($emailRegex, '[تم حجب البريد الإلكتروني لحمايتك - تواصل عبر شات Tasker الرسمية 🛡️]', $cleanText);
        }

        // 3. Detect External Chat/Meeting Links (WhatsApp, Telegram, Zoom, Meet, Teams, Skype)
        $externalLinkRegex = '/(https?:\/\/)?(www\.)?(wa\.me|whatsapp\.com|t\.me|telegram\.me|zoom\.us|meet\.google\.com|teams\.microsoft\.com|skype\.com)\/[^\s]+/iu';
        if (preg_match($externalLinkRegex, $cleanText)) {
            $isFlagged = true;
            $flaggedReasons[] = 'رابط تواصل خارجي';
            $cleanText = preg_replace($externalLinkRegex, '[تم حجب رابط التواصل الخارجي - جميع المحادثات والاجتماعات موثقة داخل المنصة 🛡️]', $cleanText);
        }

        // 4. Detect Off-Platform Payment Keywords (PayPal, IBAN, STC Pay, Zain Cash, Vodafone Cash)
        $paymentRegex = '/(paypal|iban|stc\s*pay|zain\s*cash|vodafone\s*cash|تحويل\s*بنكي|حساب\s*بنكي)/iu';
        if (preg_match($paymentRegex, $cleanText)) {
            $isFlagged = true;
            $flaggedReasons[] = 'طلب تحويل مالي خارجي';
            $cleanText = preg_replace($paymentRegex, '[تم حجب وسيلة الدفع الخارجية - جميع المستحقات محفوظة بحساب الضمان الإلكرتوني 🛡️]', $cleanText);
        }

        return [
            'clean_text' => $cleanText,
            'is_flagged' => $isFlagged,
            'flagged_reasons' => array_unique($flaggedReasons),
        ];
    }
}
