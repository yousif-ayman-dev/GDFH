<?php

namespace App\Services\AI;

class AISanitizer
{
    /**
     * Sanitize input text by scrubbing sensitive keywords, tokens, passwords, and private paths.
     */
    public function sanitize(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // Scrub passwords, tokens, API keys, and secret values
        $text = preg_replace(
            '/\b(password|passwd|pwd|token|api[_-]?key|secret|bearer)\b\s*[:=]\s*["\']?[^"\'\s,;]+["\']?/i',
            '$1: [REDACTED_SECRET]',
            $text
        );

        // Scrub potential Credit Card numbers (13 to 16 digits)
        $text = preg_replace(
            '/\b(?:\d[ -]*?){13,16}\b/',
            '[REDACTED_CARD_NUMBER]',
            $text
        );

        // Scrub private file storage paths (verification documents, private storage)
        $text = preg_replace(
            '/(storage\/app\/private\/[^\s,\'\"]+|uploads\/verifications\/[^\s,\'\"]+)/i',
            '[REDACTED_PRIVATE_DOCUMENT_PATH]',
            $text
        );

        return $text;
    }

    /**
     * Recursively sanitize context data array before passing to AI provider APIs.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function sanitizeContext(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            // Skip sensitive keys entirely
            if (in_array(strtolower($key), ['password', 'password_confirmation', 'token', 'secret', 'api_key', 'private_key', 'card_number', 'cvv'], true)) {
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
