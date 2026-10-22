<?php

namespace Database\Factories;

use App\Models\EmailDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailDelivery>
 */
class EmailDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(),
            'recipient' => fake()->safeEmail(),
            'mailable_class' => null,
            'status' => EmailDelivery::STATUS_SENT,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmailDelivery::STATUS_FAILED,
            'error' => 'SMTP connection refused (111): smtp.example.test:587',
        ]);
    }
}
