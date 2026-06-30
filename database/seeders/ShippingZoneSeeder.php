<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Shop Pick Up: Simara Mall 7th Floor 7F20', 'price' => 0],
            ['name' => 'Super Metro: Thika, Kikuyu, Ruiru, Juja, Ngong, Kitengela etc.', 'price' => 250.00],
            ['name' => 'ZONE A: Within CBD', 'price' => 150.00],
            ['name' => 'ZONE B: Upperhill, Valley Road, Community, Hurlingham, Nairobi Hospital, Pangani, Ngara,KNH,Ojijo Road etc.', 'price' => 300.00],
            ['name' => 'ZONE C: Riverside, Westlands, ABC, Kilimani,Kileleshwa,Westgate, General Mathenge,Parklands,MP-Shah, Aghakhan,Oshwal etc.', 'price' => 350.00],            ['name' => 'Pick Up Mtaani Agent Drop Off', 'price' => 220.00],
            ['name' => 'ZONE D: Kangemi, Loresho, Mountain View, Spring Valley, Lower Kabete, Industrial Area', 'price' => 400.00],
            ['name' => 'ZONE E: South B/C, Mbagathi, Madaraka, Nairobi West, Langata, Carnivore, Nairobi West, Bellevue, NextGen Mall, Panari, Imara etc.', 'price' => 350.00],
            ['name' => 'ZONE F: Junction Mall, Lavington, Kibra, Dagoretti Corner, Kawangware, Wanyee etc.', 'price' => 400.00],
            ['name' => 'ZONE G: Ruaka, Runda, Nyari, Gigiri, UNEP, Muchatha, Thindigua, Muthaiga North, Fourways, Ridgeways, Komarock, Tassia etc.', 'price' => 450.00],
            ['name' => 'ZONE H: Gateway Mall, Syokimau, Ruiru Bypass, Kitusuru, Mwimuto etc.', 'price' => 600.00],
            ['name' => 'ZONE J: Roasters, Mountain Mall, Garden City, TRM,Lumumba Drive, USIU, Ngumba etc.', 'price' => 350.00],
            ['name' => 'ZONE K: Outside Nairobi (Indicate your town & preferred courier)', 'price' => 500.00],
            ['name' => 'ZONE N: Donholm, Uhuru Estate, Buruburu, Fedha, Tassia, Savanah, Pipeline, Mtindwa, Lucky Summer etc.', 'price' => 400.00],
            ['name' => 'ZONE M: Mirema, Kahawa Sukari, Zimmerman, Kiambu Road(Kmall), Githurai, Kahawa West, Kahawa Wendani, Clayworks etc.', 'price' => 450.00],
            ['name' => 'Zone O: Uthiru, Kinoo, Kikuyu Town', 'price' => 450.00],
            ['name' => 'ZONE P: Rongai, Karen, Ngong Town, Tatu City, Kenyatta Road', 'price' => 800.00],
            ['name' => 'Zone Q: Thika Town, Kamulu, Kitengela (Same day)', 'price' => 1350.00],
            ['name' => 'Zone R: Athi River', 'price' => 900.00],
        ];

        foreach ($zones as $zone) {
            ShippingZone::create($zone);
        }
    }
}
