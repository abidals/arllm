<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends Model
{
    protected $table = 'ai_providers';

    protected $fillable = [
        'label', 'driver', 'base_url', 'model', 'api_key_encrypted',
        'max_tokens', 'is_default', 'is_active', 'created_by',
    ];

    protected $hidden = ['api_key_encrypted'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /* مفتاح API مشفّر AES-256-GCM ولا يُفكّ إلا لحظة الاتصال بالمزوّد */

    public function setApiKeyAttribute(string $plain): void
    {
        $this->attributes['api_key_encrypted'] = Crypt::encryptString(trim($plain));
    }

    public function decryptKey(): string
    {
        try {
            return Crypt::decryptString($this->api_key_encrypted);
        } catch (\Throwable) {
            abort(500, 'تعذر فك تشفير مفتاح المزوّد — تحقق من APP_KEY.');
        }
    }

    public function maskedKey(): string
    {
        try {
            $plain = Crypt::decryptString($this->api_key_encrypted);
        } catch (\Throwable) {
            return '••••';
        }

        if (strlen($plain) <= 8) {
            return '••••••••';
        }

        return substr($plain, 0, 3).'…'.substr($plain, -4);
    }
}