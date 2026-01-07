<?php

namespace App\Services;

use App\Models\User;

class ShippingAddressService
{
    /**
     * 配送先住所を構築
     *
     * @param  \App\Models\User  $user
     * @return string
     */
    public function build(User $user)
    {
        $shippingAddress = '';
        if ($user->postal_code) {
            $shippingAddress .= '〒' . $user->postal_code . ' ';
        }
        if ($user->address) {
            $shippingAddress .= $user->address;
        }
        if ($user->building_name) {
            $shippingAddress .= ' ' . $user->building_name;
        }
        return $shippingAddress;
    }
}

