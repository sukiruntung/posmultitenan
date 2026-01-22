<?php

namespace App\Services;

use App\Models\Counters\DocumentNumbering;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NumberGenerator
{
    public static function generate(string $menuName, int $outletId): string
    {
        $now = Carbon::now();
        $number = '';
        $sequence = DocumentNumbering::where('outlet_id', $outletId)
            ->where('document_numbering_name', $menuName)->lockForUpdate()->first();

        if (! $sequence) {
            throw ValidationException::withMessages([
                'message' => "Number sequence for {$menuName} not configured."
            ]);
            // throw new \Exception("Number sequence for {$menuName} not configured.");
        }

        if ($sequence->document_numbering_resettype === 'yearly' && $sequence->updated_at?->year !== $now->year) {
            $sequence->document_numbering_currentnumber  = 0;
        } elseif ($sequence->document_numbering_resettype === 'monthly' && $sequence->updated_at?->format('Ym') !== $now->format('Ym')) {
            $sequence->document_numbering_currentnumber  = 0;
        } elseif ($sequence->document_numbering_resettype === 'daily' && $sequence->updated_at?->format('Yd') !== $now->format('Yd')) {
            $sequence->document_numbering_currentnumber  = 0;
        }
        $sequence->document_numbering_currentnumber++;
        $sequence->save();

        if ($sequence->document_numbering_format) {

            $parts = preg_split('/(\[.*?\])/', $sequence->document_numbering_format, -1, PREG_SPLIT_DELIM_CAPTURE);


            foreach ($parts as $part) {
                switch ($part) {
                    case '[document_numbering_prefix]':
                        $number .= $sequence->document_numbering_prefix;
                        break;
                    case '[Y]':
                        $number .= date('Y');
                        break;
                    case '[y]':
                        $number .= date('y');
                        break;
                    case '[M]':
                        $number .= date('m');
                        break;
                    case '[D]':
                        $number .= date('d');
                        break;
                    default:
                        $number .= $part;
                        break;
                }
            }
        }
        $number .= str_pad($sequence->document_numbering_currentnumber, $sequence->document_numbering_numberlength, '0', STR_PAD_LEFT);

        return $number;
    }
}
