<?php

namespace Controllers;

require_once 'Models/SubscriptionModel.php';
require_once 'Models/UserModel.php';
require_once 'Models/UserSubscriptionModel.php';
require_once 'Models/VerifyCodeModel.php';
require_once 'Services/EmailService.php';
require_once 'Services/JobService.php';
require_once 'Services/EmailService.php';
require_once 'Services/ParsePriceService.php';

use Models\SubscriptionModel;
use Models\UserModel;
use Models\UserSubscriptionModel;
use Models\VerifyCodeModel;
use Services\EmailService;
use Services\ParsePriceService;

class AppController
{
    static private function generateVerificationCode(): string
    {
        return ''.mt_rand(100000, 999999);
    }

    static private function sendVerificationMessage($recipient, $verification_code): void
    {
       EmailService::sendEmail($recipient, 'Verification Code', 'Your verification code: '.$verification_code);
    }

    static public function subscribe(): bool|string
    {
        if (array_key_exists('email', $_POST) && array_key_exists('url', $_POST)) {
            $url = $_POST['url'];
            if (!str_contains($url, 'https://www.olx.ua')) {
                http_response_code(400);
                return json_encode([
                    'message' => 'url invalid'
                ]);
            }
            $user_model = new UserModel();
            if ($user = $user_model->select_one(['id', 'is_verified'], [['email', '=', $_POST['email']]])) {
                $user_id = $user['id'];
                $is_verified = (int)$user['is_verified'];
                if (!$is_verified) {
                    http_response_code(4001);
                    return json_encode([
                        'status' => false,
                        'message' => 'Your email is not verified, please verify first your email'
                    ]);
                }
            }
            else {
                $user_id = $user_model->insert(['email' => $_POST['email']]);
                $verification_code = self::generateVerificationCode();
                try {
                    self::sendVerificationMessage($_POST['email'], $verification_code);
                    $verify_model = new VerifyCodeModel();
                    $verify_model->insert(['user_id' => $user_id, 'verify_code' => $verification_code]);
                } catch (\Exception $exception) {

                }
            }
            $subscription_model = new SubscriptionModel();
            if ($subscription = $subscription_model->select_one(['id'], [['url', '=', $url]])) {
                $subscription_id = $subscription['id'];
            }
            else {
                $subscription_id = $subscription_model->insert([
                    'url' => $url
                ]);
            }
            $user_subscription_model = new UserSubscriptionModel();
            $user_subscription_model->insertOrIgnore([
                'user_id' => $user_id,
                'subscription_id' => $subscription_id
            ]);
            return json_encode([
                'status' => true
            ]);
        }
        else {
            http_response_code(400);
            return json_encode([
                'status' => false,
                'message' => 'email and url parameters are required'
            ]);
        }
    }

    static public function verifyCode(): bool|string
    {
        if (array_key_exists('email' ,$_POST) && array_key_exists('code', $_POST)) {
            $user_model = new UserModel();
            $user_id = $user_model->select_one(['id'], [[
                'email', '=', $_POST['email']
            ]]);
            if ($user_id === false) {
                http_response_code(404);
                return json_encode([
                    'status' => false,
                    'message' => 'User with such email not found'
                ]);
            }
            $user_id = $user_id['id'];
            if (!isset($user_id)) {
                return json_encode([
                    'status' => false,
                    'message' => 'User with such email not found'
                ]);
            }
            $verify_model = new VerifyCodeModel();
            $code_id = $verify_model->select_one(['verify_code'], [
                ['user_id', '=', $user_id]
            ]);
            if ($code_id === false || $code_id['verify_code'] !== $_POST['code']) {
                http_response_code(400);
                return json_encode([
                    'status' => false,
                    'message' => 'Key is not matched for this user'
                ]);
            }
            $result = $user_model->update(['is_verified' => 1], [
                ['id', '=', $user_id]
            ]);
            if ($result === true) {
                $verify_model->delete([
                    ['user_id', '=', $user_id]
                ]);
                return json_encode([
                    'status' => true
                ]);
            }
            http_response_code(500);
            return json_encode([
                'status' => false,
                'message' => 'Something went wrong, please try again'
            ]);
        }
        http_response_code(400);
        return json_encode([
            'status' => false,
            'message' => 'email and code parameters should be provided'
        ]);
    }

    static public function runParser()
    {
        ParsePriceService::sendPrices();
    }
}