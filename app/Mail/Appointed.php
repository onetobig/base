<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Appointed extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from("14628789@qq.com")
            ->subject($this->appointment->name . "预约")
            ->with([
                'url' => route('apppointments.index')
            ])
            ->markdown('emails.appointment.appointed');
    }
}
