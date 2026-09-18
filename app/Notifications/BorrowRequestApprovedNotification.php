<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BorrowRequest $borrowRequest,
        public int $itemCount,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $borrowRequest = $this->borrowRequest;

        return (new MailMessage)
            ->subject("[{$borrowRequest->request_no}] คำขอยืมอุปกรณ์ได้รับการอนุมัติแล้ว")
            ->greeting("เรียน {$notifiable->name}")
            ->line("คำขอยืมเลขที่ {$borrowRequest->request_no} ได้รับการอนุมัติแล้ว")
            ->line("วัตถุประสงค์: {$borrowRequest->purpose}")
            ->line(sprintf(
                'ระยะเวลายืม: %s ถึง %s',
                $borrowRequest->borrow_date->format('d/m/Y'),
                $borrowRequest->expected_return_date->format('d/m/Y'),
            ))
            ->line('จำนวนอุปกรณ์: '.number_format($this->itemCount).' รายการ')
            ->action('ดูคำขอยืมของฉัน', route('borrow.mine'))
            ->line('โปรดติดตามขั้นตอนการเตรียมและรับอุปกรณ์ในระบบ');
    }
}
