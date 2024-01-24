<?php

namespace Services;
require_once 'Models/SubscriptionModel.php';
require_once 'Models/UserSubscriptionModel.php';
require_once 'Models/UserModel.php';
require_once 'EmailService.php';

use DOMDocument;
use DOMXPath;
use Models\UserModel;

class ParsePriceService
{
    static public function parsePrice($url)
    {
        $doc = new DOMDocument();
        $doc->loadHTMLFile($url);
        $xpath_price = '//div[@id="baxter-middle"]/../../div[2]/div[1]/div[3]//h3';
        $xpath = new DOMXpath($doc);
        $elements = $xpath->query($xpath_price);

        if (!is_null($elements)) {
            $element = $elements[0];
            return $element->nodeValue;
        }
        return null;
    }

    static public function sendPrices()
    {
        $user_model = new UserModel();
        $data = $user_model->select_all(['email', 'url', 'price'],
            [
                ['is_verified', '=', 1]
            ],
            [
                'join user_subscriptions usb on usb.user_id = users.id',
                'join subscriptions sb on sb.id = usb.subscription_id'
            ]
        );
        echo json_encode($data);
        $url_prices = [];
        foreach ($data as $user) {
            if (array_key_exists($user['url'], $url_prices)) {
                $price = $url_prices[$user['url']];
            }
            else {
                $price = self::parsePrice($user['url']);
                $url_prices[$user['url']] = $price;
            }
            if (isset($price)) {
                \Services\EmailService::sendEmail($user['email'], 'Price parsing', 'Price for url(' . $user['url'] . ') ' . $price);
            }
        }
        return $data;
    }
}