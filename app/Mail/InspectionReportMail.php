<?php

namespace App\Mail;

use App\Models\DataInspection\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $inspection;
    public function __construct(Inspection $inspection)
    {
        $this->inspection = $inspection;
    
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inspection Report Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

     public function build()
    {
        return $this->subject('Laporan Inspeksi Kendaraan')
                    ->view('emails.inspection-report')
                    ->attach(storage_path('app/public/reports/inspection-'.$this->inspection->id.'.pdf'), [
                        'as' => 'laporan-inspeksi.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
