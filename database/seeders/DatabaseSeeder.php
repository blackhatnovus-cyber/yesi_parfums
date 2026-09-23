<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $user = User::query()->updateOrCreate(
            ['username' => 'issey'],
            [
                'name' => 'Issey A. Cabangon',
                'email' => 'issey@example.com',
                'password' => Hash::make('password'),
            ],
        );

        Cart::query()->firstOrCreate(['user_id' => $user->id]);
        Wishlist::query()->firstOrCreate(['user_id' => $user->id]);

        $products = [
            [
                'name' => 'Issey Parfums Signature',
                'category' => 'Woody Floral',
                'description' => 'A poised signature of pale iris, hinoki wood, and skin-warm musk, composed to feel quiet, architectural, and unmistakably personal.',
                'price' => '4850.00',
                'stock' => 18,
                'image' => '/images/signature.jpg',
            ],
            [
                'name' => 'Rose Éclat',
                'category' => 'Floral',
                'description' => 'Dewy damask rose opens into pink pepper and soft suede, leaving a luminous trail that feels fresh-cut rather than sweet.',
                'price' => '4250.00',
                'stock' => 24,
                'image' => '/images/rose-eclat.jpg',
            ],
            [
                'name' => 'Noir Essence',
                'category' => 'Amber Woody',
                'description' => 'Smoked vetiver, black tea, and labdanum gather in a shadowed composition with a polished mineral edge.',
                'price' => '5600.00',
                'stock' => 10,
                'image' => '/images/noir-essence.jpg',
            ],
            [
                'name' => 'Velvet Bloom',
                'category' => 'Floral Amber',
                'description' => 'Osmanthus and magnolia rest on ambered woods, creating a plush floral with apricot warmth and restrained radiance.',
                'price' => '4480.00',
                'stock' => 16,
                'image' => '/images/velvet-bloom.jpg',
            ],
            [
                'name' => 'Pure Aura',
                'category' => 'Fresh Floral',
                'description' => 'Rain-washed iris, bergamot, and clean cedar form an airy veil inspired by cool stone after a summer shower.',
                'price' => '3950.00',
                'stock' => 30,
                'image' => '/images/pure-aura.jpg',
            ],
            [
                'name' => 'Golden Mist',
                'category' => 'Citrus Amber',
                'description' => 'Mandarin peel and saffron glow above blond woods and benzoin, diffusing like late light through linen curtains.',
                'price' => '4725.00',
                'stock' => 14,
                'image' => '/images/golden-mist.jpg',
            ],
            [
                'name' => 'Midnight Oud',
                'category' => 'Woody',
                'description' => 'A measured accord of oud, cedar smoke, and cacao husk—deep and nocturnal, yet tailored close to the skin.',
                'price' => '6250.00',
                'stock' => 8,
                'image' => '/images/midnight-oud.jpg',
            ],
            [
                'name' => 'Fleur Blanc',
                'category' => 'White Floral',
                'description' => 'Orange blossom, jasmine tea, and fig leaf unfold in a sheer white floral balanced by green, quietly bitter facets.',
                'price' => '4150.00',
                'stock' => 22,
                'image' => '/images/fleur-blanc.jpg',
            ],
            [
                'name' => 'Amber Élégance',
                'category' => 'Amber',
                'description' => 'Vanilla bean, amber resin, and sandalwood are cut with dry incense for warmth that remains elegant, never heavy.',
                'price' => '5350.00',
                'stock' => 12,
                'image' => '/images/amber-elegance.jpg',
            ],
            [
                'name' => 'Ocean Muse',
                'category' => 'Aquatic Citrus',
                'description' => 'Salted neroli, sea fennel, and driftwood capture bright coastal air without the familiar sweetness of a conventional marine scent.',
                'price' => '4380.00',
                'stock' => 20,
                'image' => '/images/ocean-muse.jpg',
            ],
        ];

        foreach ($products as $product) {
            $category = Category::query()->firstOrCreate(['name' => $product['category']]);

            Product::query()->updateOrCreate(
                ['name' => $product['name']],
                [
                    ...$product,
                    'category_id' => $category->id,
                    'status' => 'active',
                ],
            );
        }
    }
}
