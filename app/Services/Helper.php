<?php

namespace App\Services;

class Helper
{
    /**
     * Clean the response from the AI service to ensure it is valid JSON.
     *
     * @param string $rawResponse
     * @return string
     */
    public function cleanCoherentResponse(string $responseText): ?array
    {
        $cleaned = trim(preg_replace('/^.*?(\[|\{)/', '$1', $responseText));
        
        $closingPos = strrpos($cleaned, ']');
        if ($closingPos === false) {
            $closingPos = strrpos($cleaned, '}');
        }

        if ($closingPos !== false) {
            $cleaned = substr($cleaned, 0, $closingPos + 1);
        }

        try {
            return json_decode($cleaned, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Gagal decode JSON AI: ' . $e->getMessage(), [
                'response' => $responseText,
                'cleaned' => $cleaned,
            ]);

            return null;
        }
    }
}