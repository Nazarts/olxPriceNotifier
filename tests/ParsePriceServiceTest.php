<?php

namespace Services;

use Models\SubscriptionModel;
use Models\UserModel;
use Models\UserSubscriptionModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParsePriceService::class)]
#[UsesClass(EmailService::class)]
class ParsePriceServiceTest extends TestCase
{
    public function testParsePrice()
    {
        $result = ParsePriceService::parsePrice('https://www.olx.ua/d/uk/obyavlenie/invertor-chistaya-sinusoida-3000vt-12v-220v-preobrazovatel-toka-IDQG08S.html?reason=ip%7Clister');
        $this->assertNotEmpty($result);

        $result = ParsePriceService::parsePrice('www.olx.dsdssd/ds');
        $this->assertEmpty($result);
    }

    public function testSendPrice()
    {
        $user_model = new UserModel();
        $user_id = $user_model->insert(['email' => 'nazar.tsiupiakbox@gmail.com', 'is_verified' => 1]);
        if ($user_id) {
            $subscription_model = new SubscriptionModel();
            $url = 'https://www.olx.ua/d/uk/obyavlenie/invertor-chistaya-sinusoida-3000vt-12v-220v-preobrazovatel-toka-IDQG08S.html?reason=ip%7Clister';
            $subscription_id = $subscription_model->insert(['url' => $url]);
            $this->assertIsInt($subscription_id);
            $user_subscription = new UserSubscriptionModel();
            $user_subscription->insert([
                'user_id' => $user_id,
                'subscription_id' => $subscription_id
            ]);
            echo json_encode([
                'user_id' => $user_id,
                'subscription_id' => $subscription_id
            ]);
            ParsePriceService::sendPrices();
            $price = ParsePriceService::parsePrice($url);
            $new_price = $subscription_model->select_one(['price'], [['url', '=', $url]]);
            $this->assertEquals($price, $new_price['price']);
            $user_subscription->delete([['subscription_id', '=', $subscription_id]]);
            $subscription_model->delete([['id', '=', $subscription_id]]);
        }
        $user_model->delete([['email', '=', 'nazar.tsiupiakbox@gmail.com']]);
    }
}
