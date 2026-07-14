<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'name', 'subject', 'body', 'variables'];

    protected function casts(): array
    {
        return ['variables' => 'array'];
    }

    public function render(array $data = []): string
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return $body;
    }
}
