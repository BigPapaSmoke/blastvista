<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MissingBarcodeReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $report)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily missing barcode report for ' . $this->report['report_date'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.missing-barcodes-report',
            with: [
                'report' => $this->report,
            ],
        );
    }
}