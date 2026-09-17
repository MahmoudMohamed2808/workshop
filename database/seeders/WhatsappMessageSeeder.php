<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\WhatsappMessage;
use Illuminate\Database\Seeder;

class WhatsappMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 500+ رسالة واتساب باللهجة المصرية مربوطة بالعملاء والأوامر.
     */
    public function run(): void
    {
        if (WhatsappMessage::count() > 0) {
            return;
        }

        RepairOrder::with(['customer', 'deviceType', 'brand'])->chunkById(100, function ($orders): void {
            foreach ($orders as $order) {
                $customer = $order->customer;
                if (! $customer) {
                    continue;
                }

                $device = trim(($order->deviceType->name ?? 'الجهاز').' '.($order->brand->name ?? ''));
                $messages = $this->messagesFor($order->status, $order->order_number, $device, (float) $order->total_cost);

                // رسالة أو رسالتين لكل أمر حسب مرحلته
                $count = in_array($order->status, ['delivered', 'ready_for_delivery'], true) ? 2 : 1;
                $picked = collect($messages)->shuffle()->take($count);

                foreach ($picked as $msg) {
                    $createdAt = $order->created_at->copy()->addHours(random_int(1, 96));
                    $statusRoll = random_int(1, 100);
                    $status = $statusRoll <= 85 ? 'sent' : ($statusRoll <= 93 ? 'pending' : 'failed');

                    $customer->whatsappMessages()->create([
                        'repair_order_id' => $order->id,
                        'type' => $msg['type'],
                        'message' => $msg['text'],
                        'media_url' => null,
                        'status' => $status,
                        'error_message' => $status === 'failed' ? 'فشل الإرسال: رقم العميل مش عليه واتساب أو الشبكة واقعة' : null,
                        'sent_at' => $status === 'sent' ? $createdAt->copy()->addMinutes(random_int(1, 60)) : null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        });

        // شوية رسائل عامة من غير أمر (استفسارات عملاء)
        $this->seedGeneralMessages();
    }

    /**
     * @return list<array{type: string, text: string}>
     */
    private function messagesFor(string $status, string $orderNumber, string $device, float $total): array
    {
        $totalText = number_format($total, 0).' جنيه';

        $all = [
            ['type' => 'intake', 'text' => "أهلا بيك في ورشة الإصلاح يا فندم 🌹 استلمنا من حضرتك {$device} بأمر الشغل رقم {$orderNumber} وهنكشف عليه ونبلغك بالتكلفة قبل أي شغل."],
            ['type' => 'diagnosis', 'text' => "كشفنا على {$device} بتاع حضرتك (أمر {$orderNumber}) والتشخيص جاهز. التكلفة التقديرية {$totalText} شامل الفك والتركيب والاختبار."],
            ['type' => 'approval_request', 'text' => "بعد إذن حضرتك محتاجين موافقتك عشان نبدأ شغل في {$device} (أمر {$orderNumber}). التكلفة {$totalText}. رد علينا بـ (موافق) أو كلمنا لو عندك أي استفسار."],
            ['type' => 'ready', 'text' => "خبر حلو يا فندم 🎉 {$device} بتاع حضرتك (أمر {$orderNumber}) خلص وبقى جاهز للاستلام. مستنيينك في أي وقت من 10 الصبح لـ 10 بالليل."],
            ['type' => 'invoice', 'text' => "فاتورة حضرتك جاهزة لأمر الشغل {$orderNumber} بإجمالي {$totalText}. الدفع كاش أو فودافون كاش أو انستاباي. شكرا لثقتك فينا 🙏"],
            ['type' => 'payment_reminder', 'text' => "بنفكر حضرتك إن لسه باقي مبلغ على أمر الشغل {$orderNumber} وقدره {$totalText}. تقدر تدفع لما تستلم أو عن طريق فودافون كاش. شكرا ليك."],
        ];

        return match ($status) {
            'received' => [$all[0]],
            'in_repair' => [$all[1]],
            'ready_for_delivery' => [$all[3], $all[4]],
            'delivered' => [$all[3], $all[4]],
            default => [$all[0], $all[5]],
        };
    }

    private function seedGeneralMessages(): void
    {
        $customers = Customer::inRandomOrder()->limit(40)->get();

        $texts = [
            ['type' => 'other', 'text' => 'لو سمحتوا الورشة فاتحة يوم الجمعة؟ وعندكم صيانة تكييفات في البيت ولا لازم أجيب الجهاز؟'],
            ['type' => 'other', 'text' => 'عندي تلاجة توشيبا 16 قدم مش بتبرد كويس، الكشف بكام وهل في ضمان على التصليح؟'],
            ['type' => 'other', 'text' => 'الغسالة عندي بتخبط جامد في العصر، ممكن حد ييجي يشوفها في البيت في فيصل؟'],
            ['type' => 'other', 'text' => 'بتاخدوا كام على شحن فريون التكييف 1.5 حصان؟ والشغل عليه ضمان قد إيه؟'],
            ['type' => 'other', 'text' => 'الموتور عندي بيشتغل ويفصل، عايز أعرف التكلفة قبل ما أجيبه الورشة لو سمحتوا.'],
        ];

        foreach ($customers as $customer) {
            $msg = $texts[array_rand($texts)];
            $createdAt = now()->subDays(random_int(0, 90));

            $customer->whatsappMessages()->create([
                'repair_order_id' => null,
                'type' => $msg['type'],
                'message' => $msg['text'],
                'media_url' => null,
                'status' => random_int(1, 100) <= 90 ? 'sent' : 'pending',
                'error_message' => null,
                'sent_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
