<?php

if (!function_exists('formatDate')) {
    /**
     * Format a date to dd/mm/yyyy format
     *
     * @param mixed $date
     * @return string
     */
    function formatDate($date)
    {
        if (empty($date)) {
            return '';
        }

        try {
            if ($date instanceof \Carbon\Carbon) {
                return $date->format('d/m/Y');
            }

            if (is_string($date)) {
                $carbon = \Carbon\Carbon::parse($date);
                return $carbon->format('d/m/Y');
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format a datetime to dd/mm/yyyy HH:mm format
     *
     * @param mixed $date
     * @return string
     */
    function formatDateTime($date)
    {
        if (empty($date)) {
            return '';
        }

        try {
            if ($date instanceof \Carbon\Carbon) {
                return $date->format('d/m/Y H:i');
            }

            if (is_string($date)) {
                $carbon = \Carbon\Carbon::parse($date);
                return $carbon->format('d/m/Y H:i');
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}

