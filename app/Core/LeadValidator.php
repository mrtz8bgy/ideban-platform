<?php
declare(strict_types=1);

namespace App\Core;

final class LeadValidator
{
    /** @return array{data: array<string,string>, errors: array<string,string>} */
    public static function validate(array $input): array
    {
        $data = [
            'full_name' => trim((string) ($input['full_name'] ?? '')),
            'company_name' => trim((string) ($input['company_name'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'requested_service' => trim((string) ($input['requested_service'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'lead_type' => trim((string) ($input['lead_type'] ?? 'contact')),
        ];
        $errors = [];

        if ($data['full_name'] === '' || \text_length($data['full_name']) > 160) {
            $errors['full_name'] = 'نام را وارد کنید.';
        }
        $normalizedPhone = strtr($data['phone'], ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
        $digits = preg_replace('/[^0-9]/', '', $normalizedPhone) ?? '';
        if (strlen($digits) < 8 || strlen($digits) > 18) {
            $errors['phone'] = 'شماره تماس معتبر وارد کنید.';
        } else {
            $data['phone'] = $digits;
        }
        if ($data['email'] !== '' && (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 190)) {
            $errors['email'] = 'ایمیل معتبر وارد کنید.';
        }
        if (!in_array($data['lead_type'], ['consultation', 'quote', 'contact'], true)) {
            $data['lead_type'] = 'contact';
        }
        foreach (['company_name' => 180, 'requested_service' => 180] as $field => $limit) {
            if (\text_length($data[$field]) > $limit) {
                $errors[$field] = 'طول مقدار واردشده بیش از حد مجاز است.';
            }
        }
        if (\text_length($data['description']) > 8000) {
            $errors['description'] = 'توضیحات بیش از حد طولانی است.';
        }

        return ['data' => $data, 'errors' => $errors];
    }
}
