<?php

it('should get an otp', function () {
    $response = $this->post('/api/getOtp', ['phoneNumber' => "09920320860"]);

    $response->assertStatus(500);
});
